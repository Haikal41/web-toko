<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('role', 'karyawan')->orderBy('name')->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        return back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, User $employee)
    {
        abort_unless($employee->role === 'karyawan', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($employee->id)],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $employee->name = $data['name'];
        $employee->email = $data['email'];
        $employee->phone = $data['phone'];
        $employee->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $employee->password = $data['password'];
        }

        $employee->save();

        return back()->with('success', 'Data karyawan diperbarui.');
    }

    public function destroy(User $employee)
    {
        abort_unless($employee->role === 'karyawan', 404);
        $employee->delete();

        return back()->with('success', 'Karyawan dihapus.');
    }
}
