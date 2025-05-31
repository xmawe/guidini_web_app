<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use App\Models\TourCategory;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Cultural & Historical',
            'Nature & Outdoor',
            'Food & Culinary',
            'Arts & Handicrafts',
            'Spiritual & Wellness',
            'Adventure & Sports',
            'Sightseeing & Scenic Tours',
            'Family & Kids',
            'Educational & Workshops',
            'Shopping & Markets',
            'Photography & Creative',
            'Nightlife & Entertainment',
            'Off the Beaten Path',
            'Eco & Sustainable Tourism',
            'Water Activities',
            'Winter Sports & Snow Activities',
            'Cruises & Boat Tours',
            'Festivals & Local Events',
            'Language & Cultural Immersion',
            'Volunteer & Community-Based',
            'Wildlife & Safari',
            'Urban Exploration',
            'Religious & Pilgrimage Tours',
            'Local Life & Home Visits',
            'Farm & Agricultural Experiences',
            'Transit & Layover Tours',
            'Accessible Travel',
            'Drone & Aerial Photography Tours',
            'Romance & Honeymoon Experiences',
            'Fitness & Wellness Retreats',
            'Military & War History Tours',
            'Archaeology & Ruins Exploration',
            'Extreme Sports',
            'Geology & Natural Wonders',
            'Technology & Innovation Tours',
            'Luxury & VIP Experiences',
            'Movie & TV Location Tours',
            'Biking & E-Bike Tours',
            'Horseback Riding Adventures',
        ];

        foreach ($categories as $category) {
            ActivityCategory::create([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
