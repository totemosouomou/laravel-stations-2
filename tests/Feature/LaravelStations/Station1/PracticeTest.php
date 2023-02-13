<?php

namespace Tests\Feature\LaravelStations\Station1;

use Tests\TestCase;

class PracticeTest extends TestCase
{
    /**
     * @group station1
     */
    public function testPracticeが表示されるか(): void
    {
        $response = $this->get('/practice');
        $response->assertStatus(200);
        $response->assertSeeText('practice');
    }

    /**
     * @group station1
     */
    public function testPractice2が表示されるか(): void
    {
        $response = $this->get('/practice2');
        $response->assertStatus(200);
        $response->assertSeeText('practice2');
    }

    /**
     * @group station1
     */
    public function testPractice3が表示されるか(): void
    {
        $response = $this->get('/practice3');
        $response->assertStatus(200);
        $response->assertSeeText('test');
    }
}
