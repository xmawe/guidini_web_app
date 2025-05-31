<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guideId' => $this->guide_id,
            'locationId' => $this->location_id,
            'cityId' => $this->city_id,
            'bookingCount'  => $this->bookingCount(),
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'maxGroupSize' => $this->max_group_size,
            'availabilityStatus' => $this->availability_status,
            'isTransportIncluded' => $this->is_transport_included,
            'isFoodIncluded' => $this->is_food_included,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            'guide' => new GuideResource($this->whenLoaded('guide')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'city' => new CityResource($this->whenLoaded('city')),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'tourDates' => TourDateResource::collection($this->whenLoaded('tourDates')),
            'tourImages' => TourImageResource::collection($this->whenLoaded('tourImages')),
        ];
    }
}
