<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Movie::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'image_url' => $this->faker->imageUrl(),
            'published_year' => $this->faker->numberBetween(2000, 2024), // 2000年から2024年までのランダムな整数
            'description' => $this->faker->paragraph,
            'is_showing' => $this->faker->boolean(), // true または false のランダムな値
        ];
    }
}

