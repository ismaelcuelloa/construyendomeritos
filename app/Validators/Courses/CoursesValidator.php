<?php

namespace App\Validators\Courses;

use App\Validators\Rules;
use LaravelServiceGateway\Validators\BaseValidator;

class CoursesValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->course()->get();
        }

        return array_merge($rules, [
            'category_id' => Rules::rules()->category($required)->get(),
            'subcategory_id' => Rules::rules()->required(false)->nullable()->get(),
            'code' => Rules::rules()->string(false)->nullable()->get(),
            'grado' => Rules::rules()->string(false)->nullable()->get(),
            'title' => Rules::rules()->string($required, 255)->get(),
            'description' => Rules::rules()->string(false)->nullable()->get(),
            'price' => Rules::rules()->unsignedNumeric($required, true)->get(),
            'published' => Rules::rules()->required(false)->boolean()->get(),
            'active' => Rules::rules()->required(false)->boolean()->get(),
        ]);
    }
}
