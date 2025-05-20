<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Casablanca',
            'Rabat',
            'Fès',
            'Marrakech',
            'Tanger',
            'Agadir',
            'Meknès',
            'Oujda',
            'Kenitra',
            'Tétouan',
            'Safi',
            'El Jadida',
            'Beni Mellal',
            'Nador',
            'Khouribga',
            'Taza',
            'Mohammedia',
            'Errachidia',
            'Ksar El Kebir',
            'Larache',
        ];

        foreach ($cities as $city) {
            City::create(['name' => $city]);
        }
    }
}
