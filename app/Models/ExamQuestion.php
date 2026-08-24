<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question_text',
        'options',
        'correct_answer',
        'justification',
        'points',
        'order_no',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'float',
        'order_no' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
