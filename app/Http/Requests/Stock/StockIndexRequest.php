<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockIndexRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer'],
            'store_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => trim($this->input('q'))]);
        }
    }
}
