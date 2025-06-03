<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'user'      => new UserResource($this->user),
            'rating' => $this->rating,
            'tourId' => $this->tour_id,
            'comment' => $this->comment,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

        ];
    }
}
