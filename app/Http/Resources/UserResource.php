<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\MarketResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'is_admin' => $this->isAdmin(),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'markets' => MarketResource::collection($this->whenLoaded('markets')),

        ];
    }
}
