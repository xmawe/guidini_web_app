<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use  HasRoles, HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'city_id',
        'last_activity_at',
        'profile_picture',
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

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the time ago in French for the last activity.
     *
     * @return string
     */
    public function getLastActivityAgo(): string
    {
        if (!$this->last_activity_at) {
            return 'Aucune activité récente';
        }

        $lastActivity = Carbon::parse($this->last_activity_at);

        return $lastActivity->diffForHumans([
            'locale' => 'fr', // Set the locale to French
            'short' => true,  // Use short format
        ]);
    }

    public function isActive(): bool
    {
        $lastActivity = $this->last_activity_at ? Carbon::parse($this->last_activity_at) : null;
        return $lastActivity && $lastActivity->greaterThanOrEqualTo(now()->subMinutes(1));
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin']);
    }

    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_user')
            ->withTimestamps();
    }

    public function isGuide(): bool
    {
        return $this->hasAnyRole(['guide']);
    }

}
