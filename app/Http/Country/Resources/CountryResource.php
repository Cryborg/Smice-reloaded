<?php

namespace App\Http\Country\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'type' => 'country',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
            ],
            'relationships' => [
                'users' => $this->whenLoaded($this->users),
            ]
        ];
    }
}
