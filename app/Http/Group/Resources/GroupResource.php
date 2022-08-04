<?php

namespace App\Http\Group\Resources;

use App\Http\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'name' => $this->name,
            'society_id' => $this->society_id,
            'created_by' => new UserResource(
                $this->whenLoaded('createdBy')
            ),
        ];
    }
}
