<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLog extends Model
{
    protected $fillable = [
        'order_id',
        'recipient',
        'recipient_type',
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
}
