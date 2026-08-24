<?php

namespace App\Models;

use App\Models\Traits\Status;
use Illuminate\Database\Eloquent\Model;

/**
 * @method slug(string $slug)
 * @method withUserSubscription(null|bool $active = null)
 * @method byCategory($category_id)
 * @method visible(bool $visible=true)
 *
 * */
class Course extends Model
{
    use Status;

    protected $fillable = [
        'code',
        'grado',
        'title',
        'slug',
        'description',
        'price',
        'published',
        'category_id',
        'subcategory_id',
        'active',
    ];

    protected $casts = [
        'published' => 'boolean',
        'active' => 'boolean',
    ];

    protected $appends = ['price_formatted'];

    public function metadata()
    {
        return $this->hasOne(CourseMetadata::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function codes()
    {
        return $this->hasMany(CourseCode::class);
    }

    public function scopeSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeByCategory($query, $category_id)
    {
        return $query->where('category_id', $category_id);
    }

    public function getPriceFormattedAttribute()
    {
        return '$ '.number_format($this->price);
    }

    public function scopeWithUserSubscription($query, ?bool $active = null)
    {

        if (auth()->check() === false) {
            return $query;
        }

        $user = auth()->user();

        return $query->with(['subscriptions' => function ($query) use ($user, $active) {
            $query->byUser($user->id, $active);
        }]);

    }

    public function scopeWithUserPaidOrders($query)
    {
        if (auth()->check() === false) {
            return $query;
        }

        $user = auth()->user();

        return $query->with(['orderItems' => function ($query) use ($user) {
            $query->with('order') // Cargar la relación order
                ->whereHas('order', function ($orderQuery) use ($user) {
                    $orderQuery->where('user_id', $user->id)
                        ->where(function ($statusQuery) {
                            // Incluir órdenes pagadas
                            $statusQuery->where('status_id', \App\Enums\OrderStatus::PAID->value)
                                // O demos que no hayan expirado
                                ->orWhere(function ($demoQuery) {
                                    $demoQuery->where('status_id', \App\Enums\OrderStatus::DEMO->value)
                                        ->where('demo_expires_at', '>', now());
                                });
                        });
                });
        }]);
    }

    public function scopeSubscripted($query, string|int $userId, ?bool $active = null)
    {
        $query->where(function ($query) use ($userId, $active) {
            $query->whereHas('subscriptions', function ($query) use ($userId, $active) {
                $query->byUser($userId, $active);
            });
            if ($active === false) {
                $query->orWhereDoesntHave('subscriptions');
            }
        });

    }
}
