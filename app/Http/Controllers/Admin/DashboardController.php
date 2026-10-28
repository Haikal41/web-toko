<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        $todayRevenue = Order::revenue()->whereDate('paid_at', $today)->sum('total');
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $monthRevenue = Order::revenue()->where('paid_at', '>=', $monthStart)->sum('total');
        $pendingOrders = Order::where('status', 'pending')->count();
        $lowStock = Product::whereColumn('stock', '<=', 'low_stock_threshold')->where('is_active', true)->count();

        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $revenueByDay = Order::revenue()
            ->where('paid_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(paid_at) as day, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $ordersByDay = Order::revenue()
            ->where('paid_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(paid_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart = [
            'labels' => $days->map(fn ($day) => Carbon::parse($day)->format('d M'))->values(),
            'revenue' => $days->map(fn ($day) => (float) ($revenueByDay[$day] ?? 0))->values(),
            'orders' => $days->map(fn ($day) => (int) ($ordersByDay[$day] ?? 0))->values(),
        ];

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', Order::REVENUE_STATUSES)
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.subtotal) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('sold')
            ->take(10)
            ->get();

        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentOrders = Order::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', [
            'isOwner' => auth()->user()->isOwner(),
            'todayRevenue' => $todayRevenue,
            'todayOrders' => $todayOrders,
            'monthRevenue' => $monthRevenue,
            'pendingOrders' => $pendingOrders,
            'lowStock' => $lowStock,
            'chart' => $chart,
            'topProducts' => $topProducts,
            'statusCounts' => $statusCounts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
