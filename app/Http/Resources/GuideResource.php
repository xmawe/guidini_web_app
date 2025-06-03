<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideResource extends JsonResource
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
            'userId' => $this->user_id,
            'languages' => $this->languages, // returns JSON array
            'isVerified' => filter_var($this->is_verified, FILTER_VALIDATE_BOOLEAN),
            'rating' => number_format($this->rating, 2),
            'biography' => $this->biography,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            // Optionally include related user
            'user' => new UserResource($this->whenLoaded('user')),
            'tours' => TourResource::collection($this->whenLoaded('tours')),
        ];
    }
}
