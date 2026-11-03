<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\Field;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoFarmSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@farmsync.test')->firstOrFail();
        $farm = Farm::firstOrCreate(
            ['owner_id' => $owner->id, 'name' => 'Green Valley Farm'],
            [
                'location' => 'Fresno County, California',
                'latitude' => 36.7378,
                'longitude' => -119.7871,
                'total_area_hectares' => 48.5,
            ],
        );

        Field::firstOrCreate(
            ['farm_id' => $farm->id, 'name' => 'North Field'],
            [
                'area_hectares' => 12.5,
                'soil_type' => 'Sandy loam',
                'irrigation_type' => 'Drip',
                'status' => 'Growing',
                'latitude' => 36.7421,
                'longitude' => -119.7913,
                'boundary' => [
                    ['lat' => 36.744, 'lng' => -119.794],
                    ['lat' => 36.744, 'lng' => -119.789],
                    ['lat' => 36.740, 'lng' => -119.789],
                    ['lat' => 36.740, 'lng' => -119.794],
                ],
            ],
        );

        Field::firstOrCreate(
            ['farm_id' => $farm->id, 'name' => 'South Field'],
            [
                'area_hectares' => 8.0,
                'soil_type' => 'Loam',
                'irrigation_type' => 'Sprinkler',
                'status' => 'Prepared',
                'latitude' => 36.7335,
                'longitude' => -119.7838,
            ],
        );
    }
}
