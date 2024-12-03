<?php

namespace Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Please\Kernel;
use Statamic\Facades\Stache;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\TestCase;

class PleaseTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__.'/Kernel.php';

        parent::setUp();
    }

    #[Test]
    public function it_can_run_an_artisan_command_with_statamic_prefix()
    {
        Stache::shouldReceive('clear')->once();

        $this->artisan('statamic:stache:clear');
    }

    #[Test]
    public function statamic_prefixed_commands_will_throw_exception_when_running_in_artisan_without_prefix()
    {
        $this->expectException(CommandNotFoundException::class);

        $this->artisan('flat:camp');
    }

    #[Test]
    public function it_can_run_a_please_command_without_statamic_prefix()
    {
        Stache::shouldReceive('clear')->once();

        $this->please('stache:clear');
    }

    #[Test]
    public function it_can_run_a_please_command_with_statamic_prefix()
    {
        Stache::shouldReceive('clear')->once();

        $this->please('statamic:stache:clear');
    }

    public function please($command, $parameters = [])
    {
        return $this->app[Kernel::class]->call($command, $parameters);
    }
}
