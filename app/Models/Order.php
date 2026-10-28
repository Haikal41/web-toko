<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'diproses', 'dikirim', 'selesai', 'batal'];

    public const REVENUE_STATUSES = ['paid', 'diproses', 'dikirim', 'selesai'];

    public const CHANNELS = ['online', 'pos'];

    public const FULFILLMENTS = ['delivery', 'pickup'];

    /** Status yang masih boleh dibatalkan oleh kasir. */
    public const KASIR_CANCELLABLE_STATUSES = ['pending', 'paid', 'diproses'];

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
        'channel',
        'fulfillment',
        'cash_received',
        'change_amount',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
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
        return self::labelFor($this->status);
    }

    public function statusColor(): string
    {
        return self::colorFor($this->status);
    }

    public static function labelFor(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    public static function colorFor(string $status): string
    {
        return match ($status) {
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

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function isPos(): bool
    {
        return $this->channel === 'pos';
    }

    public function channelLabel(): string
    {
        return $this->isPos() ? 'Kasir / POS' : 'Online';
    }

    public function isDelivery(): bool
    {
        return $this->fulfillment !== 'pickup';
    }

    public function isPickup(): bool
    {
        return $this->fulfillment === 'pickup';
    }

    public function fulfillmentLabel(): string
    {
        return $this->isPickup() ? 'Ambil di toko' : 'Dikirim';
    }

    public function hasReceipt(): bool
    {
        return in_array($this->status, self::REVENUE_STATUSES, true);
    }

    /**
     * Struk publik memakai signed URL agar pembeli tamu tetap bisa membukanya
     * tanpa akun, sementara orang lain tidak bisa menebak nomor pesanan.
     */
    public function receiptUrl(int $days = 30): string
    {
        return URL::temporarySignedRoute('receipt.show', now()->addDays($days), [
            'orderNumber' => $this->order_number,
        ]);
    }

    public function canBeCancelledBy(User $user): bool
    {
        if ($this->status === 'batal') {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return $user->isKasir() && in_array($this->status, self::KASIR_CANCELLABLE_STATUSES, true);
    }
}
