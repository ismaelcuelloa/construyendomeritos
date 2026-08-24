<?php

namespace App\Models;

use App\Models\Traits\Status;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use Status;

    protected $fillable = [
        'category_id',
        'parent_id',
        'title',
        'slug',
        'description',
        'published',
        'active',
        'image_id',
    ];

    protected $casts = [
        'published' => 'boolean',
        'active' => 'boolean',
        'description' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parent()
    {
        return $this->belongsTo(Subcategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Subcategory::class, 'parent_id');
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function scopeSlug($query, $slug)
    {
        $query->where('slug', $slug);
    }
}
