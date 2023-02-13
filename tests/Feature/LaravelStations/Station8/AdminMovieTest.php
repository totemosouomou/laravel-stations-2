<?php

namespace Tests\Feature\LaravelStations\Station8;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMovieTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group station8
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

    public function test管理者映画作成画面が表示されているか(): void
    {
        $response = $this->get('/admin/movies/create');
        $response->assertStatus(200);
    }

    public function test管理者映画作成画面で映画が作成されるか(): void
    {
        $this->assertMovieCount(0);
        $response = $this->post('/admin/movies/store', [
            'title' => '新しい映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
        ]);
        $response->assertStatus(302);
        $this->assertMovieCount(1);
    }

    public function testRequiredバリデーションが設定されているか(): void
    {
        $this->assertMovieCount(0);
        $response = $this->post('/admin/movies/store', [
            'title' => '',
            'image_url' => '',
            'published_year' => null,
            'description' => '',
            'is_showing' => null
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['title', 'image_url', 'published_year', 'description', 'is_showing']);
        $this->assertMovieCount(0);
    }

    public function test画像URLバリデーションが設定されているか(): void
    {
        $this->assertMovieCount(0);
        $response = $this->post('/admin/movies/store', [
            'title' => '新しい映画',
            'image_url' => '画像URL',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['image_url']);
        $this->assertMovieCount(0);
    }

    public function test映画タイトルの重複バリデーションが設定されているか(): void
    {
        Movie::insert([
            'title' => '最初からある映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => (bool)random_int(0, 1),
        ]);
        $this->assertMovieCount(1);
        $response = $this->post('/admin/movies/store', [
            'title' => '最初からある映画',
            'image_url' => '画像URL',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['title']);
        $this->assertMovieCount(1);
    }

    private function assertMovieCount(int $count): void
    {
        $movieCount = Movie::count();
        $this->assertEquals($movieCount, $count);
    }
}
