<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'exam_id',
        'score',
        'total_points',
        'passed',
        'started_at',
        'finished_at',
        'last_question_index',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'score' => 'integer',
        'total_points' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamUserAnswer::class, 'attempt_id');
    }
}
