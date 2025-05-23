<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'icon' => $this->icon,
        ];
    }
}
