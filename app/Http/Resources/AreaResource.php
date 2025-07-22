<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
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
            'city_id' => $this->city_id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => $this->coordinates,
            'has_coordinates' => $this->hasCoordinates(),
        ];
    }
}