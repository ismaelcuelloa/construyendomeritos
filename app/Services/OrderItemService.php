<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Validation\ValidationException;

class OrderItemService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function createSubscription($item): subscription
    {
        if (! $item->order->IsPaid()) {
            $this->error('La compra no fue pagada');
        }

        if ($item->subscription_id) {
            $this->error('La compra ya tiene una suscripción');
        }

        $subscription = app(SubscriptionService::class)->create([
            'user_id' => $item->order->user_id,
            'course_id' => $item->course_id,
        ]);

        $item->subscription_id = $subscription->id;
        $item->save();

        return $subscription;
    }
}
