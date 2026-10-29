<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $banner = $this->route('banner');
        $required = $banner !== null ? 'sometimes' : 'required';

        return [
            'image' => [$required, 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
