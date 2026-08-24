<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    const ROLE_SUPER_USER = 'super_user';

    const ROLE_ADMIN = 'admin';

    const ROLE_USER = 'user';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'password',
        'current_session_id',
        'current_device_info',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['full_name', 'is_admin', 'is_super_user'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, Subscription::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->last_name;
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_SUPER_USER]);
    }

    public function getIsSuperUserAttribute(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_USER);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_SUPER_USER]);
    }

    public function isSuperUser(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_USER);
    }
}
