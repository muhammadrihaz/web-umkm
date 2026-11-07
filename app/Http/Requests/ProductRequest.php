<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:40'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.uploaded' => 'Foto gagal diunggah. Pastikan ukuran file tidak melebihi 4 MB.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto tidak boleh melebihi 4 MB.',
        ];
    }
}
