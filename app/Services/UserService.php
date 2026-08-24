<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Validators\Users\UserValidator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $data): User
    {

        UserValidator::validate($data);

        try {
            $this->initTransactions();
            $user = new User;
            $user->fill($data);
            $user->password = Hash::make($data['password']);
            $user->save();

            if (! empty($data['role'])) {
                $user->assignRole($data['role']);
            }

            // Si se enviaron cursos, crear orden y suscripciones automáticamente
            if (! empty($data['courses']) && is_array($data['courses'])) {
                $orderStatus = $data['order_status'] ?? OrderStatus::PENDING->value;
                $this->createOrderAndSubscriptions($user, $data['courses'], $orderStatus);
            }

            $this->commitTransactions();

            return $user;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }

    }

    public function update(string|int $id, array $data): User
    {

        $data['id'] = $id;
        $data['create'] = false;
        UserValidator::validate($data);

        try {
            $this->initTransactions();
            $user = User::find($id);
            $user->fill($data);

            if (isset($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (! empty($data['role'])) {
                $user->syncRoles($data['role']);
            }

            $this->commitTransactions();

            return $user;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }

    }

    /**
     * Crear orden y suscripciones automáticamente para los cursos seleccionados
     */
    private function createOrderAndSubscriptions(User $user, array $courseIds, ?int $orderStatus = null): void
    {
        if (empty($courseIds)) {
            return;
        }

        // Obtener los cursos
        $courses = Course::whereIn('id', $courseIds)->get();

        if ($courses->isEmpty()) {
            return;
        }

        // Calcular el total
        $amount = $courses->sum('price');

        // Usar el estado proporcionado o PENDING por defecto
        $statusId = $orderStatus ?? OrderStatus::PENDING->value;

        // Crear la orden con el estado especificado
        $order = Order::create([
            'number' => 'ORD-'.now()->format('YmdHis'),
            'user_id' => $user->id,
            'amount' => $amount,
            'currency' => 'COP',
            'status_id' => $statusId,
            // Solo agregar paid_at si el estado es PAID
            'paid_at' => $statusId == OrderStatus::PAID->value ? now() : null,
        ]);

        // Crear los items de la orden
        foreach ($courses as $course) {
            $order->items()->create([
                'course_id' => $course->id,
                'unit_price' => $course->price,
                'description' => $course->title,
                'quantity' => 1,
                'total' => $course->price,
            ]);
        }

        // Crear las suscripciones automáticamente
        $orderItemService = app(OrderItemService::class);
        foreach ($order->items as $item) {
            try {
                $orderItemService->createSubscription($item);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error("Error creando suscripción para Item ID {$item->id}: ".$exception->getMessage());
            }
        }
    }
}
