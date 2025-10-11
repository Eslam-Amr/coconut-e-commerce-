<?php

namespace App\Http\Resources\Api\General\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'notification_status' => $this->notification_status,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'file_path' => $media->file_path,
                        'file_url' => $media->file_url,
                        'file_type' => $media->file_type,
                        'file_size' => $media->file_size,
                        'created_at' => $media->created_at,
                    ];
                });
            }),
            'profile_image' => $this->whenLoaded('media', function () {
                $profileImage = $this->media->where('file_type', 'image')->first();
                return $profileImage ? [
                    'id' => $profileImage->id,
                    'file_name' => $profileImage->file_name,
                    'file_path' => $profileImage->file_path,
                    'file_url' => $profileImage->file_url,
                    'file_type' => $profileImage->file_type,
                    'file_size' => $profileImage->file_size,
                ] : null;
            }),
        ];
    }
}
