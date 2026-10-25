<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\AttendanceToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceService
{
    public function generateToken(User $admin): AttendanceToken
    {
        AttendanceToken::where('expires_at', '<', now()->subDay())->delete();

        return AttendanceToken::create([
            'token' => Str::random(64),
            'expires_at' => now()->addSeconds(60),
            'created_by' => $admin->id,
        ]);
    }

    public function currentToken(): ?AttendanceToken
    {
        return AttendanceToken::where('expires_at', '>', now())->latest()->first();
    }

    /**
     * @return array{ok:bool, message:string, attendance:?Attendance}
     */
    public function scan(User $user, string $token, Request $request): array
    {
        $tokenModel = AttendanceToken::where('token', $token)->first();

        if (! $tokenModel || ! $tokenModel->isValid()) {
            return ['ok' => false, 'message' => 'QR kedaluwarsa atau tidak valid. Minta admin memperbarui QR.', 'attendance' => null];
        }

        $setting = AttendanceSetting::current();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first() ?? new Attendance([
                'user_id' => $user->id,
                'date' => $today,
            ]);

        $now = now();
        $workStart = Carbon::parse($today.' '.$setting->work_start);
        $lateLimit = $workStart->copy()->addMinutes($setting->late_tolerance_minutes);

        if ($attendance->exists && $attendance->check_out) {
            return ['ok' => false, 'message' => 'Anda sudah absen masuk dan pulang hari ini.', 'attendance' => $attendance];
        }

        if (! $attendance->exists || ! $attendance->check_in) {
            $attendance->check_in = $now->format('H:i:s');
            $attendance->status = $now->gt($lateLimit) ? 'telat' : 'hadir';
            $attendance->ip_address = $request->ip();
            $attendance->user_agent = substr((string) $request->userAgent(), 0, 255);
            $attendance->save();

            $label = $attendance->status === 'telat' ? 'Telat' : 'Hadir';

            return ['ok' => true, 'message' => 'Absen masuk berhasil pukul '.$now->format('H:i').' ('.$label.').', 'attendance' => $attendance];
        }

        $attendance->check_out = $now->format('H:i:s');
        $attendance->ip_address = $request->ip();
        $attendance->user_agent = substr((string) $request->userAgent(), 0, 255);
        $attendance->save();

        return ['ok' => true, 'message' => 'Absen pulang berhasil pukul '.$now->format('H:i').'.', 'attendance' => $attendance];
    }
}
