<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryZoneRequest;
use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        return view('admin.delivery-zones.index', ['zones' => DeliveryZone::query()->orderBy('name')->get()]);
    }

    public function store(DeliveryZoneRequest $request): RedirectResponse
    {
        DeliveryZone::query()->create($request->safe()->except('is_active') + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Zona delivery dibuat.');
    }

    public function update(DeliveryZoneRequest $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update($request->safe()->except('is_active') + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Zona delivery diperbarui.');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update(['is_active' => false]);

        return back()->with('success', 'Zona dinonaktifkan agar histori tetap utuh.');
    }
}
