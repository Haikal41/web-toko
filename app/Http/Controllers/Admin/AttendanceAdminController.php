<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\AttendanceToken;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceAdminController extends Controller
{
    public function index(AttendanceService $service)
    {
        $settings = AttendanceSetting::current();
        $currentToken = $service->currentToken();

        $todayAttendances = Attendance::with('user')
            ->whereDate('date', now()->toDateString())
            ->get()
            ->keyBy('user_id');

        $employees = User::where('role', 'karyawan')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.attendance.index', compact('settings', 'currentToken', 'todayAttendances', 'employees'));
    }

    public function generate(AttendanceService $service)
    {
        $service->generateToken(auth()->user());

        return back()->with('success', 'QR absensi baru dibuat. Berlaku 60 detik.');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'work_days' => ['required', 'string', 'max:20'],
        ]);

        AttendanceSetting::current()->update([
            'work_start' => $data['work_start'].':00',
            'work_end' => $data['work_end'].':00',
            'late_tolerance_minutes' => $data['late_tolerance_minutes'],
            'work_days' => $data['work_days'],
        ]);

        return back()->with('success', 'Pengaturan jam kerja diperbarui.');
    }

    public function recap(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $employees = User::where('role', 'karyawan')->orderBy('name')->get();
        $attendances = Attendance::whereBetween('date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->get()
            ->groupBy('user_id');

        $days = collect(range(1, $date->daysInMonth));

        return view('admin.attendance.recap', compact('employees', 'attendances', 'days', 'date', 'month'));
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $employees = User::where('role', 'karyawan')->orderBy('name')->get();
        $attendances = Attendance::whereBetween('date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->get()
            ->groupBy('user_id');

        $filename = 'rekap-absensi-'.$month.'.csv';

        return response()->streamDownload(function () use ($employees, $attendances, $date) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $headers = ['Nama'];
            foreach (range(1, $date->daysInMonth) as $day) {
                $headers[] = $day;
            }
            $headers[] = 'Hadir';
            $headers[] = 'Telat';
            $headers[] = 'Tidak Hadir';
            fputcsv($handle, $headers);

            foreach ($employees as $employee) {
                $records = ($attendances[$employee->id] ?? collect())->keyBy(fn ($row) => $row->date->format('j'));
                $row = [$employee->name];
                $counts = ['hadir' => 0, 'telat' => 0, 'tidak_hadir' => 0];

                foreach (range(1, $date->daysInMonth) as $day) {
                    $record = $records->get($day);
                    $row[] = $record?->statusCode() ?? '-';
                    if ($record) {
                        $counts[$record->status] = ($counts[$record->status] ?? 0) + 1;
                    }
                }

                $row[] = $counts['hadir'];
                $row[] = $counts['telat'];
                $row[] = $counts['tidak_hadir'];
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
