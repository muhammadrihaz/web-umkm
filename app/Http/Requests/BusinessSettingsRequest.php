<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'business_whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'business_address' => ['required', 'string', 'max:2000'],
            'cod_enabled' => ['required', 'boolean'],
        ];
    }
}
