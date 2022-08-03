<?php

namespace App\Http\User\Resources;

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
            'groups' => $this->groups?->pluck('name'),
            'roles' => $this->roles?->pluck('name'),
            'shops' => $this->shops?->pluck('name'),
            'country' => $this->country?->name,
        ];
    }
}
