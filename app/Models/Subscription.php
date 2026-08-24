<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'created_by_user_id',
        'enrolled_at',
        'access_expires_at',
    ];

    protected $appends = ['is_expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeByUser($query, $userId, ?bool $valid = null)
    {
        $query->where('user_id', $userId);
        if ($valid === true) {
            $query->valid();
        }

        if ($valid === false) {
            $query->invalid();
        }
    }

    public function scopeValid($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('access_expires_at')
                ->orWhere('access_expires_at', '>', now());
        });
    }

    public function scopeInvalid($query)
    {
        return $query->where('access_expires_at', '<', now());
    }

    public function getIsExpiredAttribute()
    {
        return $this->access_expires_at !== null && $this->access_expires_at < now();
    }
}
