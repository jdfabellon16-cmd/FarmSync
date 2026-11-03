<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
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
            'name' => $this->name,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_area_hectares' => $this->total_area_hectares,
            'can_update' => $request->user()?->can('update', $this->resource) ?? false,
            'can_manage_fields' => $request->user()?->can('manageFields', $this->resource) ?? false,
            'fields_count' => $this->whenCounted('fields'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
