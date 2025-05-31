<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $fillable = [
        'guide_id',
        'location_id',
        'city_id',
        'title',
        'description',
        'price',
        'duration',
        'max_group_size',
        'availability_status',
        'is_transport_included',
        'is_food_included',
        // 'is_accommodation_included',
    ];

    /**
     * Get the guide for the tour.
     */
    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }

    /**
     * Get the location for the tour.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingCount()
    {
        return $this->bookings()->count();
    }

    public function tourImages(): HasMany
    {
        return $this->hasMany(TourImage::class);
    }

    public function tourDates(): HasMany
    {
        return $this->hasMany(TourDate::class);
    }

    /**
     * Get the city for the tour.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
