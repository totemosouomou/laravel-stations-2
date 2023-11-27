<?php

namespace Tests\Feature\LaravelStations\Station19;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group station19
 */
class AdminScheduleTest extends TestCase
{
    use RefreshDatabase;

    private int $genreId;

    public function setUp(): void
    {
        parent::setUp();

        $this->genreId = Genre::insertGetId(['name' => 'ジャンル']);
    }

    public function test管理者映画詳細にスケジュール一覧が表示されているか(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $movieId = $this->createMovie('タイトル' . $i)->id;
            for ($j = 0; $j < 10; $j++) {
                Schedule::insert([
                    'movie_id' => $movieId,
                    'start_time' => CarbonImmutable::createFromTime($i, 0, 0),
                    'end_time' => CarbonImmutable::createFromTime($i + 2, 0, 0),
                ]);
            }
        }
        $movies = Movie::all();
        foreach ($movies as $movie) {
            $response = $this->get('/admin/movies/' . $movie->id);
            $response->assertStatus(200);
            $response->assertSeeText($movie->title);
            $response->assertSee($movie->image_url);
            $response->assertSeeText($movie->published_year);
            $response->assertSeeText($movie->description);
            if ($movie->is_showing) {
                $response->assertSeeText('上映中');
            } else {
                $response->assertSeeText('上映予定');
            }
            foreach ($movie->schedules as $schedule) {
                $response->assertSeeText($schedule->start_time);
                $response->assertSeeText($schedule->end_time);
            }
        }
        $response->assertDontSee('true');
        $response->assertDontSee('false');
    }

    public function test管理者映画スケジュール作成画面が表示されているか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $response = $this->get('/admin/movies/' . $movieId . '/schedules/create');
        $response->assertStatus(200);
    }

    public function test管理者映画スケジュール作成画面でスケジュールが作成されるか(): void
    {
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $this->assertScheduleCount(0);
        $movieId = $this->createMovie('タイトル')->id;
        $response = $this->post('/admin/movies/' . $movieId . '/schedules/store', [
            'movie_id' => $movieId,
            'start_time_date' => $startTime->format('Y-m-d'),
            'start_time_time' => $startTime->format('H:i'),
            'end_time_date' => $endTime->addHour()->format('Y-m-d'),
            'end_time_time' => $endTime->addHour()->format('H:i'),
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseCount('schedules', 1);
    }

    public function testRequiredバリデーションが設定されているか(): void
    {
        $this->assertScheduleCount(0);
        $movieId = $this->createMovie('タイトル')->id;
        $response = $this->post('/admin/movies/' . $movieId . '/schedules/store', [
            'movie_id' => null,
            'start_time_date' => null,
            'start_time_time' => null,
            'end_time_date' => null,
            'end_time_time' => null,
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['movie_id', 'start_time_date', 'start_time_time', 'end_time_date', 'end_time_time']);
        $this->assertDatabaseCount('schedules', 0);
    }

    public function test日時フォーマットのバリデーションが設定されているか(): void
    {
        $this->assertScheduleCount(0);
        $movieId = $this->createMovie('タイトル')->id;
        $response = $this->post('/admin/movies/' . $movieId . '/schedules/store', [
            'movie_id' => $movieId,
            'start_time_date' => '2022/01/01',
            'start_time_time' => '01時00分',
            'end_time_date' => '2022/01/01',
            'end_time_time' => '03時00分',
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['start_time_date', 'start_time_time', 'end_time_date', 'end_time_time']);
        $this->assertDatabaseCount('schedules', 0);
    }

    /**
     * @dataProvider dataProvider_開始時刻と終了時刻の関係バリデーションチェック
     */
    public function test新規登録時_開始時刻と終了時刻に矛盾がある場合バリデーションで弾く(array $input, array $expected): void
    {
        $movieId = $this->createMovie('タイトル')->id;

        $response = $this->post(
            '/admin/movies/' . $movieId . '/schedules/store',
            array_merge(['movie_id' => $movieId], $input)
        );

        $response->assertStatus(302);
        $response->assertInvalid($expected);
        $this->assertDatabaseCount('schedules', 0);
    }

    private function assertScheduleCount(int $count): void
    {
        $scheduleCount = Schedule::count();
        $this->assertEquals($scheduleCount, $count);
    }

    public function test管理者映画編スケジュール集画面が表示されているか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $response = $this->get('/admin/schedules/' . $scheduleId . '/edit');
        $response->assertStatus(200);
    }

    public function test管理者映画スケジュール編集画面で映画スケジュールが更新されるか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $response = $this->patch('/admin/schedules/' . $scheduleId . '/update', [
            'movie_id' => $movieId,
            'start_time_date' => $startTime->addHours(2)->format('Y-m-d'),
            'start_time_time' => $startTime->addHours(2)->format('H:i'),
            'end_time_date' => $endTime->addHours(2)->format('Y-m-d'),
            'end_time_time' => $endTime->addHours(2)->format('H:i'),
        ]);
        $response->assertStatus(302);
        $updated = Schedule::find($scheduleId);
        $this->assertEquals($updated->start_time, $startTime->addHours(2));
        $this->assertEquals($updated->end_time, $endTime->addHours(2));
    }

    public function test更新時Requiredバリデーションが設定されているか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $response = $this->patch('/admin/schedules/' . $scheduleId . '/update', [
            'movie_id' => null,
            'start_time_date' => null,
            'start_time_time' => null,
            'end_time_date' => null,
            'end_time_time' => null,
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['movie_id', 'start_time_date', 'start_time_time', 'end_time_date', 'end_time_time']);
    }

    public function test更新時日時フォーマットのバリデーションが設定されているか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $response = $this->patch('/admin/schedules/' . $scheduleId . '/update', [
            'movie_id' => $movieId,
            'start_time_date' => '2022/01/01',
            'start_time_time' => '01時00分',
            'end_time_date' => '2022/01/01',
            'end_time_time' => '03時00分',
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['start_time_date', 'start_time_time', 'end_time_date', 'end_time_time']);
    }

    /**
     * @dataProvider dataProvider_開始時刻と終了時刻の関係バリデーションチェック
     */
    public function test更新時_開始時刻と終了時刻に矛盾がある場合バリデーションで弾く(array $input, array $expected): void
    {
        $movieId = $this->createMovie('タイトル')->id;

        $response = $this->post(
            '/admin/movies/' . $movieId . '/schedules/store',
            array_merge(['movie_id' => $movieId], $input)
        );

        $response->assertStatus(302);
        $response->assertInvalid($expected);
        $this->assertDatabaseCount('schedules', 0);
    }

    public function dataProvider_開始時刻と終了時刻の関係バリデーションチェック(): array
    {
        return [
            '開始日付が終了日付より後' => [
                [
                    'start_time_date' => '2022-12-31',
                    'start_time_time' => '14:00',
                    'end_time_date' => '2022-12-30',
                    'end_time_time' => '15:40',
                ],
                ['start_time_date', 'end_time_date'],
            ],
            '開始時刻が終了時刻より後' => [
                [
                    'start_time_date' => '2022-12-30',
                    'start_time_time' => '15:00',
                    'end_time_date' => '2022-12-30',
                    'end_time_time' => '14:40',
                ],
                ['start_time_time', 'end_time_time'],
            ],
            '開始日時と終了日時が同一' => [
                [
                    'start_time_date' => '2022-12-30',
                    'start_time_time' => '15:00',
                    'end_time_date' => '2022-12-30',
                    'end_time_time' => '15:00',
                ],
                ['start_time_time', 'end_time_time'],
            ],
            '開始時刻と終了時刻の差が 5 分未満' => [
                [
                    'start_time_date' => '2022-12-30',
                    'start_time_time' => '14:00',
                    'end_time_date' => '2022-12-30',
                    'end_time_time' => '14:05',
                ],
                ['start_time_time', 'end_time_time'],
            ],
        ];
    }

    private function createMovie(string $title): Movie
    {
        $movieId = Movie::insertGetId([
            'title' => $title,
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => false,
            'genre_id' => $this->genreId,
        ]);
        return Movie::find($movieId);
    }

    public function testスケジュールを削除できるか(): void
    {
        $movieId = $this->createMovie('タイトル')->id;
        $startTime = new CarbonImmutable('2022-01-01 00:00:00');
        $endTime = new CarbonImmutable('2022-01-01 02:00:00');
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $this->assertScheduleCount(1);
        $response = $this->delete('/admin/schedules/' . $scheduleId . '/destroy');
        $response->assertStatus(302);
        $this->assertScheduleCount(0);
    }

    public function test削除対象が存在しない時404が返るか(): void
    {
        $response = $this->delete('/admin/schedules/1/destroy');
        $response->assertStatus(404);
    }
}
