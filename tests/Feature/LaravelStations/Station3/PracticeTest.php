<?php

namespace Tests\Feature\LaravelStations\Station3;

use App\Http\Controllers\PracticeController;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    /**
     * @group station3
     */
    public function testPracticeが練習という文字列を返すか(): void
    {
        $response = $this->get('/practice');
        $response->assertStatus(200);
        $response->assertSeeText('練習');
    }

    /**
     * @group station3
     */
    public function testPractice2がtestParamという変数を返すか(): void
    {
        $controller = new PracticeController();
        $response = $controller->sample2();
        $this->assertArrayHasKey('testParam', $response->getData());
    }

    /**
     * @group station3
     */
    public function testPractice3がtestParamという変数を返すか(): void
    {
        $controller = new PracticeController();
        $response = $controller->sample3();
        $data = $response->getData();
        $this->assertArrayHasKey('testParam', $data);
        $this->assertEquals('test', $data['testParam']);
    }
}
