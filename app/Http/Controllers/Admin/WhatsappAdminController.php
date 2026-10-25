<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappAdminController extends Controller
{
    public function index()
    {
        $logs = WhatsappLog::with('order')->latest()->paginate(20);

        return view('admin.whatsapp.index', compact('logs'));
    }

    public function test(Request $request, WhatsappService $service)
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:500'],
        ]);

        $result = $service->send($data['number'], $data['message']);

        return back()->with($result['ok'] ? 'success' : 'error', 'Hasil pengiriman: '.$result['response']);
    }
}
