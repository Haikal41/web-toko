<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLog extends Model
{
    public const TYPE_PAYMENT_RECEIPT = 'payment_receipt';

    public const TYPE_MANUAL_RESEND = 'manual_resend';

    protected $fillable = [
        'order_id',
        'recipient',
        'recipient_type',
        'message_type',
        'message',
        'status',
        'provider_response',
        'attempts',
        'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messageTypeLabel(): string
    {
        return $this->message_type === self::TYPE_MANUAL_RESEND ? 'Kirim ulang manual' : 'Struk otomatis';
    }
}
