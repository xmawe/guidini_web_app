<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\CityResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'city' => new CityResource($this->city),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            // 'notifications' => NotificationResource::collection($this->whenLoaded('notifications')),
            'emailVerifiedAt' => $this->email_verified_at,
            'lastActivityAt' => $this->last_activity_at,
            'isActive' => $this->resource->isActive(),
            'isGuide' => $this->resource->isGuide(),
            'activityTimeAgo' => $this->resource->getLastActivityAgo(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

        ];
    }
}
