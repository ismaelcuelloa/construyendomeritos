<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'created_by_user_id',
        'amount',
        'currency',
        'status_id',
        'payu_order_id',
        'payu_transaction_id',
        'payment_method',
        'response_code',
        'description',
        'paid_at',
        'demo_expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'demo_expires_at' => 'datetime',
    ];

    protected $appends = ['status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool
    {
        return $this->status_id === OrderStatus::PAID->value;
    }

    public function isPending(): bool
    {
        return $this->status_id === OrderStatus::PENDING->value;
    }

    public function isProcessing(): bool
    {
        return $this->status_id === OrderStatus::PROCESSING->value;
    }

    public function isDemo(): bool
    {
        return $this->status_id === OrderStatus::DEMO->value;
    }

    public function demoExpired(): bool
    {
        return $this->isDemo() && $this->demo_expires_at && now()->isAfter($this->demo_expires_at);
    }

    public function hasValidAccess(): bool
    {
        // Pagado = acceso válido
        if ($this->isPaid()) {
            return true;
        }

        // Demo = válido solo si no expiró
        if ($this->isDemo()) {
            return !$this->demoExpired();
        }

        return false;
    }

    public function getStatusAttribute(): string
    {
        try {
            return OrderStatus::from($this->status_id)->label();
        } catch (\ValueError $e) {
            return 'Estado desconocido';
        }
    }
}
