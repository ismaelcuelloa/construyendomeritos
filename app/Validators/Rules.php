<?php

namespace App\Validators;

use App\Models\Category;
use App\Models\Course;
use App\Models\File;
use App\Models\Module;
use App\Models\ModuleFile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Role;

/**
 * @method self required(bool $required = true)
 * @method self exist(string $modelClass, string|null $column = null, \Closure|null $callback = null)
 * @method self model(string $modelClass, bool $required = true, string|null $column = null, \Closure|null $callback = null)
 * @method self id()
 * @method self email(bool $required = true, int|null $max = null, int|null $min = null)
 * @method self boolean()
 * @method self int()
 * @method self nullable()
 * @method self unsignedNumeric(bool $includeZero = false)
 * @method self unsignedInt(bool $includeZero = false)
 * @method self string(bool $required = true, int|null $max = null, int|null $min = null)
 * @method self date()
 * @method self between(mixed ...$options)
 * @method self unique(string $modelClass, string|null $column = null, \Closure|null $callback = null, mixed $ignore = null, string|null $ignoreColumn = null)
 * @method self array()
 * @method self min(int $min)
 */
class Rules
{
    use Traits\Rules;

    private array $rules = [];

    public static function rules(): Rules
    {
        return new self;
    }

    public function get(): array
    {
        return $this->rules;
    }

    public function add($rule): self
    {
        if (is_array($rule)) {
            $this->merge($rule);
        } else {
            $this->rules[] = $rule;
        }

        return $this;
    }

    private function merge($rules): void
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    private function isClassModel($modelClass): bool
    {
        return class_exists($modelClass) && is_subclass_of($modelClass, Model::class);
    }

    private function getTable($modelClass): string
    {
        $model = new $modelClass;
        $table = explode('.', $model->getTable());

        return end($table);
    }

    public function user(bool $required = true): self
    {
        return $this->add($this->_model(User::class, $required));
    }

    public function course(bool $required = true): self
    {
        return $this->add($this->_model(Course::class, $required));
    }

    public function category(bool $required = true): self
    {
        return $this->add($this->_model(Category::class, $required));
    }

    public function module(bool $required = true): self
    {
        return $this->add($this->_model(Module::class, $required));
    }

    public function moduleFile(bool $required = true): self
    {
        return $this->add($this->_model(ModuleFile::class, $required));
    }

    public function file(bool $required = true): self
    {
        return $this->add($this->_model(File::class, $required));
    }

    public function subscription(bool $required = true): self
    {
        return $this->add($this->_model(Subscription::class, $required));
    }

    public function role(bool $required = true): self
    {
        return $this->string($required)->exist(Role::class, 'name');
    }

    public function __call($method, $arguments)
    {

        $method = '_'.$method;
        if (method_exists($this, $method)) {
            $result = call_user_func([$this, $method], ...$arguments);

            return $this->add($result);
        }

        return $this;
        // throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
