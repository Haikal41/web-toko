<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'work_start',
        'work_end',
        'late_tolerance_minutes',
        'work_days',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'work_start' => '08:00:00',
            'work_end' => '17:00:00',
            'late_tolerance_minutes' => 15,
            'work_days' => '1,2,3,4,5,6',
        ]);
    }

    public function workDayNumbers(): array
    {
        return array_map('intval', array_filter(explode(',', $this->work_days)));
    }

    public function isWorkDay(int $dayOfWeekIso): bool
    {
        return in_array($dayOfWeekIso, $this->workDayNumbers(), true);
    }
}
