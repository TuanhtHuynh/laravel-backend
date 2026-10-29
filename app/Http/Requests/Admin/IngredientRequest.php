<?php

namespace App\Http\Requests\Admin;

use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->route('ingredient') instanceof Ingredient ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
        ];
    }
}
