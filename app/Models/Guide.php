<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'rating' => 'float',
    ];

    /**
     * Get the user that owns the guide.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Optional accessor for is_verified as boolean
     */
    protected function isVerified(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            set: fn ($value) => $value ? '1' : '0',
        );
    }
}
