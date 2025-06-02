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
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer'
    ];

    /**
     * Relation avec le tour
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Relation avec la location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Relation avec la catégorie d'activité
     */
    public function activityCategory()
    {
        return $this->belongsTo(ActivityCategory::class);
    }

    /**
     * Relation avec les bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
