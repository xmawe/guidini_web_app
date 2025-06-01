<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tour;
use App\Models\Guide;
use App\Models\Location;
use App\Models\City;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create related models if not exist
        $city = City::firstOrCreate(['name' => 'Marrakesh']);
        $location = Location::firstOrCreate([
            'label' => 'Jemaa el-Fnaa',
            'latitude' => 31.6258,
            'longitude' => -7.9892,
        ]);
        $guide = Guide::firstOrCreate(['user_id' => 8]); // Make sure user_id=1 exists

        // Create tours
        Tour::create([
            'guide_id' => $guide->id,
            'location_id' => $location->id,
            'city_id' => $city->id,
            'title' => 'Marrakech Medina & Souks Walking Tour',
            'description' => 'Explore the vibrant souks and historic medina of Marrakech.',
            'price' => 25,
            'duration' => 3,
            'max_group_size' => 10,
            'availability_status' => 'available',
            'is_transport_included' => true,
            'is_food_included' => false,
        ]);
    }
}
