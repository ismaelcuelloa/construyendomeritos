<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    const TYPE_DOCUMENT = '1';

    const TYPE_IMAGE = '2';

    const TYPE_VIDEO = '3';

    protected $appends = ['url', 'full_name'];

    protected $hidden = ['path', 'name'];

    protected $fillable = [
        'name',
        'path',
        'type',
        'created_at',
        'updated_at',
    ];

    public function moduleFiles()
    {
        return $this->belongsTo(ModuleFile::class);
    }

    public function getUrlAttribute()
    {
        return $this->type == 2 ? $this->getURL() : null;
    }

    public function getFullNameAttribute()
    {
        return $this->type == 2 ? $this->getFullName() : null;
    }

    public function getFullName(): string
    {
        return $this->path.'/'.$this->name;
    }

    public function getURL()
    {
        return url($this->getFullName());
    }
}
