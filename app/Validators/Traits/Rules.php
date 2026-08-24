<?php

namespace App\Validators\Traits;

use Illuminate\Validation\Rule;

trait Rules
{
    private function _required(bool $required = true): string
    {
        return ($required) ? 'required' : 'sometimes';
    }

    private function _exist($modelClass, $column = null, ?\Closure $callback = null): \Illuminate\Validation\Rules\Exists
    {
        if ($this->isClassModel($modelClass) === false) {
            throw new \InvalidArgumentException("Class {$modelClass} must be a valid Eloquent model.");
        }

        $model = new $modelClass;
        $table = $this->getTable($modelClass);
        $keyName = $column ?? $model->getKeyName();

        $rule = Rule::exists($table, $keyName);

        if ($callback) {
            $rule->where($callback);
        }

        return $rule;
    }

    private function _model($modelClass, $required = true, $column = null, ?\Closure $callback = null): array
    {
        return [$this->_required($required), $this->_id(), $this->_exist($modelClass, $column, $callback)];
    }

    private function _id(): array
    {
        return $this->_unsignedInt(false);
    }

    private function _email($required = true, $max = null, $min = null): array
    {
        return [$this->_string($required, $max, $min), 'email'];
    }

    private function _boolean(): string
    {
        return 'boolean';
    }

    private function _int(): string
    {
        return 'integer';
    }

    private function _nullable(): string
    {
        return 'nullable';
    }

    private function _unsignedNumeric(bool $includeZero = false): array
    {
        return ['numeric', ($includeZero) ? 'gte:0' : 'gt:0'];
    }

    private function _unsignedInt(bool $includeZero = false): array
    {
        return [$this->_int(), ($includeZero) ? 'gte:0' : 'gt:0'];
    }

    private function _string(bool $required = true, $max = null, $min = null): array
    {
        return [$this->_required($required), 'string', ($max) ? 'max:'.$max : null, ($min) ? 'min:'.$min : null];
    }

    private function _date(): string
    {
        return 'date_format:Y-m-d';
    }

    private function _between(...$options): string
    {
        return 'between:'.implode(',', $options);
    }

    private function _unique($modelClass, $column = null, ?\Closure $callback = null, $ignore = null, $ignoreColumn = null): \Illuminate\Validation\Rules\Unique
    {
        if (! self::isClassModel($modelClass)) {
            throw new \InvalidArgumentException("Class {$modelClass} must be a valid Eloquent model.");
        }

        $model = new $modelClass;
        $table = $this->getTable($modelClass);
        $keyName = $column ?? $model->getKeyName();

        $rule = Rule::unique($table, $keyName);

        if ($ignore !== null) {
            $rule->ignore($ignore, $ignoreColumn ?? $model->getKeyName());
        }

        if ($callback) {
            $rule->where($callback);
        }

        return $rule;
    }

    private function _array(): string
    {
        return 'array';
    }

    private function _min($min): string
    {
        return "min:$min";
    }
}
