<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->withCount('variants');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', ['product' => new Product(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = Product::create($data);

        $this->saveImages($request, $product);
        $this->saveVariants($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants']);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $product->update($data);

        $this->saveImages($request, $product);
        $this->saveVariants($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($product->images()->where('is_primary', true)->doesntExist()) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Foto dihapus.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Foto utama diperbarui.');
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);
        $variant->delete();

        return back()->with('success', 'Varian dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'requires_personalization' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $data['requires_personalization'] = $request->boolean('requires_personalization');
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        return $data;
    }

    private function saveImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sort = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('products', 'public');
            $sort++;

            $product->images()->create([
                'path' => $path,
                'is_primary' => ! $hasPrimary,
                'sort_order' => $sort,
            ]);

            $hasPrimary = true;
        }
    }

    private function saveVariants(Request $request, Product $product): void
    {
        $names = $request->input('variant_name', []);
        $adjustments = $request->input('variant_price', []);
        $stocks = $request->input('variant_stock', []);

        foreach ($names as $index => $name) {
            $name = trim((string) $name);

            if ($name === '') {
                continue;
            }

            $variantId = $request->input('variant_id', [])[$index] ?? null;

            $payload = [
                'name' => $name,
                'price_adjustment' => (float) ($adjustments[$index] ?? 0),
                'stock' => (int) ($stocks[$index] ?? 0),
            ];

            if ($variantId) {
                $product->variants()->whereKey($variantId)->update($payload);
            } else {
                $product->variants()->create($payload);
            }
        }
    }
}
