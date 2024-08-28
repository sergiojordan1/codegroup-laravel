<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use Faker\Factory as Faker;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Insert 4 goalkeepers
        for ($i = 0; $i < 4; $i++) {
            Player::create([
                'name' => $faker->name,
                'level' => $faker->numberBetween(1, 5),
                'is_goalkeeper' => true,
            ]);
        }

        // Insert 16 aleatory players
        for ($i = 0; $i < 16; $i++) {
            Player::create([
                'name' => $faker->name,
                'level' => $faker->numberBetween(1, 5),
                'is_goalkeeper' => false,
            ]);
        }
    }
}
