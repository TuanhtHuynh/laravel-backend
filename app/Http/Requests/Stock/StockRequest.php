<?php

namespace App\Http\Requests\Stock;

use App\Models\ProductStock;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('stock') instanceof ProductStock;

        return [
            'product_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
            'store_id' => ['nullable', 'integer', Rule::exists('stores', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
        ];
    }

    /**
     * Nhân viên kho chỉ được ghi tồn cho cửa hàng của mình; quản trị mới được chỉ định store_id.
     */
    public function isAdmin(): bool
    {
        return $this->user()?->staff?->role?->slug === Role::ADMIN;
    }
}
