<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'location_id',
        'activity_category_id',
        'title',
        'description',
        'duration',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    // Relationships
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function activityCategory()
    {
        return $this->belongsTo(ActivityCategory::class);
    }
}
