<?php

use App\Models\Setting;
use App\Services\CartService;

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $value): string
    {
        return 'Rp'.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('store_name')) {
    function store_name(): string
    {
        return (string) setting('store_name', 'Multiverse Aksesoris');
    }
}

if (! function_exists('store_phone')) {
    function store_phone(): string
    {
        return (string) (setting('store_phone') ?: config('store.phone', ''));
    }
}

if (! function_exists('store_email')) {
    function store_email(): string
    {
        return (string) (setting('store_email') ?: config('store.email', ''));
    }
}

if (! function_exists('store_logo')) {
    function store_logo(): ?string
    {
        $path = setting('store_logo');

        return $path ? asset('storage/'.$path) : null;
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $phone, string $message = ''): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return 'https://wa.me/'.$digits.($message !== '' ? '?text='.urlencode($message) : '');
    }
}

if (! function_exists('cart_count')) {
    function cart_count(): int
    {
        try {
            return app(CartService::class)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
