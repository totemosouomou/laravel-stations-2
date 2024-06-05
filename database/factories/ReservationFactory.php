<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\Sheet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'schedule_id' => Schedule::factory(),
            'sheet_id' => Sheet::inRandomOrder()->first()->id,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'date' => Carbon::now()->format('Y-m-d'),
            'is_canceled' => false,
        ];
    }
}
