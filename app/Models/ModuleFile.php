<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFile extends Model
{
    protected $fillable = [
        'module_id',
        'file_id',
        'title',
        'description',
        'created_at',
        'updated_at',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
