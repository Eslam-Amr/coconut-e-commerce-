<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'mediable_id',
        'mediable_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the parent mediable model (User, Doctor, Slider, etc.)
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Get the full file URL
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            // return asset('storage/' . $this->file_path);
            return env('BASE_IMAGE_URL',"127.0.0.1:8000").'/' . $this->file_path;
        }
        return null;
    }

    /**
     * Check if the file is an image
     */
    public function getIsImageAttribute()
    {
        return $this->file_type === 'image' || 
               str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if the file is a video
     */
    public function getIsVideoAttribute()
    {
        return $this->file_type === 'video' || 
               str_starts_with($this->mime_type ?? '', 'video/');
    }

    /**
     * Check if the file is a PDF
     */
    public function getIsPdfAttribute()
    {
        return $this->file_type === 'pdf' || 
               $this->mime_type === 'application/pdf';
    }
}
