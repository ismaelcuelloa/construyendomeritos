<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function list(Request $request)
    {
        $query = Subscription::query()->with(['course', 'creator.roles']);
        $perPage = $this->getPerPage($request);
        $user_id = $request->input('user_id');

        if ($user_id != null) {
            $query->where('user_id', $user_id);
        }

        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            // Validar que el curso exista
            $course = Course::findOrFail($request->course_id);

            // Verificar si ya existe una suscripción activa
            $existingSubscription = Subscription::where('user_id', $request->user_id)
                ->where('course_id', $request->course_id)
                ->valid()
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'message' => 'El usuario ya tiene una suscripción activa a este curso',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::beginTransaction();

            // Crear la orden en estado PENDIENTE
            $orderNumber = 'ORD-'.now()->format('YmdHis');

            $order = Order::create([
                'number' => $orderNumber,
                'user_id' => $request->user_id,
                'amount' => $course->price,
                'currency' => 'COP',
                'status_id' => OrderStatus::PENDING->value,
            ]);

            // Crear el item de la orden
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $course->id,
                'description' => $course->title,
                'quantity' => 1,
                'unit_price' => $course->price,
                'total' => $course->price,
            ]);

            // Crear la suscripción
            $subscription = Subscription::create([
                'user_id' => $request->user_id,
                'course_id' => $request->course_id,
                'order_id' => $order->id,
                'created_by_user_id' => auth()->id(),
                'enrolled_at' => now(),
                'access_expires_at' => null, // Acceso permanente
            ]);

            // Vincular el order_item con la suscripción para evitar duplicados
            $orderItem->update(['subscription_id' => $subscription->id]);

            DB::commit();

            return response()->json([
                'subscription' => $subscription->load('course'),
                'order' => $order->load('items'),
                'message' => 'Suscripción y orden creadas exitosamente',
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleException($e);
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $subscription = Subscription::findOrFail($id);
            
            // Si la suscripción tiene una orden asociada, eliminarla primero
            // Esto eliminará en cascada los order_items
            if ($subscription->order_id) {
                $order = Order::find($subscription->order_id);
                if ($order) {
                    $order->delete();
                }
            }
            
            // Ahora eliminar la suscripción
            $subscription->delete();

            DB::commit();

            return response()->json(['message' => 'Suscripción y orden eliminadas exitosamente'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
