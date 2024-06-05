<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'movie_id' => Movie::factory(), // 映画が関連付けられていることを保証
            'start_time' => $this->faker->time,
            'end_time' => $this->faker->time,
        ];
    }
}
