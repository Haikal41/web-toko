<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = ['date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'telat' => 'Telat',
            'tidak_hadir' => 'Tidak Hadir',
            default => ucfirst($this->status),
        };
    }

    public function statusCode(): string
    {
        return match ($this->status) {
            'hadir' => 'H',
            'telat' => 'T',
            default => 'A',
        };
    }
}
