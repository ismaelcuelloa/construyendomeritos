<?php

namespace App\Validators\Categories;

use App\Models\Category;
use App\Validators\Rules;
use LaravelServiceGateway\Validators\BaseValidator;

class CategoriesValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->category()->get();
        }

        return array_merge($rules, [
            'code' => Rules::rules()->unique(Category::class, 'code', null, $id)->get(),
            'title' => Rules::rules()->string($required, 255)->get(),
            'description' => Rules::rules()->string(false)->nullable()->get(),
            'published' => Rules::rules()->required(false)->boolean()->get(),
            'active' => Rules::rules()->required(false)->boolean()->get(),
            'image' => Rules::rules()->required(false)->add(\Illuminate\Validation\Rules\File::image())->get(),
            'enable_custom_filter' => Rules::rules()->required(false)->boolean()->get(),
            'custom_filter_options' => Rules::rules()->required(false)->nullable()->get(),
            'enable_subcategories' => Rules::rules()->required(false)->boolean()->get(),
        ]);
    }
}
