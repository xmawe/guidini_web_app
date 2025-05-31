<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tourId' => $this->tour_id,
            'locationId' => $this->location_id,
            'activityCategoryId' => $this->activity_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => number_format($this->price, 2),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            // Relationships
            'location' => new LocationResource($this->whenLoaded('location')),
            'activityCategory' => new ActivityCategoryResource($this->whenLoaded('activityCategory')),
        ];
    }
}
