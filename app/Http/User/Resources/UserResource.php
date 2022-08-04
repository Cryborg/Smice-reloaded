<?php

namespace App\Http\User\Resources;

use App\Http\Group\Resources\GroupResourceCollection;
use App\Http\Role\Resources\RoleResourceCollection;
use App\Http\Shop\Resources\ShopResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'city' => $this->city,
            'email' => $this->email,
            'last_mission' => $this->last_mission,
            'name' => $this->name,
            'phone' => $this->phone,
            'postal_code' => $this->postal_code,
            'registration' => $this->registration,
            'society' => $this->society?->name,
            'status_id' => $this->status_id,
            'status_name' => $this->status_name,
            'validated_mission' => $this->validated_mission,
            'groups' => new GroupResourceCollection(
                $this->whenLoaded('groups')
            ),
            'roles' => new RoleResourceCollection(
                $this->whenLoaded('roles')
            ),
            'shops' => new ShopResourceCollection(
                $this->whenLoaded('shops')
            ),
            'country' => $this->country?->name,
        ];
    }
}
