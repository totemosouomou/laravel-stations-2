<?php

namespace Tests\Feature\LaravelStations\Station17;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group station17
 */
class AdminMovieTest extends TestCase
{
    use RefreshDatabase;

    public function test管理者映画一覧に全ての映画のカラムが表示されているか(): void
    {
        $genreId = Genre::insertGetId(['name' => 'ジャンル']);

        for ($i = 0; $i < 3; $i++) {
            Movie::insert([
                'title' => 'タイトル'.$i,
                'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
                'published_year' => 2000 + $i,
                'description' => '概要'.$i,
                'is_showing' => random_int(0, 1),
                'genre_id' => $genreId,
            ]);
        }

        $response = $this->get('/admin/movies');
        $response->assertStatus(200);

        $movies = Movie::all();
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
        $response->assertSeeText('ジャンル');
        $response->assertStatus(200);
    }

    public function test管理者映画作成画面で映画が作成される(): void
    {
        $response = $this->post('/admin/movies/store', [
            'title' => '新しい映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => true,
            'genre' => 'ジャンル',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('movies', 1);
    }

    public function test既に登録済みのジャンルでも映画登録が正常終了する(): void
    {
        $genre = Genre::create(['name' => 'ジャンル']);

        $input = [
            'title' => '新しい映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => true,
            'genre' => $genre->name,
        ];

        $response = $this->post('/admin/movies/store', $input);

        $response->assertStatus(302);
        $this->assertDatabaseHas('movies', [
            'title' => $input['title'],
            'genre_id' => $genre->id,
        ]);
    }

    public function test映画登録失敗時にジャンルも未登録になる(): void
    {
        $input = [
            'title' => str_repeat('test', 100),
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => '概要',
            'is_showing' => true,
            'genre' => 'ジャンル',
        ];

        $response = $this->post('/admin/movies/store', $input);

        $response->assertStatus(500);
        $this->assertDatabaseCount('movies', 0);
        $this->assertDatabaseCount('genres', 0);
    }

    public function testRequiredバリデーションが設定されている(): void
    {
        $this->assertMovieCount(0);
        $response = $this->post('/admin/movies/store', [
            'title' => '',
            'image_url' => '',
            'published_year' => null,
            'description' => '',
            'is_showing' => null,
            'genre' => null,
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['title', 'image_url', 'published_year', 'description', 'is_showing', 'genre']);
        $this->assertMovieCount(0);
    }

    public function test画像urlバリデーションが設定されている(): void
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
        $movie = $this->createMovie();
        $response = $this->post('/admin/movies/store', [
            'title' => $movie->title,
            'image_url' => '画像URL',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
            'genre' => 'ジャンル',
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['title']);
        $this->assertDatabaseCount('movies', 1);
    }

    private function assertMovieCount(int $count): void
    {
        $movieCount = Movie::count();
        $this->assertEquals($movieCount, $count);
    }

    public function test管理者映画編集画面が表示される(): void
    {
        $movie = $this->createMovie();
        $response = $this->get('/admin/movies/'.$movie->id.'/edit');
        $response->assertStatus(200);
        $response->assertSee($movie->title);
        $response->assertSee($movie->image_url);
        $response->assertSee($movie->published_year);
        $response->assertSee($movie->description);
        $response->assertSee($movie->is_showing ? '上映中' : '上映予定');
        $response->assertSee($movie->genre->name);
    }

    public function test管理者映画編集画面で映画が更新される(): void
    {
        $movie = $this->createMovie();
        $genreName = 'ジャンル';
        $input = [
            'title' => '修正後の映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f78.png',
            'published_year' => 2022,
            'description' => '更新された概要',
            'is_showing' => true,
            'genre' => $genreName,
        ];

        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $input);
        $response->assertStatus(302);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => $input['title'],
            'image_url' => $input['image_url'],
            'published_year' => $input['published_year'],
            'description' => $input['description'],
            'is_showing' => $input['is_showing'],
            'genre_id' => Genre::whereName($genreName)->first()->id,
        ]);
    }

    public function testジャンル変更なしでも映画更新に成功する(): void
    {
        $movie = $this->createMovie();
        $input = [
            'title' => '修正後の映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f78.png',
            'published_year' => 2022,
            'description' => '更新された概要',
            'is_showing' => true,
            'genre' => Genre::find($movie->genre_id)->name,
        ];

        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $input);
        $response->assertStatus(302);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => $input['title'],
            'image_url' => $input['image_url'],
            'published_year' => $input['published_year'],
            'description' => $input['description'],
            'is_showing' => $input['is_showing'],
            'genre_id' => $movie->genre_id,
        ]);
    }

    public function test映画更新失敗時_新規ジャンルは登録されない(): void
    {
        $movie = $this->createMovie();
        $input = [
            'title' => str_repeat('test', 100),
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => '概要',
            'is_showing' => true,
            'genre' => '新規ジャンル',
        ];

        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $input);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('movies', ['title' => $input['title']]);
        $this->assertDatabaseMissing('genres', ['name' => $input['genre']]);
    }

    public function test更新時Requiredバリデーションが設定されている(): void
    {
        $movie = $this->createMovie();
        $data = [
            'title' => '',
            'image_url' => '',
            'published_year' => null,
            'description' => '',
            'is_showing' => null,
            'genre' => null,
        ];
        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $data);
        $response->assertStatus(302);
        $response->assertInvalid(['title', 'image_url', 'published_year', 'description', 'is_showing', 'genre']);
    }

    public function test更新時画像urlバリデーションが設定されているか(): void
    {
        $movie = $this->createMovie();
        $data = [
            'title' => '新しい映画',
            'image_url' => '画像URL',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
        ];
        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $data);
        $response->assertStatus(302);
        $response->assertInvalid(['image_url']);
    }

    public function test更新時映画タイトルの重複バリデーションが設定されているか(): void
    {
        $genre = Genre::create(['name' => '既存ジャンル']);
        $duplicatedTitle = '既存の映画';
        Movie::insert([
            'title' => $duplicatedTitle,
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => (bool)random_int(0, 1),
            'genre_id' => $genre->id,
        ]);

        $movie = $this->createMovie();
        $data = [
            'title' => $duplicatedTitle,
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2022,
            'description' => "概要\n概要\n",
            'is_showing' => (bool)random_int(0, 1),
            'genre' => $genre->name,
        ];
        $response = $this->patch('/admin/movies/'.$movie->id.'/update', $data);
        $response->assertStatus(302);
        $response->assertInvalid(['title']);
    }

    public function test_moviesテーブルのtitleにユニークキー制約を設定している(): void
    {
        $data = [
            'title' => '最初からある映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => (bool)random_int(0, 1),
            'genre_id' => Genre::insertGetId(['name' => 'ジャンル']),
        ];
        Movie::insert($data);

        try {
            Movie::insert($data);
            $this->fail();
        } catch (QueryException) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function createMovie(): Movie
    {
        $movieId = Movie::insertGetId([
            'title' => '最初からある映画',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => false,
            'genre_id' => Genre::insertGetId(['name' => 'ジャンル']),
        ]);
        return Movie::find($movieId);
    }

    public function test映画を削除できるか(): void
    {
        $movie = $this->createMovie();
        $this->assertMovieCount(1);
        $response = $this->delete('/admin/movies/'.$movie->id.'/destroy');
        $response->assertStatus(302);
        $this->assertMovieCount(0);
    }

    public function test削除対象が存在しない時404が返るか(): void
    {
        $response = $this->delete('/admin/movies/1/destroy');
        $response->assertStatus(404);
    }
}
