<?php

namespace Tests\Feature\LaravelStations\Station19;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\Sheet;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group station19
 */
class SheetTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testSeedコマンドでマスターデータが作成されるか(): void
    {
        $this->assertEquals(Sheet::count(), 15);
    }

    public function test座席一覧画面に全ての座席が表示されるか(): void
    {
        $response = $this->get('/sheets');
        $response->assertStatus(200);
        $sheets = Sheet::all();
        foreach ($sheets as $sheet) {
            $response->assertSeeText($sheet->row .'-'. $sheet->column);
        }
    }

    public function test座席予約画面が表示されるか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/sheets?date='.CarbonImmutable::now());
        $response->assertStatus(200);
    }

    public function test座席予約画面がエラー時400を返すか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/sheets');
        $response->assertStatus(400);
    }

    public function test予約ページが表示されるか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/reservations/create?date='.CarbonImmutable::now().'&sheetId='.Sheet::first()->id);
        $response->assertStatus(200);
    }

    public function test予約ページがエラー時400を返すか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/reservations/create');
        $response->assertStatus(400);
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/reservations/create?date='.CarbonImmutable::now());
        $response->assertStatus(400);
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/reservations/create?sheetId='.Sheet::first()->id);
        $response->assertStatus(400);
    }

    public function test予約を保存できるかどうか(): void
    {
        $this->assertReservationCount(0);
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->post('/reservations/store', [
            'schedule_id' => $scheduleId,
            'sheet_id' => Sheet::first()->id,
            'name' => '予約者氏名',
            'email' => "techbowl@techbowl.com",
            'date' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $response->assertStatus(302);
        $this->assertReservationCount(1);
    }

    public function test予約のバリデーションチェック(): void
    {
        $this->assertReservationCount(0);
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        $response = $this->post('/reservations/store', [
            'schedule_id' => null,
            'sheet_id' => null,
            'name' => null,
            'email' => "techbowl@",
            'date' => null,
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['schedule_id', 'sheet_id', 'name', 'email', 'date']);
        $this->assertReservationCount(0);
    }

    public function test予約重複時時エラーを返す(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        Reservation::insert([
            'schedule_id' => $scheduleId,
            'sheet_id' => Sheet::first()->id,
            'name' => '予約者氏名',
            'email' => "techbowl@techbowl.com",
            'date' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $this->assertReservationCount(1);
        $response = $this->post('/reservations/store', [
            'schedule_id' => $scheduleId,
            'sheet_id' => Sheet::first()->id,
            'name' => '予約者氏名',
            'email' => "techbowl@techbowl.com",
            'date' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $response->assertStatus(302);
        $this->assertReservationCount(1);
    }

    public function testDBのUnique制限がかかっているかどうか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        Reservation::insert([
            'schedule_id' => $scheduleId,
            'sheet_id' => Sheet::first()->id,
            'name' => '予約者氏名',
            'email' => "techbowl@techbowl.com",
            'date' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $this->assertReservationCount(1);
        try {
            Reservation::insert([
                'schedule_id' => $scheduleId,
                'sheet_id' => Sheet::first()->id,
                'name' => '予約者氏名',
                'email' => "techbowl@techbowl.com",
                'date' => CarbonImmutable::now()->format('Y-m-d'),
            ]);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
        $this->assertReservationCount(1);
    }

    public function test既に存在する予約の場合予約ページが400となるか(): void
    {
        [$movieId, $scheduleId] = $this->createMovieAndSchedule();
        Reservation::insert([
            'date' => new CarbonImmutable(),
            'schedule_id' => $scheduleId,
            'sheet_id' => Sheet::first()->id,
            'email' => 'sample@techbowl.com',
            'name' => 'サンプルユーザー',
        ]);
        $response = $this->get('/movies/'.$movieId.'/schedules/'.$scheduleId.'/reservations/create?date='.CarbonImmutable::now().'&sheetId='.Sheet::first()->id);
        $response->assertStatus(400);
    }

    private function createMovieAndSchedule()
    {
        $genreId = Genre::insertGetId(['name' => 'ジャンル']);
        $movieId = Movie::insertGetId([
            'title' => 'タイトル',
            'image_url' => 'https://techbowl.co.jp/_nuxt/img/6074f79.png',
            'published_year' => 2000,
            'description' => '概要',
            'is_showing' => (bool)random_int(0, 1),
            'genre_id' => $genreId,
        ]);
        $startTime = CarbonImmutable::now();
        $scheduleId = Schedule::insertGetId([
            'movie_id' => $movieId,
            'start_time' => $startTime,
            'end_time' => $startTime->addHours(2),
        ]);
        return [$movieId, $scheduleId];
    }

    private function assertReservationCount(int $count): void
    {
        $this->assertEquals($count, Reservation::count());
    }
}
