<?php

namespace Tests\Feature\LaravelStations\Station4;

use Mockery;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    /**
     * @group station4
     */
    public function testGetPracticeがPracticeallを実行しているか(): void
    {
        $mock = Mockery::mock('overload:App\Practice');
        $mock->shouldReceive('all')
            ->once()->andReturn([]);
        $response = $this->get('/getPractice');
        $response->assertJson([]);
    }
}
