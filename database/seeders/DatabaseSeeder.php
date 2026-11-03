<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $owner = User::firstOrCreate(
            ['email' => 'owner@farmsync.test'],
            [
                'name' => 'FarmSync Demo Owner',
                'password' => 'password',
            ],
        );
        $owner->assignRole('Farm Owner');

        $this->call(DemoFarmSeeder::class);
    }
}
