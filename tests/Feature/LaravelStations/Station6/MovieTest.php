<?php

namespace Tests\Feature\LaravelStations\Station6;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group station6
     */
    public function test映画一覧に全ての映画のタイトル、画像URLが表示されているか(): void
    {
        $count = 12;
        for ($i = 0; $i < $count; $i++) {
            Movie::insert([
                'title' => 'タイトル'.$i,
                'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png'
            ]);
        }
        $movies = Movie::all();
        $response = $this->get('/movies');
        $response->assertStatus(200);
        foreach ($movies as $movie) {
            $response->assertSeeText($movie->title);
            $response->assertSee($movie->image_url);
        }
    }
}
