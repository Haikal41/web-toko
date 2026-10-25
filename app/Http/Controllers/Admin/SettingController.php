<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', ['settings' => Setting::allSettings()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_tagline' => ['nullable', 'string', 'max:150'],
            'store_description' => ['nullable', 'string', 'max:500'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:20'],
            'store_email' => ['nullable', 'email', 'max:150'],
            'wa_admin_number' => ['nullable', 'string', 'max:20'],
            'shipping_flat' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'store_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['wa_admin_number'] = $data['wa_admin_number'] ?? null;

        if ($request->hasFile('store_logo')) {
            $old = Setting::get('store_logo');

            if ($old) {
                Storage::disk('public')->delete($old);
            }

            $data['store_logo'] = $request->file('store_logo')->store('branding', 'public');
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan toko disimpan.');
    }
}
