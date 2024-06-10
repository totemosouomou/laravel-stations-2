<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use Faker\Factory as Faker;

class MovieTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('movies')->delete();
        $id = 1;

        $faker = Faker::create();
        $movies = [];

        for ($i = 1; $i <= 10; $i++) {
            $movies[] = [
                'id' => $id++,
                'title' => 'Movie ' . $i,
                'image_url' => $faker->imageUrl(),
                'published_year' => $faker->numberBetween(2000, 2024),
                'is_showing' => $faker->boolean(),
                'description' => $faker->text(),
            ];
        }

        foreach ($movies as $movie) {
            Movie::create($movie);
        }
    }
}
