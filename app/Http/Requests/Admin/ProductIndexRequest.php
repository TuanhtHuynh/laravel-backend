<?php

namespace App\Http\Requests\Admin;

class ProductIndexRequest extends IndexRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);
    }
}
