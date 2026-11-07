<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['stock_on_hand' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'min:5', 'max:255']];
    }
}
