<?php

namespace Tests\Licensing;

use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OutpostScheduleTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        if ($appKey = $this->providedData()['appKey'] ?? null) {
            $app['config']->set('app.key', $appKey);
        }
    }

    #[Test]
    #[DataProvider('appKeyProvider')]
    public function it_schedules_an_hourly_outpost_ping_at_a_minute_derived_from_the_app_key(string $appKey, string $expression)
    {
        $this->assertEquals($expression, $this->outpostEvent()->expression);
    }

    public static function appKeyProvider(): array
    {
        return [
            ['appKey' => 'base64:Kqt7tNXyHrEh4dqMv7oLKO+OKwHXBQ3WmQ3vEBYDgZ8=', 'expression' => '45 * * * *'],
            ['appKey' => 'base64:9fLx3Q2XJ0cH2nRKfJd8lZq0n1v0Yk8kq2gS2+X3eBM=', 'expression' => '58 * * * *'],
        ];
    }

    #[Test]
    #[WithConfig('app.key', null)]
    public function it_derives_the_minute_from_the_base_path_when_there_is_no_app_key()
    {
        $minute = crc32(base_path()) % 60;

        $this->assertEquals("{$minute} * * * *", $this->outpostEvent()->expression);
    }

    private function outpostEvent()
    {
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($event) => $event->description === 'statamic-outpost');

        $this->assertNotNull($event);

        return $event;
    }
}
