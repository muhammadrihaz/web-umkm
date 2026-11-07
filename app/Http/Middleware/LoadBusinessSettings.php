<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LoadBusinessSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::query()->whereIn('key', ['business.name', 'business.whatsapp', 'business.address', 'cod_enabled'])->pluck('value', 'key');
                config([
                    'pangkonan.business.name' => $settings->get('business.name', config('pangkonan.business.name')),
                    'pangkonan.business.whatsapp' => $settings->get('business.whatsapp', config('pangkonan.business.whatsapp')),
                    'pangkonan.business.address' => $settings->get('business.address', config('pangkonan.business.address')),
                    'pangkonan.cod_enabled' => $settings->has('cod_enabled') ? filter_var($settings->get('cod_enabled'), FILTER_VALIDATE_BOOL) : config('pangkonan.cod_enabled'),
                ]);
            }
        } catch (Throwable) {
            // Halaman berikutnya tetap menentukan respons bila database sedang tidak tersedia.
        }

        return $next($request);
    }
}
