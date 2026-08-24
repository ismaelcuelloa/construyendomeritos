<?php

namespace App\Validators\Users;

use App\Models\User;
use App\Validators\Rules;
use Illuminate\Validation\Rules\Password;
use LaravelServiceGateway\Validators\BaseValidator;

class UserValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->user()->get();
        }

        return array_merge($rules, [
            'name' => Rules::rules()->string($required, 255)->get(),
            'last_name' => Rules::rules()->string(false, 255)->get(),
            'email' => Rules::rules()->email($required, 255)->unique(User::class, 'email', null, $id)->get(),
            'phone' => Rules::rules()->string($required, 20)->get(),
            'password' => Rules::rules()->string($required, 255)->add(Password::defaults())->get(),
            'role' => Rules::rules()->role($required)->get(),
            'courses' => ['nullable', 'array'],
            'courses.*' => Rules::rules()->course(false)->get(),
            'order_status' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
        ]);
    }
}
