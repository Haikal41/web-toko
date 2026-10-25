<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $setting = AttendanceSetting::current();
        $today = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now()->toDateString())
            ->first();

        return view('attendance.index', compact('setting', 'today'));
    }

    public function scan(Request $request, AttendanceService $service)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $result = $service->scan($request->user(), $data['token'], $request);

        if ($request->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function history()
    {
        $attendances = auth()->user()->attendances()->latest('date')->paginate(20);

        return view('attendance.history', compact('attendances'));
    }
}
