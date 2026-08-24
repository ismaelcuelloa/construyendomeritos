<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ProcessPaidOrder extends Command
{
    protected $signature = 'order:process-paid {orderNumber}';

    protected $description = 'Process a paid order and create subscriptions';

    public function handle()
    {
        $orderNumber = $this->argument('orderNumber');

        $order = Order::where('number', $orderNumber)->first();

        if (! $order) {
            $this->error("Order {$orderNumber} not found");

            return 1;
        }

        // Update order status to paid
        $order->status_id = \App\Enums\OrderStatus::PAID->value;
        $order->paid_at = now();
        $order->save();

        $this->info("Order {$orderNumber} marked as paid");

        // Create subscriptions
        app(OrderService::class)->createSubscriptions($order);

        $this->info("Subscriptions created for order {$orderNumber}");
        $this->info('User can now access their courses');

        return 0;
    }
}
