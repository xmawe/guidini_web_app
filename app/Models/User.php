<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'phone_number',
        'role',
        'city_id',
        'location_id',
        'last_activity_at',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }

        return $this->name ?? '';
    }

    /**
     * Get the user's phone number (supports both field names)
     */
    public function getPhoneAttribute($value): ?string
    {
        return $value ?? $this->phone_number;
    }

    // Relationships

    /**
     * Get all bookings for this user
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the guide profile for this user (if they are a guide)
     */
    public function guide()
    {
        return $this->hasOne(Guide::class);
    }

    /**
     * Get the city this user belongs to
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the location this user belongs to
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Helper Methods

    /**
     * Check if user is a guide
     */
    public function isGuide(): bool
    {
        return $this->guide()->exists() || $this->role === 'guide';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check if user is active (based on last activity)
     */
    public function isActive(): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }

        return $this->last_activity_at->diffInDays(now()) <= 30;
    }
}
