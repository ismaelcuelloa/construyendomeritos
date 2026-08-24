<?php

namespace App\Validators\Files;

use App\Models\File;
use App\Validators\Rules;
use Illuminate\Validation\Rule;
use LaravelServiceGateway\Validators\BaseValidator;

class FilesValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        $create = $data['create'] ?? true;
        $required = $create;
        $id = $data['id'] ?? null;

        $rules = [];
        if (! $create) {
            $rules['id'] = Rules::rules()->file()->get();
        }

        return array_merge($rules, [
            'name' => Rules::rules()->string(false, 255)->nullable()->get(),
            'path' => Rules::rules()->string(false)->nullable()->get(),
            'type' => Rules::rules()->required($required)->unsignedInt()->add(Rule::in([File::TYPE_DOCUMENT, File::TYPE_IMAGE, File::TYPE_VIDEO]))->get(),
            'file' => Rules::rules()->required($required)->add(\Illuminate\Validation\Rules\File::default())->get(),
        ]);
    }
}
