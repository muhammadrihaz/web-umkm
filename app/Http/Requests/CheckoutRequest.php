<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:24'],
            'fulfillment_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'delivery_zone_id' => ['nullable', 'required_if:fulfillment_method,delivery', 'exists:delivery_zones,id'],
            'delivery_address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['midtrans', 'cod'])],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
