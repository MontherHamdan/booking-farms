<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->when($this->relationLoaded('city'), function () {
                if (!$this->city) {
                    return null;
                }
                
                return [
                    'id' => $this->city->id,
                    'name_ar' => $this->city->name_ar,
                    'name_en' => $this->city->name_en,
                ];
            }),
            'avatar' => $this->avatar,
            'role' => $this->getPrimaryRole(),
            'is_farm_owner' => $this->isFarmOwner(), 
            'verifications' => $this->getFarmOwnerVerifications(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}