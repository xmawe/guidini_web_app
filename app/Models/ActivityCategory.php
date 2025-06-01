<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        // Add other fields like description if you have them in your database table
    ];

    // You might add relationships here later if needed, e.g., hasMany Activities
}
