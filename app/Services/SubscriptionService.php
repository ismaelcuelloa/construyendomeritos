<?php

namespace App\Services;

use App\Models\Subscription;
use App\Validators\Subscriptions\SubscriptionValidator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SubscriptionService extends BaseService
{
    /**
     * @throws ValidationException
     */
    public function create(array $data): Subscription
    {

        SubscriptionValidator::validate($data);

        try {
            $this->initTransactions();
            $subscription = new Subscription;
            $subscription->fill($data);

            $now = Carbon::now();
            if (empty($data['enrolled_at'])) {
                $subscription->enrolled_at = $now;
            }

            if (empty($data['access_expires_at'])) {
                // $subscription->access_expires_at = (clone $now)->addMonth();
            }

            $subscription->save();

            $this->commitTransactions();

            return $subscription;
        } catch (\Exception $exception) {
            $this->rollbackTransactions();
            throw $exception;
        }

    }
}
