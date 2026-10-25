<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                $settings = Setting::allSettings();
            } catch (\Throwable $e) {
                $settings = [];
            }

            $view->with('storeSettings', $settings);
            $view->with('cartCount', cart_count());

            if (auth()->check() && auth()->user()->isAdmin()) {
                $view->with('adminPendingCount', Order::where('status', 'pending')->count());
                $view->with('adminLowStockCount', Product::whereColumn('stock', '<=', 'low_stock_threshold')->count());
            }
        });
    }
}
