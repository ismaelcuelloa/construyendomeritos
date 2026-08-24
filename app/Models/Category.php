<?php

namespace App\Models;

use App\Models\Traits\Status;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Status;

    protected $fillable = [
        'code',
        'title',
        'slug',
        'description',
        'published',
        'active',
        'image_id',
        'enable_custom_filter',
        'custom_filter_options',
        'enable_subcategories',
    ];

    protected $casts = [
        'published' => 'boolean',
        'active' => 'boolean',
        'description' => 'string',
        'enable_custom_filter' => 'boolean',
        'custom_filter_options' => 'array',
        'enable_subcategories' => 'boolean',
    ];

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function scopeSlug($query, $slug)
    {
        $query->where('slug', $slug);
    }
}
