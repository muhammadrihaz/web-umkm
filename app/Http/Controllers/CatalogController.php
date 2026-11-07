<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'products' => Product::query()->with('category', 'images')->where('is_active', true)->latest()->limit(8)->get(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $products = Product::query()->with('category', 'images')->where('is_active', true)
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('name', 'like', '%'.$request->string('q').'%')->orWhere('sku', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category'))))
            ->latest()->paginate(12)->withQueryString();

        return view('catalog.index', ['products' => $products, 'categories' => Category::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $relatedProducts = Product::query()
            ->with('category', 'images')
            ->where('is_active', true)
            ->whereKeyNot($product->getKey())
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->latest()
            ->limit(4)
            ->get();

        return view('catalog.show', [
            'product' => $product->load('category', 'images'),
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function contact(): View
    {
        return view('contact');
    }
}
