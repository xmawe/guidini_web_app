<?php

// App/Models/Tour.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

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
        'is_food_included'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_transport_included' => 'boolean',
        'is_food_included' => 'boolean'
    ];

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function tourDates()
    {
        return $this->hasMany(TourDate::class);
    }

        public function tourImages()
    {
        return $this->hasMany(TourImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

        public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function activityCount()
    {
        return $this->activities()->count();
    }

    public function bookingCount()
    {
        return $this->bookings()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 2);
    }

    public function getFormattedDurationAttribute()
    {
        $hours = intval($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }
}
