<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit');
    }

    public function update(BusinessSettingsRequest $request): RedirectResponse
    {
        $values = [
            'business.name' => $request->string('business_name')->toString(),
            'business.whatsapp' => $request->string('business_whatsapp')->toString(),
            'business.address' => $request->string('business_address')->toString(),
            'cod_enabled' => $request->boolean('cod_enabled') ? 'true' : 'false',
        ];
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => $key === 'cod_enabled' ? 'boolean' : 'string', 'is_public' => true]);
        }

        return back()->with('success', 'Pengaturan usaha diperbarui.');
    }
}
