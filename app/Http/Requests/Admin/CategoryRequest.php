<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Concerns\GeneratesUniqueSlugs;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    use GeneratesUniqueSlugs;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $required = $category instanceof Category ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category instanceof Category ? $category->getKey() : null),
            ],
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

        $category = $this->route('category');
        $name = $this->input('name') ?? ($category instanceof Category ? $category->name : null);

        if (is_string($name) && $name !== '') {
            $this->merge([
                'slug' => $this->uniqueSlugFor(
                    Category::class,
                    $name,
                    $category instanceof Category ? $category->getKey() : null,
                ),
            ]);
        }
    }
}
