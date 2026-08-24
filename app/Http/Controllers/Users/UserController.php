<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Users/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            \Log::info('Creando usuario con datos:', $request->all());

            $user = (new UserService)->create($request->all());

            return response()->json(['user' => $user], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->handleException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::query()
            ->with([
                'roles',
                'courses',
                'subscriptions.creator.roles',
                'orders' => function ($query) {
                    $query->whereIn('status_id', [
                            \App\Enums\OrderStatus::PAID->value,
                            \App\Enums\OrderStatus::DEMO->value,
                            \App\Enums\OrderStatus::MANUAL->value,
                            \App\Enums\OrderStatus::DEMO_EXPIRED->value
                        ])
                        ->with([
                            'creator.roles',
                            'items' => function ($itemQuery) {
                                // Solo cargar items que NO tienen suscripción asociada
                                // para evitar duplicados (las suscripciones se muestran aparte)
                                $itemQuery->whereNull('subscription_id')
                                    ->with('course');
                            }
                        ]);
                }
            ])
            ->findOrFail($id);

        return Inertia::render('Admin/Users/Show', ['user' => $user]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $authUser = auth()->user();
            $data = $request->all();

            // Solo los super_user pueden cambiar la contraseña de otros usuarios
            if (isset($data['password']) && ! $authUser->hasRole(User::ROLE_SUPER_USER)) {
                return response()->json([
                    'message' => 'Solo los super usuarios pueden cambiar contraseñas',
                ], Response::HTTP_FORBIDDEN);
            }

            $user = (new UserService)->update($id, $data);

            return response()->json(['user' => $user], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Verificar que el usuario autenticado sea super_user
            $authUser = auth()->user();
            if (! $authUser->hasRole(User::ROLE_SUPER_USER)) {
                return response()->json([
                    'message' => 'Solo los super usuarios pueden eliminar usuarios',
                ], Response::HTTP_FORBIDDEN);
            }

            $user = User::findOrFail($id);

            // Prevenir que se elimine a sí mismo
            if ($user->id === $authUser->id) {
                return response()->json([
                    'message' => 'No puedes eliminar tu propia cuenta',
                ], Response::HTTP_FORBIDDEN);
            }

            // Eliminar relaciones del usuario antes de eliminarlo
            // Eliminar suscripciones
            $user->subscriptions()->delete();

            // Eliminar órdenes relacionadas
            $user->orders()->delete();

            // Desvincular roles
            $user->roles()->detach();

            // Ahora eliminar el usuario
            $user->delete();

            return response()->json([
                'message' => 'Usuario eliminado exitosamente',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function list(Request $request)
    {
        // dd($request->all());
        $query = User::query()->with(['roles']);

        // Búsqueda por nombre completo, apellido y email
        if ($request->has('search') && ! empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                    ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
            });
        }

        // Filtro por rango de fechas
        if ($request->has('date_from') && ! empty($request->input('date_from'))) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to') && ! empty($request->input('date_to'))) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Aplicar ordenamiento dinámico o por defecto created_at desc
        if ($request->has('sort') && ! empty($request->input('sort'))) {
            $sort = json_decode($request->input('sort'), true);
            if (is_array($sort)) {
                foreach ($sort as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            }
        } else {
            // Ordenar por fecha de creación descendente por defecto (último primero)
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $this->getPerPage($request);
        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    public function getMe()
    {
        $user = auth()->user();
        $user->is_admin = $user->isAdmin();
        unset($user->roles);

        return response()->json(['user' => $user]);
    }

    public function changePassword(Request $request)
    {
        try {
            $user = auth()->user();

            // Validar los datos
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Verificar que la contraseña actual sea correcta
            if (! \Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Actualizar la contraseña
            $user->password = \Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
