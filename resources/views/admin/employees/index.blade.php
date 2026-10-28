@extends('layouts.admin')

@section('title', 'Kelola Kasir')
@section('crumb', 'Kasir / Kelola')

@section('content')
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Daftar kasir</h3>
                <p>{{ $employees->total() }} kasir terdaftar</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>Email</th><th>WhatsApp</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td class="bold">{{ $employee->name }}</td>
                            <td class="small">{{ $employee->email }}</td>
                            <td class="small">{{ $employee->phone }}</td>
                            <td><span class="pill pill-{{ $employee->is_active ? 'green' : 'gray' }}">{{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('employee-{{ $employee->id }}').showModal()">Edit</button>
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost btn-sm" style="color:var(--red)" type="submit" data-confirm="Hapus kasir {{ $employee->name }}?">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <dialog id="employee-{{ $employee->id }}" style="border:0;border-radius:16px;padding:0;max-width:460px;width:92%;box-shadow:var(--shadow)">
                            <form action="{{ route('admin.employees.update', $employee) }}" method="POST" class="panel">
                                @csrf
                                @method('PUT')
                                <h3 style="font-size:1.05rem">Edit kasir</h3>
                                <div class="field">
                                    <label class="label">Nama</label>
                                    <input class="input" name="name" value="{{ $employee->name }}" required>
                                </div>
                                <div class="field">
                                    <label class="label">Email</label>
                                    <input type="email" class="input" name="email" value="{{ $employee->email }}" required>
                                </div>
                                <div class="field">
                                    <label class="label">WhatsApp</label>
                                    <input class="input" name="phone" value="{{ $employee->phone }}" required>
                                </div>
                                <div class="field">
                                    <label class="label">Password baru <span>(opsional)</span></label>
                                    <input type="password" class="input" name="password">
                                </div>
                                <label class="checkbox-row mb-2"><input type="checkbox" name="is_active" value="1" @checked($employee->is_active)> Akun aktif</label>
                                <div class="flex gap-sm">
                                    <button class="btn" type="submit">Simpan</button>
                                    <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                                </div>
                            </form>
                        </dialog>
                    @empty
                        <tr><td colspan="5" class="text-center muted" style="padding:2.5rem">Belum ada kasir. Tambahkan melalui form di samping.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel">{{ $employees->links() }}</div>
    </div>

    <div class="card panel" style="align-self:start">
        <h3 style="font-size:1.02rem">Tambah kasir</h3>
        <p class="tiny muted">Kasir dapat mengakses pesanan, membuat pesanan manual, dan membatalkan pesanan sampai status diproses.</p>
        <form action="{{ route('admin.employees.store') }}" method="POST" class="mt-2">
            @csrf
            <div class="field">
                <label class="label">Nama</label>
                <input class="input" name="name" required>
            </div>
            <div class="field">
                <label class="label">Email</label>
                <input type="email" class="input" name="email" required>
            </div>
            <div class="field">
                <label class="label">Nomor WhatsApp</label>
                <input class="input" name="phone" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="field">
                <label class="label">Password</label>
                <input type="password" class="input" name="password" required placeholder="Minimal 6 karakter">
            </div>
            <button class="btn btn-block" type="submit">Tambah kasir</button>
        </form>
    </div>
</div>
@endsection

