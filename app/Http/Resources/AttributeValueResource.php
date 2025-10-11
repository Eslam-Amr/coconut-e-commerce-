<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
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
            'attribute_id' => $this->attribute_id,
            'value' => $this->value, // This will get the translated value based on current locale
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attribute' => new AttributeResource($this->whenLoaded('attribute')),
        ];
    }
}
