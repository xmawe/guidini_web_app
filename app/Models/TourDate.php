<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDate extends Model
{
    protected $fillable = [
        'tour_id',
        'available_date',
        'available_slots'
    ];

    protected $casts = [
        'available_date' => 'datetime',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
