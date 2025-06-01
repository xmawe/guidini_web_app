<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guide extends Model
{
    protected $fillable = [
        'user_id',
        'languages',
        'is_verified',
        'rating',
        'biography',
    ];

    protected $casts = [
        'languages' => 'array',
        'is_verified' => 'boolean',
        'rating' => 'float',
    ];

    /**
     * Get the user that owns the guide profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guide's experience in years based on guide creation date.
     */
    public function getExperienceYearsAttribute()
    {
        return now()->diffInYears($this->created_at);
    }
}
