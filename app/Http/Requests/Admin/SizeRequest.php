<?php

namespace App\Http\Requests\Admin;

use App\Models\Size;
use Illuminate\Foundation\Http\FormRequest;

class SizeRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->route('size') instanceof Size ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
        ];
    }
}
