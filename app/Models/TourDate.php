<?php

// App/Models/TourDate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'day_of_week',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time->format('H:i');
    }

    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time->format('H:i');
    }

    public function getTimeRangeAttribute()
    {
        return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
    }
}
