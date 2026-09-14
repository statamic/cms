<?php

namespace Tests\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StacheClear;
use Statamic\Facades\Stache;
use Tests\TestCase;

class StacheClearTest extends TestCase
{
    #[Test]
    public function it_doesnt_add_any_exclusion_if_no_parameter()
    {
        Stache::shouldReceive('exclude')->never()
            ->shouldReceive('clear')->once();

        $this->artisan(StacheClear::class);
    }

    #[Test]
    public function it_adds_exclude()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('clear')->once()->andReturn();

        $this->artisan(StacheClear::class, ['--exclude' => 'foo']);
    }

    #[Test]
    public function it_adds_multiple_excludes()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('exclude')->once()->with('bar')->andReturn()
            ->shouldReceive('clear')->once()->andReturn();

        $this->artisan(StacheClear::class, ['--exclude' => 'foo,bar']);
    }

    #[Test]
    public function it_trims_whitespace_from_excludes()
    {
        Stache::shouldReceive('exclude')->once()->with('foo')->andReturn()
            ->shouldReceive('exclude')->once()->with('bar')->andReturn()
            ->shouldReceive('clear')->once()->andReturn();

        $this->artisan(StacheClear::class, ['--exclude' => 'foo, bar']);
    }
}
