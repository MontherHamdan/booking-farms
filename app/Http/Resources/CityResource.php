<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'name_en'             => $this->name_en,
            'name_ar'             => $this->name_ar,
            'description_en'      => $this->description_en,
            'description_ar'      => $this->description_ar,
            'image_url'           => $this->image,
            'farms_count'         => $this->active_farms_count ?? 0, 
            'areas_count'         => $this->published_areas_count ?? 0, // Use published areas count
        ];
    }
}