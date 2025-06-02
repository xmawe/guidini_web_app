<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'is_verified',
        'rating',
        'languages'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'rating' => 'float', // Changed from 'decimal:2,1' to 'float'
        'languages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }
}
