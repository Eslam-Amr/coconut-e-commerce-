<?php

namespace App\Observers;

use App\Models\Media;
use App\Services\Utilities\MediaService;
use Illuminate\Support\Facades\Log;

class MediaObserver
{

    public function __construct(private MediaService $mediaService) {}
    public function deleted(Media $media)
    {
        try {
            if ($media->file_path)
                $this->mediaService->deleteMediaFromStorage($media->file_path);
        } catch (\Exception $e) {
            Log::error('Error occurred while deleting media file', [
                'media_id' => $media->id,
                'file_path' => $media->file_path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
