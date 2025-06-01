<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'location' => [
                'id' => $this->location?->id,
                'label' => $this->location?->label,
                'latitude' => $this->location?->latitude,
                'longitude' => $this->location?->longitude,
            ],
            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
            ],
            'guide' => [
                'id' => $this->guide?->id,
                'name' => $this->guide?->user?->first_name . ' ' . $this->guide?->user?->last_name,
                'rating' => $this->guide?->rating,
            ],
            'is_transport_included' => $this->is_transport_included,
            'is_food_included' => $this->is_food_included,
            'activities_count' => $this->activities()->count(),
            // Add more fields as needed
        ];
    }
}