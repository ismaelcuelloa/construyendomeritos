<?php

namespace App\Validators\Courses\Modules\ModuleFiles;

use App\Validators\Rules;
use Illuminate\Validation\Rules\File;
use LaravelServiceGateway\Validators\BaseValidator;

class ModuleFilesValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->moduleFile()->get();
        }

        return array_merge($rules, [
            'module_id' => Rules::rules()->module($required)->get(),
            'file_id' => Rules::rules()->file(false)->get(),
            'title' => Rules::rules()->string($required)->get(),
            'description' => Rules::rules()->string(false)->nullable()->get(),
            'file' => Rules::rules()->required(false)->add(File::types(['pdf']))->get(),
        ]);
    }
}
