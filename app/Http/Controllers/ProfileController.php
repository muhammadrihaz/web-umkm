<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()->load('addresses')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:24']]);
        $request->user()->update($data);

        return back()->with('success', 'Profil diperbarui.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:80'], 'recipient_name' => ['required', 'string'], 'phone' => ['required', 'string', 'max:24'], 'address_line' => ['required', 'string', 'max:1000'], 'village' => ['nullable', 'string'], 'district' => ['nullable', 'string'], 'regency' => ['nullable', 'string'], 'postal_code' => ['nullable', 'string', 'max:10'], 'notes' => ['nullable', 'string', 'max:500'], 'is_default' => ['nullable', 'boolean']]);
        $request->user()->addresses()->create($data);

        return back()->with('success', 'Alamat ditambahkan.');
    }
}
