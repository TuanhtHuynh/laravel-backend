<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:255'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
