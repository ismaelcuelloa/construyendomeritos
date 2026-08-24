<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'description',
        'time_limit',
        'max_attempts',
        'passing_score',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'passing_score' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order_no');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function userAttempts($userId)
    {
        return $this->attempts()->where('user_id', $userId);
    }

    public function remainingAttempts($userId): ?int
    {
        if ($this->max_attempts === null) {
            return null;
        }
        $used = $this->attempts()->where('user_id', $userId)->count();
        return max(0, $this->max_attempts - $used);
    }

    public function totalPoints(): int
    {
        return $this->questions()->sum('points');
    }
}
