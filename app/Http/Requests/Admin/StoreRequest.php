<?php

namespace App\Http\Requests\Admin;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->route('store') instanceof Store ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string', 'max:255'],
        ];
    }
}
