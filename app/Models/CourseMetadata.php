<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMetadata extends Model
{
    protected $fillable = [
        'course_id',
        'description',
        'banner',
        'color',
        'custom_filter_value',
        'created_at',
        'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeCourse($query, $id)
    {
        return $query->where('course_id', $id);
    }
}
