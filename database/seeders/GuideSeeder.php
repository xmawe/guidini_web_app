<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to create guides for
        $users = User::take(5)->get();

        foreach ($users as $user) {
            // Create a guide with a different creation date than the user
            // This will simulate the guide having joined the platform at one time
            // but becoming a guide at a later date

            // User creation date (when they joined the platform)
            $userCreatedAt = $user->created_at;

            // Guide creation date (when they became a guide - sometime after joining)
            // Between 6 months and 2 years after joining
            $guideCreatedAt = Carbon::parse($userCreatedAt)
                ->addMonths(rand(6, 24));

            // If guide creation date would be in the future, set it to now
            if ($guideCreatedAt->isFuture()) {
                $guideCreatedAt = Carbon::now();
            }

            // Create the guide record with a custom timestamp
            DB::table('guides')->insert([
                'user_id' => $user->id,
                'languages' => json_encode(['English', 'Arabic', 'French']),
                'is_verified' => rand(0, 1),
                'rating' => rand(35, 50) / 10, // Random rating between 3.5 and 5.0
                'biography' => 'I am a professional guide with extensive experience in showing travelers the beauty and culture of Morocco. I have been guiding tours for over ' . rand(1, 10) . ' years and I am passionate about sharing my knowledge and love for this country with visitors from around the world.',
                'created_at' => $guideCreatedAt,
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
