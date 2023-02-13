<?php

namespace Tests\Feature\LaravelStations\Station2;

use App\Http\Controllers\PracticeController;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    /**
     * @group station2
     */
    public function testPracticeが表示されるか(): void
    {
        $response = $this->get('/practice');
        $response->assertStatus(200);
        $response->assertSeeText('practice');
    }

    /**
     * @group station2
     */
    public function testPractice2が表示されるか(): void
    {
        $response = $this->get('/practice2');
        $response->assertStatus(200);
        $response->assertSeeText('practice2');
    }

    /**
     * @group station2
     */
    public function testPractice3が表示されるか(): void
    {
        $response = $this->get('/practice3');
        $response->assertStatus(200);
        $response->assertSeeText('test');
    }

    /**
     * @group station2
     */
    public function testSampleメソッドが存在するか()
    {
        $controller = new PracticeController();
        $response = $controller->sample();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group station2
     */
    public function testSample2メソッドが存在するか()
    {
        $controller = new PracticeController();
        $response = $controller->sample2();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group station2
     */
    public function testSample3メソッドが存在するか()
    {
        $controller = new PracticeController();
        $response = $controller->sample3();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
