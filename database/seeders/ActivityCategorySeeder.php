<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActivityCategory;

class ActivityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Walking Tours'],
            ['name' => 'Hiking'],
            ['name' => 'Cycling'],
            ['name' => 'Cultural'],
            ['name' => 'Food Tours'],
            ['name' => 'Adventure Sports'],
            ['name' => 'Relaxation'],
        ];

        foreach ($categories as $category) {
            ActivityCategory::create($category);
        }
    }
}
