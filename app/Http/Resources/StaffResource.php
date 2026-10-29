<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'role_id' => $this->role_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'store' => new StoreResource($this->whenLoaded('store')),
            'role' => new RoleResource($this->whenLoaded('role')),
        ];
    }
}
