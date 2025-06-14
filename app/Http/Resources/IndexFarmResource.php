<?php 

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class IndexFarmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get authenticated user using Sanctum guard
        $user = Auth::guard('sanctum')->user();
        
        return [
            'id' => $this->id,
            // 'user_id' => $this->user_id,
            // 'city_id' => $this->city_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'guests_count' => $this->passengers_count,
            // 'description_ar' => $this->description_ar,
            // 'description_en' => $this->description_en,

            // Original prices
            'minimum_price' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price;
            }),
            
            // Prices after offer discount
            'minimum_price_after_offer' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price_after_offer;
            }),
            
            // Offer information
            'has_valid_offer' => $this->whenLoaded('offers', function () {
                return $this->hasValidOffer();
            }),

            // Favorite status (only when user is authenticated)
            'is_favorite' => $this->when($user !== null, function () use ($user) {
                return $this->isFavoriteByUser($user->id);
            }),

            'average_rating' => $this->whenLoaded('ratings', function () {
                return $this->display_average_rating; // null if less than 3 ratings
            }),

            'current_offer_percentage' => $this->whenLoaded('offers', function () {
                return $this->getCurrentOfferPercentage();
            }),
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city->id,
                    'name_ar' => $this->city->name_ar ?? '',
                    'name_en' => $this->city->name_en ?? '',
                ];
            }),
            'farm owner' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
                ];
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'is_main' => (bool) $image->is_main,
                    ];
                });
            }),
        ];
    }
}