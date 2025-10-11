<?php

namespace App\Services\Utilities;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Allowed image mime types
     */
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    /**
     * Allowed PDF mime types
     */
    private const ALLOWED_PDF_MIME_TYPES = [
        'application/pdf'
    ];

    /**
     * Allowed Excel mime types
     */
    private const ALLOWED_EXCEL_MIME_TYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
        'application/vnd.ms-excel.sheet.macroEnabled.12', // .xlsm
    ];

    /**
     * Maximum file size in bytes (10MB for images, 25MB for PDFs, 50MB for Excel)
     */
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    private const MAX_PDF_SIZE = 25 * 1024 * 1024;   // 25MB
    private const MAX_EXCEL_SIZE = 50 * 1024 * 1024; // 50MB

    /**
     * Store a single media file and create database record.
     */
    public function storeMedia(UploadedFile $file, Model $model, string $directory = 'media'): Media
    {
        try {
            $this->validateMedia($file);

            // Ensure directory is a folder named after the model if requested
            if ($directory === 'media' || $directory === 'images') {
                $modelFolder = Str::snake(class_basename($model));
                $directory = $directory . '/' . $modelFolder;
            }

            $filename = $this->generateUniqueFilename($file);
            $path = $file->storeAs($directory, $filename, 'public');

            // Create media record in database
            $media = $model->media()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->getMediaType($file),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);


            return $media;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Store multiple media files and create database records.
     */
    public function storeMultipleMedia(array $files, Model $model, string $directory = 'media'): array
    {
        $storedMedia = [];

        foreach ($files as $index => $file) {
            try {
                $media = $this->storeMedia($file, $model, $directory);
                $storedMedia[] = $media;
            } catch (\Exception $e) {
                // Clean up any previously stored media
                $this->cleanupStoredMedia($storedMedia);
                throw $e;
            }
        }

        return $storedMedia;
    }

    /**
     * Store image specifically.
     */
    public function storeImage(UploadedFile $image, Model $model, string $directory = 'images'): Media
    {
        if (!$this->isImage($image)) {
            throw new \Exception('File is not a valid image');
        }

        return $this->storeMedia($image, $model, $directory);
    }

    /**
     * Store PDF specifically.
     */
    public function storePdf(UploadedFile $pdf, Model $model, string $directory = 'pdfs'): Media
    {
        if (!$this->isPdf($pdf)) {
            throw new \Exception('File is not a valid PDF');
        }

        return $this->storeMedia($pdf, $model, $directory);
    }

    /**
     * Store Excel specifically.
     */
    public function storeExcel(UploadedFile $excel, Model $model, string $directory = 'excel'): Media
    {
        if (!$this->isExcel($excel)) {
            throw new \Exception('File is not a valid Excel file');
        }

        return $this->storeMedia($excel, $model, $directory);
    }

    /**
     * Delete media from storage and database.
     * Note: File deletion from storage is handled by MediaObserver
     */
    public function deleteMedia(Media $media): bool
    {
        try {
            // Delete from database - MediaObserver will handle file deletion
            $media->delete();


            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete multiple media records.
     */
    public function deleteMultipleMedia(array $mediaIds): array
    {
        $results = [];

        foreach ($mediaIds as $mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                $results[$mediaId] = $this->deleteMedia($media);
            } else {
                $results[$mediaId] = false;
            }
        }

        return $results;
    }

    /**
     * Delete all media for a specific model.
     * Note: File deletion from storage is handled by MediaObserver
     */
    public function deleteAllMediaForModel(Model $model): int
    {
        try {
            $mediaCollection = $model->media;
            $deletedCount = $mediaCollection->count();

            // Delete all media records - MediaObserver will handle file deletion
            $model->media()->delete();


            return $deletedCount;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update media record.
     */
    public function updateMedia(Media $media, array $data): Media
    {
        try {
            $media->update($data);


            return $media;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get media by ID with model relationship.
     */
    public function getMediaById(int $mediaId): ?Media
    {
        return Media::with('mediable')->find($mediaId);
    }

    /**
     * Get all media for a specific model.
     */
    public function getMediaForModel(Model $model, string $fileType = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $model->media();

        if ($fileType) {
            $query->where('file_type', $fileType);
        }

        return $query->get();
    }

    /**
     * Get media by file type for a specific model.
     */
    public function getImagesForModel(Model $model): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getMediaForModel($model, 'image');
    }

    /**
     * Get PDFs for a specific model.
     */
    public function getPdfsForModel(Model $model): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getMediaForModel($model, 'pdf');
    }

    /**
     * Move media to different directory and update database record.
     */
    public function moveMedia(Media $media, string $newDirectory): Media
    {
        try {
            $newPath = $this->moveMediaFile($media->file_path, $newDirectory);

            // Update database record
            $media->update(['file_path' => $newPath]);


            return $media;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Copy media to different directory and create new database record.
     */
    public function copyMedia(Media $media, Model $newModel, string $newDirectory): Media
    {
        try {
            $newPath = $this->copyMediaFile($media->file_path, $newDirectory);

            // Create new media record
            $newMedia = $newModel->media()->create([
                'file_name' => $media->file_name,
                'file_path' => $newPath,
                'file_type' => $media->file_type,
                'mime_type' => $media->mime_type,
                'file_size' => $media->file_size,
            ]);


            return $newMedia;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get media statistics for a specific model.
     */
    public function getMediaStatsForModel(Model $model): array
    {
        $mediaCollection = $model->media;

        $totalCount = $mediaCollection->count();
        $imageCount = $mediaCollection->where('file_type', 'image')->count();
        $pdfCount = $mediaCollection->where('file_type', 'pdf')->count();
        $totalSize = $mediaCollection->sum('file_size');

        return [
            'total_count' => $totalCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'images' => [
                'count' => $imageCount,
                'size' => $mediaCollection->where('file_type', 'image')->sum('file_size'),
                'size_mb' => round($mediaCollection->where('file_type', 'image')->sum('file_size') / 1024 / 1024, 2)
            ],
            'pdfs' => [
                'count' => $pdfCount,
                'size' => $mediaCollection->where('file_type', 'pdf')->sum('file_size'),
                'size_mb' => round($mediaCollection->where('file_type', 'pdf')->sum('file_size') / 1024 / 1024, 2)
            ]
        ];
    }

    /**
     * Get all media statistics across all models.
     */
    public function getAllMediaStats(): array
    {
        $media = Media::all();

        $totalCount = $media->count();
        $imageCount = $media->where('file_type', 'image')->count();
        $pdfCount = $media->where('file_type', 'pdf')->count();
        $totalSize = $media->sum('file_size');

        // Group by model type
        $modelStats = $media->groupBy('mediable_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'size' => $group->sum('file_size'),
                'size_mb' => round($group->sum('file_size') / 1024 / 1024, 2),
                'images' => $group->where('file_type', 'image')->count(),
                'pdfs' => $group->where('file_type', 'pdf')->count()
            ];
        });

        return [
            'total_count' => $totalCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'images' => [
                'count' => $imageCount,
                'size' => $media->where('file_type', 'image')->sum('file_size'),
                'size_mb' => round($media->where('file_type', 'image')->sum('file_size') / 1024 / 1024, 2)
            ],
            'pdfs' => [
                'count' => $pdfCount,
                'size' => $media->where('file_type', 'pdf')->sum('file_size'),
                'size_mb' => round($media->where('file_type', 'pdf')->sum('file_size') / 1024 / 1024, 2)
            ],
            'by_model_type' => $modelStats
        ];
    }

    /**
     * Delete media from storage.
     */
    public function deleteMediaFromStorage(string $mediaPath): bool
    {
        try {
            if (Storage::disk('public')->exists($mediaPath)) {
                Storage::disk('public')->delete($mediaPath);


                return true;
            }


            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete multiple media files from storage.
     */
    public function deleteMultipleMediaFromStorage(array $mediaPaths): array
    {
        $results = [];

        foreach ($mediaPaths as $path) {
            $results[$path] = $this->deleteMediaFromStorage($path);
        }

        return $results;
    }

    /**
     * Validate media file.
     */
    public function validateMedia(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception(__('validation.media.invalid_file'));
        }

        // Check file size based on type
        if ($this->isImage($file)) {
            if ($file->getSize() > self::MAX_IMAGE_SIZE) {
                throw new \Exception('Image file size exceeds maximum allowed size of ' . (self::MAX_IMAGE_SIZE / 1024 / 1024) . 'MB');
            }
        } elseif ($this->isPdf($file)) {
            if ($file->getSize() > self::MAX_PDF_SIZE) {
                throw new \Exception('PDF file size exceeds maximum allowed size of ' . (self::MAX_PDF_SIZE / 1024 / 1024) . 'MB');
            }
        } elseif ($this->isExcel($file)) {
            if ($file->getSize() > self::MAX_EXCEL_SIZE) {
                throw new \Exception('Excel file size exceeds maximum allowed size of ' . (self::MAX_EXCEL_SIZE / 1024 / 1024) . 'MB');
            }
        } else {
            throw new \Exception('Unsupported file type. Only images, PDFs, and Excel files are allowed.');
        }

        // Check mime type
        $mimeType = $file->getMimeType();
        $allowedTypes = array_merge(self::ALLOWED_IMAGE_MIME_TYPES, self::ALLOWED_PDF_MIME_TYPES, self::ALLOWED_EXCEL_MIME_TYPES);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception(__('validation.media.invalid_file_type'));
        }
    }

    /**
     * Check if file is an image.
     */
    public function isImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES);
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return in_array($mimeType, self::ALLOWED_PDF_MIME_TYPES);
    }

    /**
     * Check if file is Excel
     */
    public function isExcel(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return in_array($mimeType, self::ALLOWED_EXCEL_MIME_TYPES);
    }

    /**
     * Get media type (image, pdf, or excel).
     */
    public function getMediaType(UploadedFile $file): string
    {
        if ($this->isImage($file)) {
            return 'image';
        } elseif ($this->isPdf($file)) {
            return 'pdf';
        } elseif ($this->isExcel($file)) {
            return 'excel';
        }

        return 'unknown';
    }

    /**
     * Generate unique filename for media.
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;

        return $filename;
    }

    /**
     * Clean up stored media in case of failure.
     * Note: For Media instances, file deletion is handled by MediaObserver
     */
    private function cleanupStoredMedia(array $storedMedia): void
    {
        foreach ($storedMedia as $media) {
            if ($media instanceof Media) {
                // Delete from database - MediaObserver will handle file deletion
                $media->delete();
            } else {
                // For non-Media instances, manually delete from storage
                $this->deleteMediaFromStorage($media['path']);
            }
        }
    }

    /**
     * Get media URL from storage path.
     */
    public function getMediaUrl(string $mediaPath): string
    {
        if (Storage::disk('public')->exists($mediaPath)) {
            return asset('storage/' . $mediaPath);
        }

        return asset('images/placeholder.png'); // Fallback image
    }

    /**
     * Check if media exists in storage.
     */
    public function mediaExists(string $mediaPath): bool
    {
        return Storage::disk('public')->exists($mediaPath);
    }

    /**
     * Get media information.
     */
    public function getMediaInfo(string $mediaPath): array
    {
        if (!$this->mediaExists($mediaPath)) {
            throw new \Exception('Media not found');
        }

        $fullPath = Storage::disk('public')->path($mediaPath);
        $mimeType = mime_content_type($fullPath);
        $isImage = in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES);

        $mediaInfo = [
            'path' => $mediaPath,
            'url' => $this->getMediaUrl($mediaPath),
            'size' => Storage::disk('public')->size($mediaPath),
            'mime_type' => $mimeType,
            'type' => $isImage ? 'image' : 'pdf',
            'last_modified' => Storage::disk('public')->lastModified($mediaPath)
        ];

        // Add image-specific information if it's an image
        if ($isImage) {
            $imageInfo = getimagesize($fullPath);
            $mediaInfo['width'] = $imageInfo[0] ?? null;
            $mediaInfo['height'] = $imageInfo[1] ?? null;
        }

        return $mediaInfo;
    }

    /**
     * Move media to different directory.
     */
    private function moveMediaFile(string $oldPath, string $newDirectory): string
    {
        try {
            if (!$this->mediaExists($oldPath)) {
                throw new \Exception('Source media not found');
            }

            $filename = basename($oldPath);
            $newPath = $newDirectory . '/' . $filename;

            Storage::disk('public')->move($oldPath, $newPath);


            return $newPath;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Copy media to different directory.
     */
    private function copyMediaFile(string $sourcePath, string $targetDirectory): string
    {
        try {
            if (!$this->mediaExists($sourcePath)) {
                throw new \Exception('Source media not found');
            }

            $filename = basename($sourcePath);
            $targetPath = $targetDirectory . '/' . $filename;

            Storage::disk('public')->copy($sourcePath, $targetPath);


            return $targetPath;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get storage disk usage statistics.
     */
    public function getStorageStats(): array
    {
        $disk = Storage::disk('public');
        $files = $disk->allFiles();

        $totalSize = 0;
        $imageCount = 0;
        $pdfCount = 0;
        $imageSize = 0;
        $pdfSize = 0;

        foreach ($files as $file) {
            $fullPath = $disk->path($file);
            $mimeType = mime_content_type($fullPath);
            $fileSize = $disk->size($file);

            if (in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES)) {
                $imageCount++;
                $imageSize += $fileSize;
            } elseif (in_array($mimeType, self::ALLOWED_PDF_MIME_TYPES)) {
                $pdfCount++;
                $pdfSize += $fileSize;
            }

            $totalSize += $fileSize;
        }

        return [
            'total_files' => $imageCount + $pdfCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'images' => [
                'count' => $imageCount,
                'size' => $imageSize,
                'size_mb' => round($imageSize / 1024 / 1024, 2),
                'average_size' => $imageCount > 0 ? round($imageSize / $imageCount / 1024, 2) : 0
            ],
            'pdfs' => [
                'count' => $pdfCount,
                'size' => $pdfSize,
                'size_mb' => round($pdfSize / 1024 / 1024, 2),
                'average_size' => $pdfCount > 0 ? round($pdfSize / $pdfCount / 1024, 2) : 0
            ]
        ];
    }

    /**
     * Get allowed file extensions for frontend validation.
     */
    public function getAllowedExtensions(): array
    {
        return [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'pdfs' => ['pdf'],
            'all' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf']
        ];
    }

    /**
     * Get maximum file sizes for frontend validation.
     */
    public function getMaxFileSizes(): array
    {
        return [
            'images' => self::MAX_IMAGE_SIZE,
            'pdfs' => self::MAX_PDF_SIZE,
            'images_mb' => self::MAX_IMAGE_SIZE / 1024 / 1024,
            'pdfs_mb' => self::MAX_PDF_SIZE / 1024 / 1024
        ];
    }
}
