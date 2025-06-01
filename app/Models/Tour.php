<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
