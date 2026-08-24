<?php

namespace App\Validators\Courses\Modules;

use App\Validators\Rules;
use LaravelServiceGateway\Validators\BaseValidator;

class ModuleCopyValidator extends BaseValidator
{
    public static function rules($data = []): array
    {
        return [
            'id' => Rules::rules()->module()->get(),
            'course_id' => Rules::rules()->course()->get(),
        ];
    }
}
