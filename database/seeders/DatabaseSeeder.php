<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\Movie;
use App\Models\Sheet;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Movie::factory(10)->create();
        $this->call(SheetsTableSeeder::class);
    }
}
