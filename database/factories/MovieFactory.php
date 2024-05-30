<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->word, // 一意の単語を生成
            'image_url' => $this->faker->imageUrl(), // ランダムな画像URLを生成
            'published_year' => $this->faker->numberBetween(2000, 2024), // 2000年から2024年までのランダムな整数
            'is_showing' => $this->faker->boolean() // true または false のランダムな値
        ];
    }
}
