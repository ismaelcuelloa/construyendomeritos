<?php

namespace App\Validators\Courses\Modules;

use App\Validators\Rules;
use LaravelServiceGateway\Validators\BaseValidator;

class ModulesValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->module()->get();
        }

        return array_merge($rules, [
            'course_id' => Rules::rules()->course($required)->get(),
            'title' => Rules::rules()->string($required)->get(),
            'description' => Rules::rules()->string(false)->nullable()->get(),
            'pdf_files' => 'nullable|array',
            'pdf_files.*' => 'file|mimes:pdf|max:10240', // 10MB máximo cada archivo
        ]);
    }
}
