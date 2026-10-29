<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlugs;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    use GeneratesUniqueSlugs;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $required = $product instanceof Product ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price' => [$required, 'numeric', 'min:0', 'max:9999999999'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'sale_start_at' => ['nullable', 'date'],
            'sale_end_at' => ['nullable', 'date', 'after_or_equal:sale_start_at'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product instanceof Product ? $product->getKey() : null),
            ],
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keyword' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            return;
        }

        $product = $this->route('product');
        $name = $this->input('name') ?? ($product instanceof Product ? $product->name : null);

        if (is_string($name) && $name !== '') {
            $this->merge([
                'slug' => $this->uniqueSlugFor(
                    Product::class,
                    $name,
                    $product instanceof Product ? $product->getKey() : null,
                ),
            ]);
        }
    }
}
