<?php

namespace App\Traits;

use App\Models\Media;

trait MediaTrait
{


    
    /**
     * Get all media files for this user
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
    
    /**
     * Automatically delete related media when the parent model is deleted.
     */
    public static function bootMediaTrait()
    {
        static::deleting(function ($model) {
            // Delete related media records; MediaObserver handles storage file deletion
            if (method_exists($model, 'media')) {
                $model->media()->each(function ($media) {
                    $media->delete();
                });
            }
        });
    }
    
    // public function getFullUrl(){
    //     if (!$this->full_url) {
    //         return null;
    //     }
        
    //     $baseUrl = config('app.base_image_url', env('BASE_IMAGE_URL', ''));
    //     return rtrim($baseUrl, '/') . '/' . ltrim($this->full_url, '/');
    // }
//     public function getFullUrlAttribute()
// {
//     $baseUrl = config('app.base_image_url', env('BASE_IMAGE_URL', ''));
//     return $this->full_url 
//         ? rtrim($baseUrl, '/') . '/' . ltrim($this->full_url, '/') 
//         : null;
// }
// public function getFullUrl($path = null)
// {
//     $filePath = $path ?: $this->full_url;

//     if (!$filePath) {
//         return null;
//     }
    
//     $baseUrl = config('app.base_image_url', env('BASE_IMAGE_URL', ''));
//     return rtrim($baseUrl, '/') . '/' . ltrim($filePath, '/');
// }


}