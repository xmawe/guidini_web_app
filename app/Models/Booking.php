<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'tour_id',
        'tour_date_id',
        'booked_date',
        'group_size',
        'total_price',
        'status',
        // 'specialRequests',
    ];

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tour of the booking.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the tour date of the booking.
     */
    public function tourDate(): BelongsTo
    {
        return $this->belongsTo(TourDate::class);
    }
}
