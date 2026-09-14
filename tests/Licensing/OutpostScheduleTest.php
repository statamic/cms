<?php

namespace Tests\Licensing;

use Illuminate\Console\Scheduling\Schedule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OutpostScheduleTest extends TestCase
{
    #[Test]
    public function it_schedules_an_hourly_outpost_ping()
    {
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($event) => $event->description === 'statamic-outpost');

        $this->assertNotNull($event);
        $this->assertEquals('0 * * * *', $event->expression);
    }
}
