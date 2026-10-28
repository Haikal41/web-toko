<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedSettings();
        $this->seedCategoriesAndProducts();
        $this->seedCoupons();
        $this->seedDemoOrders();
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(['email' => 'owner@multiverse.test'], [
            'name' => 'Owner Multiverse',
            'phone' => '081234567890',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'kasir@multiverse.test'], [
            'name' => 'Rina Kasir',
            'phone' => '081234567891',
            'password' => 'password',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'kasir2@multiverse.test'], [
            'name' => 'Budi Kasir',
            'phone' => '081234567892',
            'password' => 'password',
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    private function seedSettings(): void
    {
        $settings = [
            'store_name' => 'Multiverse Aksesoris',
            'store_tagline' => 'Aksesoris custom untuk hadiah yang tak terlupakan.',
            'store_description' => 'Gantungan kunci, boneka, pigura, dan piala yang bisa dipersonalisasi dengan nama atau pesan spesial. Cocok untuk wisuda, ulang tahun, pernikahan, dan momen berharga lainnya.',
            'store_address' => 'Jl. Kenangan No. 88, Bandung, Jawa Barat',
            'store_phone' => '6289502646082',
            'store_email' => 'alifhaikl41@gmail.com',
            'wa_admin_number' => '6289502646082',
            'shipping_flat' => '15000',
            'low_stock_threshold' => '5',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedCategoriesAndProducts(): void
    {
        $categories = [
            'gantungan-kunci' => ['Gantungan Kunci', '🔑'],
            'boneka' => ['Boneka', '🧸'],
            'pigura' => ['Pigura', '🖼️'],
            'piala' => ['Piala', '🏆'],
            'lainnya' => ['Lainnya', '🎁'],
        ];

        $categoryModels = [];

        foreach ($categories as $slug => [$name, $emoji]) {
            $categoryModels[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
        }

        $products = [
            [
                'category' => 'gantungan-kunci', 'name' => 'Gantungan Kunci Akrilik Nama Custom', 'price' => 25000, 'stock' => 50,
                'personal' => true, 'emoji' => '🔑',
                'description' => 'Gantungan kunci akrilik bening dengan nama custom. Cocok untuk souvenir ulang tahun, wisuda, atau hadiah kecil untuk sahabat.',
                'variants' => [['Bening', 0, 30], ['Warna Pastel', 2500, 20]],
            ],
            [
                'category' => 'gantungan-kunci', 'name' => 'Gantungan Kunci Boneka Rajut Mini', 'price' => 35000, 'stock' => 40,
                'personal' => false, 'emoji' => '🐻',
                'description' => 'Boneka rajut mini yang lucu, digantungkan pada tas atau kunci. Tersedia beberapa pilihan warna.',
                'variants' => [['Cokelat', 0, 20], ['Pink', 0, 12], ['Krem', 0, 8]],
            ],
            [
                'category' => 'gantungan-kunci', 'name' => 'Gantungan Kunci Metal Custom', 'price' => 45000, 'stock' => 30,
                'personal' => true, 'emoji' => '✨',
                'description' => 'Gantungan kunci metal premium dengan ukiran nama. Tahan lama dan terlihat elegan untuk hadiah formal.',
                'variants' => [],
            ],
            [
                'category' => 'boneka', 'name' => 'Boneka Beruang Mini 15 cm', 'price' => 85000, 'stock' => 25,
                'personal' => false, 'emoji' => '🧸',
                'description' => 'Boneka beruang berbulu lembut ukuran 15 cm. Teman tidur dan hadiah manis untuk orang tersayang.',
                'variants' => [['Cokelat', 0, 15], ['Putih', 0, 10]],
            ],
            [
                'category' => 'boneka', 'name' => 'Boneka Kelinci Plush', 'price' => 95000, 'stock' => 20,
                'personal' => false, 'emoji' => '🐰',
                'description' => 'Boneka kelinci plush dengan telinga panjang yang menggemaskan. Jahitan rapi dan bahan hypoallergenic.',
                'variants' => [],
            ],
            [
                'category' => 'boneka', 'name' => 'Boneka Custom Foto Wajah', 'price' => 150000, 'stock' => 15,
                'personal' => true, 'emoji' => '🎎',
                'description' => 'Boneka custom dengan wajah sesuai foto. Kirimkan foto terbaikmu, kami buatkan boneka yang mirip!',
                'variants' => [],
            ],
            [
                'category' => 'pigura', 'name' => 'Pigura Akrilik Custom Nama', 'price' => 75000, 'stock' => 30,
                'personal' => true, 'emoji' => '🖼️',
                'description' => 'Pigura akrilik dengan ukiran nama dan tanggal spesial. Ideal untuk hadiah wisuda dan anniversary.',
                'variants' => [['10 x 15 cm', 0, 18], ['15 x 20 cm', 15000, 12]],
            ],
            [
                'category' => 'pigura', 'name' => 'Pigura Kayu 4R Minimalis', 'price' => 65000, 'stock' => 35,
                'personal' => false, 'emoji' => '🪵',
                'description' => 'Pigura kayu minimalis untuk foto ukuran 4R. Finishing halus dengan dudukan meja.',
                'variants' => [['Natural', 0, 20], ['Hitam', 0, 15]],
            ],
            [
                'category' => 'pigura', 'name' => 'Pigura Kenang-kenangan Wisuda', 'price' => 110000, 'stock' => 20,
                'personal' => true, 'emoji' => '🎓',
                'description' => 'Pigura kenang-kenangan wisuda dengan nama, jurusan, dan tahun lulus. Dibuat rapi sebagai memori abadi.',
                'variants' => [],
            ],
            [
                'category' => 'piala', 'name' => 'Piala Penghargaan Emas', 'price' => 125000, 'stock' => 20,
                'personal' => true, 'emoji' => '🏆',
                'description' => 'Piala penghargaan finishing emas dengan nama dan keterangan custom. Cocok untuk lomba dan acara sekolah.',
                'variants' => [['20 cm', 0, 12], ['25 cm', 25000, 8]],
            ],
            [
                'category' => 'piala', 'name' => 'Piala Akrilik Custom', 'price' => 95000, 'stock' => 25,
                'personal' => true, 'emoji' => '🥇',
                'description' => 'Piala akrilik modern dengan ukiran laser. Ringan, elegan, dan bisa dipersonalisasi sesuai acara.',
                'variants' => [],
            ],
            [
                'category' => 'piala', 'name' => 'Piala Kristal Wisuda', 'price' => 175000, 'stock' => 12,
                'personal' => true, 'emoji' => '💎',
                'description' => 'Piala kristal premium untuk momen wisuda. Ukiran nama dan tahun lulus membuatnya terasa istimewa.',
                'variants' => [],
            ],
            [
                'category' => 'lainnya', 'name' => 'Kotak Hadiah Premium', 'price' => 30000, 'stock' => 60,
                'personal' => false, 'emoji' => '🎁',
                'description' => 'Kotak hadiah premium dengan pita satin. Membuat setiap hadiah terasa lebih istimewa.',
                'variants' => [],
            ],
            [
                'category' => 'lainnya', 'name' => 'Kartu Ucapan Custom', 'price' => 15000, 'stock' => 100,
                'personal' => true, 'emoji' => '💌',
                'description' => 'Kartu ucapan dengan pesan custom yang ditulis tangan. Pelengkap sempurna untuk hadiahmu.',
                'variants' => [],
            ],
        ];

        foreach ($products as $index => $data) {
            $slug = Str::slug($data['name']);
            $imagePath = 'products/'.$slug.'.svg';
            $this->writeProductSvg($imagePath, $data['emoji'], $data['name']);

            $product = Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $categoryModels[$data['category']]->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'requires_personalization' => $data['personal'],
                'is_active' => true,
                'low_stock_threshold' => 5,
            ]);

            if ($product->images()->count() === 0) {
                $product->images()->create([
                    'path' => $imagePath,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }

            if ($product->variants()->count() === 0) {
                foreach ($data['variants'] as [$name, $adjustment, $stock]) {
                    $product->variants()->create([
                        'name' => $name,
                        'price_adjustment' => $adjustment,
                        'stock' => $stock,
                    ]);
                }
            }
        }
    }

    private function writeProductSvg(string $path, string $emoji, string $name): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $gradients = [
            ['#efe7ff', '#ffd9e6'], ['#e6f7ff', '#e6e6ff'], ['#fff4d9', '#ffe0e6'],
            ['#e8fff3', '#e6f0ff'], ['#ffe9f0', '#f0e6ff'],
        ];

        [$from, $to] = $gradients[crc32($name) % count($gradients)];

        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$from}"/>
      <stop offset="100%" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#bg)"/>
  <circle cx="480" cy="120" r="130" fill="#ffffff" opacity="0.35"/>
  <circle cx="110" cy="500" r="90" fill="#ffffff" opacity="0.28"/>
  <text x="300" y="320" font-size="220" text-anchor="middle" dominant-baseline="middle">{$emoji}</text>
  <text x="300" y="500" font-size="26" font-family="Segoe UI, sans-serif" fill="#5b3df5" font-weight="600" text-anchor="middle">Multiverse Aksesoris</text>
  <text x="300" y="535" font-size="18" font-family="Segoe UI, sans-serif" fill="#8a82a8" text-anchor="middle">{$safeName}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);
    }

    private function seedCoupons(): void
    {
        Coupon::updateOrCreate(['code' => 'HADIAH10'], [
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 100000,
            'max_uses' => 100,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);
    }

    private function seedDemoOrders(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $cashier = User::where('email', 'kasir@multiverse.test')->first();
        $products = Product::with('variants')->active()->get();

        if ($products->isEmpty()) {
            return;
        }

        $statuses = ['selesai', 'selesai', 'dikirim', 'diproses', 'paid', 'selesai', 'dikirim', 'selesai', 'paid', 'selesai', 'diproses', 'selesai'];

        foreach ($statuses as $index => $status) {
            $createdAt = now()->subDays(29 - ($index * 2))->setTime(rand(9, 20), rand(0, 59));
            $items = $products->random(rand(1, 3));
            $channel = $index % 3 === 0 ? 'pos' : 'online';
            $shipping = $channel === 'pos' ? 0 : 15000;

            $subtotal = 0;
            $lines = [];

            foreach ($items as $product) {
                $quantity = rand(1, 2);
                $price = (float) $product->price;
                $subtotal += $price * $quantity;
                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_id' => $product->variants->first()?->id,
                    'variant_name' => $product->variants->first()?->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'personalization_text' => $product->requires_personalization ? 'Untuk Sahabat Tercinta' : null,
                    'subtotal' => $price * $quantity,
                ];
            }

            $cashReceived = $channel === 'pos' ? $subtotal + $shipping + 5000 : null;

            $order = Order::create([
                'order_number' => 'MVA-'.$createdAt->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'user_id' => null,
                'address_id' => null,
                'customer_name' => $channel === 'pos' ? 'Pembeli Toko' : 'Sari Pembeli',
                'customer_phone' => $channel === 'pos' ? null : '081298765432',
                'customer_address' => $channel === 'pos'
                    ? 'Ambil di toko'
                    : 'Jl. Melati No. 12, Sukajadi, Bandung 40162',
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_cost' => $shipping,
                'total' => $subtotal + $shipping,
                'status' => $status,
                'channel' => $channel,
                'fulfillment' => $channel === 'pos' ? 'pickup' : 'delivery',
                'cash_received' => $cashReceived,
                'change_amount' => $cashReceived !== null ? $cashReceived - ($subtotal + $shipping) : null,
                'created_by' => $channel === 'pos' ? $cashier?->id : null,
                'paid_at' => $createdAt,
            ]);

            $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            foreach ($lines as $line) {
                $order->items()->create($line);
            }

            $order->payments()->create([
                'transaction_id' => 'DEMO-'.$order->order_number,
                'payment_type' => $channel === 'pos' ? 'cash' : collect(['qris', 'bank_transfer', 'ewallet'])->random(),
                'gross_amount' => $order->total,
                'status' => 'settlement',
                'paid_at' => $createdAt,
            ]);

            $order->statusLogs()->create([
                'user_id' => $channel === 'pos' ? $cashier?->id : null,
                'from_status' => 'pending',
                'to_status' => 'paid',
                'note' => 'Pembayaran diterima',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($status !== 'paid') {
                $order->statusLogs()->create([
                    'user_id' => null,
                    'from_status' => 'paid',
                    'to_status' => $status,
                    'note' => null,
                    'created_at' => $createdAt->copy()->addHour(),
                    'updated_at' => $createdAt->copy()->addHour(),
                ]);
            }
        }
    }
}
