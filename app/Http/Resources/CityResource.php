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
    public static $wrap = null;
    public function toArray(Request $request): array
    {


        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'userCount'   => $this->users_count ?? 0,  // Use users_count from withCount
            'tourCount'   => $this->tours_count ?? 0,  // Use tours_count from withCount
            'createdAt'   => $this->created_at,
            'updatedAt'   => $this->updated_at,
        ];
    }
}
