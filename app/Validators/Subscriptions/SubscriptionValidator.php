<?php

namespace App\Validators\Subscriptions;

use App\Validators\Rules;
use LaravelServiceGateway\Validators\BaseValidator;

class SubscriptionValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->subscription()->get();
        }

        return array_merge($rules, [
            'user_id' => Rules::rules()->user()->get(),
            'course_id' => Rules::rules()->course()->get(),
            'enrolled_at' => Rules::rules()->required(false)->date()->get(),
            'access_expires_at' => Rules::rules()->required(false)->date()->get(),
        ]);
    }
}
