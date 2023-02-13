<?php

namespace Tests\Feature\LaravelStations\Station7;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMovieTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group station7
     */
    public function test管理者映画一覧に全ての映画のカラムが表示されているか(): void
    {
        $count = 12;
        for ($i = 0; $i < $count; $i++) {
            Movie::insert([
                'title' => 'タイトル'.$i,
                'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
                'published_year' => 2000 + $i,
                'description' => '概要'.$i,
                'is_showing' => (bool)random_int(0, 1),
            ]);
        }
        $movies = Movie::all();
        $response = $this->get('/admin/movies');
        $response->assertStatus(200);
        foreach ($movies as $movie) {
            $response->assertSeeText($movie->title);
            $response->assertSee($movie->image_url);
            $response->assertSeeText($movie->published_year);
            $response->assertSeeText($movie->description);
            if ($movie->is_showing) {
                $response->assertSeeText('上映中');
            } else {
                $response->assertSeeText('上映予定');
            }
        }
        $response->assertDontSee('true');
        $response->assertDontSee('false');
    }
}
