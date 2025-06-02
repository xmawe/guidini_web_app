<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'state',
    ];

    /**
     * Get all locations for this city
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get all users from this city
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all tours in this city
     */
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }
}
