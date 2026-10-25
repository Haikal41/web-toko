<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceToken extends Model
{
    protected $fillable = ['token', 'expires_at', 'created_by'];

    protected $casts = ['expires_at' => 'datetime'];

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }
}
