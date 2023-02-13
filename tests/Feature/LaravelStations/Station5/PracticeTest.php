<?php

namespace Tests\Feature\LaravelStations\Station5;

use App\Practice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group station5
     */
    public function testGetPracticeが全てのPracticeを返しているか(): void
    {
        for ($i = 0; $i < 11; $i++) {
            Practice::insert([
                'title' => 'タイトル'.$i,
            ]);
        }
        $response = $this->get('/getPractice');
        $practices = Practice::all();
        foreach ($practices as $practice) {
            $response->assertSeeText($practice->title);
        }
    }

}
