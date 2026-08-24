<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Gateways\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderService extends BaseService
{
    public function createFromCart(array $items): array
    {

        try {
            $this->initTransactions();

            $order = Order::create([
                'number' => 'ORD-'.now()->format('YmdHis'),
                'user_id' => auth()->id(),
                'amount' => collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']),
                'currency' => 'COP',
                'status_id' => OrderStatus::PENDING->value,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'course_id' => $item['id'],
                    'unit_price' => $item['price'],
                    'description' => $item['title'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            $paymentData = PaymentGateway::getInstance()->createPaymentRequest($order);

            $this->commitTransactions();

            return $paymentData;
        } catch (\Exception $e) {
            $this->rollbackTransactions();
            throw $e;
        }

    }

    public function createSubscriptions(Order $order): void
    {
        if ($order->isPaid()) {
            foreach ($order->items as $item) {
                try {
                    app(OrderItemService::class)->createSubscription($item);
                } catch (\Throwable $exception) {
                    Log::error("Error creando suscripción para Item ID {$item->id}: ".$exception->getMessage());
                }
            }

            // Enviar notificación de compra completada
            try {
                $order->load(['items.course', 'user']);
                $order->user->notify(new \App\Notifications\PurchaseCompleted($order));
                Log::info("Correo de compra enviado al usuario {$order->user->email} para la orden {$order->number}");
            } catch (\Throwable $exception) {
                Log::error("Error enviando correo de compra para orden {$order->number}: ".$exception->getMessage());
            }
        }
    }
}
