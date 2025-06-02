<?php
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
        'is_food_included',
    ];

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Add this missing relationship
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(TourImage::class);
    }

    public function dates()
    {
        return $this->hasMany(TourDate::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class);
    }
}
