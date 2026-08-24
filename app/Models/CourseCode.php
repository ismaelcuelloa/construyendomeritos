<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCode extends Model
{
    protected $fillable = [
        'course_id',
        'code',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
