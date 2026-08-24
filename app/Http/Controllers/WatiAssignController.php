<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WatiAssignController extends Controller
{
    /**
     * Paso 1: Guardar el ID del material seleccionado
     * Se llama cuando el usuario selecciona un material de la lista
     */
    public function saveMaterial(Request $request): JsonResponse
    {
        try {
            Log::info('Guardando material seleccionado', ['payload' => $request->all()]);

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'course_id' => 'required|integer|exists:courses,id',
            ], [
                'phone.required' => 'El teléfono es obligatorio',
                'course_id.required' => 'El ID del curso es obligatorio',
                'course_id.exists' => 'El curso seleccionado no existe',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Buscar el curso
            $course = Course::find($request->course_id);

            if (!$course || !$course->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'El material seleccionado no está disponible',
                ], 404);
            }

            // Normalizar teléfono
            $phoneRaw = preg_replace('/[^0-9]/', '', $request->phone);
            if (strlen($phoneRaw) === 12 && str_starts_with($phoneRaw, '57')) {
                $phone = substr($phoneRaw, 2);
            } else {
                $phone = $phoneRaw;
            }

            // Guardar en caché por 30 minutos (suficiente para completar el registro)
            $cacheKey = 'wati_material_' . $phone;
            Cache::put($cacheKey, $request->course_id, now()->addMinutes(30));

            Log::info('Material guardado en caché', [
                'phone' => $phone,
                'course_id' => $request->course_id,
                'course_title' => $course->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => "✅ Material seleccionado: {$course->title}\n\nAhora vamos a registrarte...",
                'course_id' => $course->id,
                'course_title' => $course->title,
            ]);

        } catch (\Exception $e) {
            Log::error('Error guardando material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Error al guardar el material. Intenta de nuevo.',
            ], 500);
        }
    }

    /**
     * Paso 2: Registrar usuario y asignarle el material guardado
     * Se llama después de recolectar los datos del usuario
     */
    public function registerAndAssign(Request $request): JsonResponse
    {
        try {
            Log::info('Registro y asignación desde WATI', ['payload' => $request->all()]);

            // Validar datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
            ], [
                'name.required' => 'El nombre es obligatorio',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'phone.required' => 'El teléfono es obligatorio',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Normalizar teléfono
            $phoneRaw = preg_replace('/[^0-9]/', '', $request->phone);
            if (strlen($phoneRaw) === 12 && str_starts_with($phoneRaw, '57')) {
                $phone = substr($phoneRaw, 2);
            } elseif (strlen($phoneRaw) === 10) {
                $phone = $phoneRaw;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'El número de teléfono debe tener 10 dígitos',
                ], 422);
            }

            // La contraseña siempre es el teléfono
            $password = $phone;

            // Recuperar el material guardado
            $cacheKey = 'wati_material_' . $phone;
            $courseId = Cache::get($cacheKey);

            if (!$courseId) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No se encontró el material seleccionado. Por favor selecciona nuevamente.',
                ], 404);
            }

            $course = Course::find($courseId);
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'El curso seleccionado no existe',
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Mensajes comunes para respuestas
                $demoWarning = "⏰ IMPORTANTE: Tienes 12 horas de acceso DEMO\n💳 Después deberás pagar para continuar\n\n";
                $accessInfo = "🌐 Ingresa aquí:\n".config('app.url')."\n\n✅ Comienza a explorar tu material ahora!";

                // Verificar si el usuario ya existe (por teléfono o email)
                $existingUser = User::where('phone', $phone)
                    ->orWhere('email', strtolower($request->email))
                    ->first();

                if ($existingUser) {
                    // Usuario existe - verificar si ya tiene el curso
                    // Buscar orden pagada o demo válida
                    $hasValidOrder = Order::where('user_id', $existingUser->id)
                        ->where(function ($query) {
                            $query->where('status_id', OrderStatus::PAID->value)
                                ->orWhere(function ($q) {
                                    $q->where('status_id', OrderStatus::DEMO->value)
                                      ->where('demo_expires_at', '>', now());
                                });
                        })
                        ->whereHas('items', function ($query) use ($courseId) {
                            $query->where('course_id', $courseId);
                        })
                        ->exists();

                    $hasSubscription = Subscription::where('user_id', $existingUser->id)
                        ->where('course_id', $courseId)
                        ->valid()
                        ->exists();

                    if ($hasValidOrder || $hasSubscription) {
                        DB::rollBack();
                        Log::info('Usuario ya tiene el curso', [
                            'user_id' => $existingUser->id,
                            'course_id' => $courseId,
                        ]);

                        return response()->json([
                            'success' => true,
                            'user_id' => $existingUser->id,
                            'password' => $phone,
                            'order_id' => null,
                            'message' => '✅ Ya tienes acceso a este material',
                            'existing_user' => true,
                        ]);
                    }

                    // Buscar si tiene una demo expirada del mismo curso
                    $expiredDemo = Order::where('user_id', $existingUser->id)
                        ->where('status_id', OrderStatus::DEMO->value)
                        ->where('demo_expires_at', '<=', now())
                        ->whereHas('items', function ($query) use ($courseId) {
                            $query->where('course_id', $courseId);
                        })
                        ->first();

                    if ($expiredDemo) {
                        // Reutilizar la demo expirada renovando su tiempo
                        $expiredDemo->update([
                            'demo_expires_at' => now()->addMinutes(30),
                            'status_id' => OrderStatus::DEMO->value, // Volver a estado DEMO activo
                        ]);

                        DB::commit();

                        Log::info('Demo renovada para usuario existente', [
                            'user_id' => $existingUser->id,
                            'order_id' => $expiredDemo->id,
                            'course_id' => $courseId,
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => "🎉 ¡Demo renovada exitosamente!\n\n".
                                  "👤 {$existingUser->name}, tu demo ha sido renovada\n\n".
                                  "📧 Email: {$existingUser->email}\n".
                                  "🔐 Tu contraseña sigue siendo: {$password}\n\n".
                                  "📚 Material renovado (DEMO):\n{$course->title}\n\n".
                                  $demoWarning.$accessInfo,
                            'user_id' => $existingUser->id,
                            'email' => $existingUser->email,
                            'password' => $password,
                            'phone' => $phone,
                            'order_id' => $expiredDemo->id,
                            'is_new_user' => false,
                            'course' => [
                                'id' => $course->id,
                                'title' => $course->title,
                            ],
                        ]);
                    }

                    // Usuario existe pero no tiene el curso - crear orden
                    $user = $existingUser;
                    $isNewUser = false;

                    Log::info('Asignando curso a usuario existente', [
                        'user_id' => $user->id,
                        'course_id' => $courseId,
                    ]);

                } else {
                    // Usuario nuevo - crear
                    $user = User::create([
                        'name' => ucwords(strtolower(trim($request->name))),
                        'last_name' => '',
                        'email' => strtolower($request->email),
                        'phone' => $phone,
                        'password' => Hash::make($password),
                    ]);

                    // Asignar rol
                    $user->assignRole('user');
                    $isNewUser = true;

                    Log::info('Usuario nuevo creado', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }

                // Crear orden para asignar el material
                $order = Order::create([
                    'number' => 'WATI-' . strtoupper(Str::random(10)),
                    'user_id' => $user->id,
                    'amount' => 39000,
                    'currency' => 'COP',
                    'status_id' => OrderStatus::DEMO->value, // Estado DEMO
                    'payment_method' => 'wati_whatsapp',
                    'description' => 'Demo desde WhatsApp - ' . $course->title,
                    'paid_at' => null,
                    'demo_expires_at' => now()->addMinutes(30), // Demo de 30 minutos
                ]);

                // Crear item de la orden (asignar el material)
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'description' => $course->title,
                    'unit_price' => 0,
                    'quantity' => 1,
                    'total' => 0,
                ]);

                DB::commit();

                // Limpiar caché
                Cache::forget($cacheKey);

                Log::info($isNewUser ? 'Usuario registrado y material asignado' : 'Material asignado a usuario existente', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'course_id' => $course->id,
                    'order_id' => $order->id,
                    'is_new_user' => $isNewUser,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $isNewUser
                        ? "🎉 ¡Registro completado exitosamente!\n\n".
                          "👤 Bienvenido/a {$user->name}\n\n".
                          "📧 Email: {$user->email}\n".
                          "🔐 Contraseña: {$password}\n\n".
                          "📚 Material asignado (DEMO):\n{$course->title}\n\n".
                          $demoWarning.$accessInfo
                        : "🎉 ¡Material asignado exitosamente!\n\n".
                          "👤 {$user->name}, se ha agregado un nuevo material a tu cuenta\n\n".
                          "📧 Email: {$user->email}\n".
                          "🔐 Tu contraseña sigue siendo: {$password}\n\n".
                          "📚 Nuevo material asignado (DEMO):\n{$course->title}\n\n".
                          $demoWarning.$accessInfo,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password' => $password,
                    'phone' => $phone,
                    'order_id' => $order->id,
                    'is_new_user' => $isNewUser,
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error en registro y asignación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Error al completar el registro. Por favor intenta de nuevo.',
            ], 500);
        }
    }

    /**
     * Endpoint de verificación
     */
    public function verify(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'WATI Assign API endpoint is active',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
