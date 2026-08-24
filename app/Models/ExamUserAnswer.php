<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamUserAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
