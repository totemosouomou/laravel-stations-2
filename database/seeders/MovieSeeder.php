<?php

namespace Database\Factories;

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
            'title' => $this->faker->realText(10),
            'image_url' => $this->faker->imageUrl() // imageUrl()を使用してランダムな画像URLを生成する
        ];
    }
}