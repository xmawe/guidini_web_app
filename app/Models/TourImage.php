<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourImage extends Model
{
    use HasFactory;

    protected $table = 'tour_images';

    protected $fillable = [
        'tour_id',
        'image_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tour that owns the image
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getFullImageUrlAttribute()
    {
        // If the image_url is already a full URL (starts with http), return as is
        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }

        // Otherwise, prepend your base URL
        return config('app.url') . '/storage/' . $this->image_url;
    }
}
