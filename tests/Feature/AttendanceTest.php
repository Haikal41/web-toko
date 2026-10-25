<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\AttendanceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\CreatesShopData;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_employee_can_open_attendance_page(): void
    {
        $employee = $this->employee();

        $this->actingAs($employee)->get(route('attendance.index'))->assertOk();
        $this->actingAs($employee)->get(route('attendance.history'))->assertOk();
    }

    public function test_customer_cannot_open_attendance_page(): void
    {
        $this->actingAs($this->customer())->get(route('attendance.index'))->assertForbidden();
    }

    public function test_admin_can_generate_qr_token_and_employee_scans_in_and_out(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        $this->actingAs($admin)
            ->post(route('admin.attendance.generate'))
            ->assertRedirect();

        $token = AttendanceToken::latest('id')->first();

        $this->assertNotNull($token);

        $this->actingAs($employee)
            ->post(route('attendance.scan'), ['token' => $token->token])
            ->assertRedirect();

        $attendance = Attendance::where('user_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);
        $this->assertNull($attendance->check_out);

        $this->actingAs($employee)
            ->post(route('attendance.scan'), ['token' => $token->token])
            ->assertRedirect();

        $attendance->refresh();

        $this->assertNotNull($attendance->check_out);
    }

    public function test_scan_with_expired_token_is_rejected(): void
    {
        $employee = $this->employee();

        AttendanceToken::create([
            'token' => 'token-kedaluwarsa',
            'expires_at' => now()->subMinute(),
            'created_by' => $this->admin()->id,
        ]);

        $this->actingAs($employee)
            ->post(route('attendance.scan'), ['token' => 'token-kedaluwarsa'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Attendance::count());
    }

    public function test_scan_requires_token_field(): void
    {
        $employee = $this->employee();

        $this->actingAs($employee)
            ->post(route('attendance.scan'), [])
            ->assertSessionHasErrors('token');
    }

    public function test_late_arrival_is_marked_as_telat(): void
    {
        $admin = $this->admin();
        $employee = $this->employee();

        AttendanceSetting::current()->update([
            'work_start' => '08:00:00',
            'late_tolerance_minutes' => 5,
        ]);

        $this->travelTo(Carbon::parse(now()->toDateString().' 09:30:00'));

        $this->actingAs($admin)->post(route('admin.attendance.generate'));

        $token = AttendanceToken::latest('id')->first();

        $this->actingAs($employee)->post(route('attendance.scan'), ['token' => $token->token]);

        $attendance = Attendance::where('user_id', $employee->id)->first();

        $this->assertSame('telat', $attendance->status);

        $this->travelBack();
    }

    public function test_admin_can_update_attendance_settings_and_export_recap(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.attendance.settings'), [
            'work_start' => '09:00',
            'work_end' => '18:00',
            'late_tolerance_minutes' => 10,
            'work_days' => '1,2,3,4,5',
        ])->assertRedirect();

        $setting = AttendanceSetting::current();

        $this->assertSame('09:00:00', $setting->work_start);
        $this->assertSame(10, $setting->late_tolerance_minutes);

        $response = $this->actingAs($admin)->get(route('admin.attendance.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }
}
