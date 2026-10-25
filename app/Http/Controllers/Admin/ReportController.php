<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end] = $this->range($request);
        $period = $this->period($request);

        $orders = Order::revenue()->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        $summary = [
            'orders' => (clone $orders)->count(),
            'revenue' => (float) (clone $orders)->sum('total'),
            'items' => (int) OrderItem::whereIn('order_id', (clone $orders)->select('id'))->sum('quantity'),
            'average' => (float) (clone $orders)->avg('total'),
        ];

        $expression = $this->periodExpression($period);

        $trend = (clone $orders)
            ->selectRaw("{$expression} as bucket, COUNT(*) as total_orders, SUM(total) as revenue")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(function ($row) use ($period) {
                $row->label = $this->label($row->bucket, $period);

                return $row;
            });

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', Order::REVENUE_STATUSES)
            ->whereBetween('orders.paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.subtotal) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('sold')
            ->take(10)
            ->get();

        $chart = [
            'labels' => $trend->map(fn ($row) => $this->label($row->bucket, $period))->values(),
            'revenue' => $trend->map(fn ($row) => (float) $row->revenue)->values(),
            'orders' => $trend->map(fn ($row) => (int) $row->total_orders)->values(),
        ];

        return view('admin.reports.index', compact('summary', 'trend', 'topProducts', 'chart', 'start', 'end', 'period'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->range($request);

        $rows = Order::revenue()
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('paid_at')
            ->get(['order_number', 'customer_name', 'customer_phone', 'paid_at', 'subtotal', 'discount', 'shipping_cost', 'total', 'status']);

        $filename = 'laporan-penjualan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['No Pesanan', 'Pembeli', 'Telepon', 'Tanggal Bayar', 'Subtotal', 'Diskon', 'Ongkir', 'Total', 'Status']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->order_number,
                    $row->customer_name,
                    $row->customer_phone,
                    optional($row->paid_at)->format('Y-m-d H:i'),
                    $row->subtotal,
                    $row->discount,
                    $row->shipping_cost,
                    $row->total,
                    $row->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function range(Request $request): array
    {
        $start = $request->filled('start')
            ? Carbon::parse($request->input('start'))
            : now()->startOfMonth();

        $end = $request->filled('end')
            ? Carbon::parse($request->input('end'))
            : now();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    private function period(Request $request): string
    {
        $period = $request->string('period')->toString();

        return in_array($period, ['harian', 'bulanan', 'tahunan'], true) ? $period : 'harian';
    }

    private function periodExpression(string $period): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return match ($period) {
                'bulanan' => "strftime('%Y-%m', paid_at)",
                'tahunan' => "strftime('%Y', paid_at)",
                default => 'DATE(paid_at)',
            };
        }

        return match ($period) {
            'bulanan' => "DATE_FORMAT(paid_at, '%Y-%m')",
            'tahunan' => "DATE_FORMAT(paid_at, '%Y')",
            default => 'DATE(paid_at)',
        };
    }

    private function label(string $bucket, string $period): string
    {
        try {
            return match ($period) {
                'bulanan' => Carbon::createFromFormat('Y-m', $bucket)->translatedFormat('M Y'),
                'tahunan' => $bucket,
                default => Carbon::parse($bucket)->translatedFormat('d M Y'),
            };
        } catch (\Throwable $e) {
            return $bucket;
        }
    }
}
