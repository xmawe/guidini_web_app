<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $adminRole = Role::create(['name' => 'admin']);
        $guideRole = Role::create(['name' => 'guide']);
        $touristRole = Role::create(['name' => 'tourist']);

        // Users
        $admin = User::create([
            'first_name' => 'Jada',
            'last_name' => 'Admin',
            'email' => 'jada@admin.com',
            'password' => Hash::make('password'),
            'phone_number' => '0600000000',
            'city_id' => 1,
        ]);
        $admin->assignRole($adminRole);

        $guide = User::create([
            'first_name' => 'Jada',
            'last_name' => 'Guide',
            'email' => 'jada@guide.com',
            'password' => Hash::make('password'),
            'phone_number' => '0600000001',
            'city_id' => 2,
        ]);
        $guide->assignRole($guideRole);

        $tourist = User::create([
            'first_name' => 'Jada',
            'last_name' => 'Tourist',
            'email' => 'jada@tourist.com',
            'password' => Hash::make('password'),
            'phone_number' => '0600000002',
            'city_id' => 3,
        ]);
        $tourist->assignRole($touristRole);

        // Create Guide Profile
        Guide::create([
            'user_id' => $guide->id,
            'biography' => 'Experienced local guide passionate about sharing hidden gems.',
            'languages' => ['English', 'French'],
            'rating' => 4.8,
            'is_verified' => true,
        ]);
    }
}
