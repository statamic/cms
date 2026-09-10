<?php

namespace Tests\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StacheWarm;
use Statamic\Facades\Stache;
use Tests\TestCase;

class StacheWarmTest extends TestCase
{
    #[Test]
    public function it_doesnt_add_any_exclusion_if_no_parameter()
    {
        Stache::shouldReceive('exclude')->never()
            ->shouldReceive('warm')->once();

        $this->artisan(StacheWarm::class);
    }

    #[Test]
    public function it_adds_exclude()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('warm')->once()->andReturn();

        $this->artisan(StacheWarm::class, ['--exclude' => 'foo']);
    }

    #[Test]
    public function it_adds_multiple_excludes()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('exclude')->once()->with('bar')->andReturn()
            ->shouldReceive('warm')->once()->andReturn();

        $this->artisan(StacheWarm::class, ['--exclude' => 'foo,bar']);
    }
}
