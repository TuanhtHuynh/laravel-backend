<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    public function rules(): array
    {
        $page = $this->route('page');
        $required = $page !== null ? 'sometimes' : 'required';

        return [
            'title' => [$required, 'string', 'max:255'],
            'slug' => [$required, 'string', 'max:255', 'unique:pages,slug' . ($page ? ',' . $page->id : '')],
            'content' => [$required, 'string'],
        ];
    }
}
