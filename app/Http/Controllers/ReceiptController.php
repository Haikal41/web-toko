<?php

namespace App\Http\Controllers;

use App\Models\Order;

class ReceiptController extends Controller
{
    public function show(string $orderNumber)
    {
        $order = Order::with(['items', 'creator', 'payments'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('admin.orders.receipt', compact('order'));
    }
}
