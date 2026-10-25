<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'diproses', 'dikirim', 'selesai', 'batal'];

    public const REVENUE_STATUSES = ['paid', 'diproses', 'dikirim', 'selesai'];

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'coupon_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'MVA-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsappLog::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'paid' => 'green',
            'diproses' => 'blue',
            'dikirim' => 'indigo',
            'selesai' => 'teal',
            'batal' => 'red',
            default => 'gray',
        };
    }

    public function scopeRevenue($query)
    {
        return $query->whereIn('status', self::REVENUE_STATUSES);
    }
}
