<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'bookingReference' => 'BK-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'bookedDate' => $this->booked_date,
            'groupSize' => $this->group_size,
            'totalPrice' => $this->total_price,
            'status' => $this->status,
            'tour'=> new TourResource($this->whenLoaded('tour')),
            'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
