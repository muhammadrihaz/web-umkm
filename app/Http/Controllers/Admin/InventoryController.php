<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('admin.inventory.index', ['products' => Product::query()->orderBy('name')->paginate(30), 'movements' => StockMovement::query()->latest()->limit(30)->get()]);
    }

    public function update(StockAdjustmentRequest $request, Product $product, InventoryService $service): RedirectResponse
    {
        $service->adjust($product, $request->integer('stock_on_hand'), $request->user(), $request->string('reason')->toString());

        return back()->with('success', 'Stok diperbarui dan dicatat pada ledger.');
    }
}
