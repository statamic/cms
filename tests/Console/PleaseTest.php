<?php

namespace Tests\Console;

use Statamic\Console\Please\Kernel;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\TestCase;

class PleaseTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__.'/Kernel.php';

        parent::setUp();
    }

    /** @test */
    public function it_can_run_an_artisan_command_with_statamic_prefix()
    {
        $this->artisan('statamic:static:clear');

        $this->expectException(CommandNotFoundException::class);
        $this->artisan('static:clear');
    }

    /** @test */
    public function it_can_run_a_please_command_without_statamic_prefix()
    {
        $this->please('static:clear');

        $this->expectException(CommandNotFoundException::class);
        $this->please('statamic:static:clear');
    }

    public function please($command, $parameters = [])
    {
        return $this->app[Kernel::class]->call($command, $parameters);
    }
}
