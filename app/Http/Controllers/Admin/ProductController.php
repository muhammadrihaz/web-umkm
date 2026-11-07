<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', ['products' => Product::withTrashed()->with('category')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['is_active'] = $request->boolean('is_active');
        $product = Product::query()->create($data);
        if ($request->hasFile('image')) {
            $product->images()->create(['path' => $request->file('image')->store('products', 'public'), 'alt_text' => $product->name]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk dibuat.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['is_active'] = $request->boolean('is_active');
        $product->update($data);
        if ($request->hasFile('image')) {
            $product->images()->create(['path' => $request->file('image')->store('products', 'public'), 'alt_text' => $product->name, 'position' => $product->images()->max('position') + 1]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);
        $product->delete();

        return back()->with('success', 'Produk diarsipkan; histori transaksi tetap tersimpan.');
    }
}
