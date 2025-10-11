<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
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
            'name' => $this->name, // This will get the translated name based on current locale
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
