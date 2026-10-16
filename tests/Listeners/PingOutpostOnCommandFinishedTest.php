<?php

namespace Tests\Listeners;

use Illuminate\Console\Events\CommandFinished;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\Radio;
use Statamic\Listeners\PingOutpostOnCommandFinished;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

class PingOutpostOnCommandFinishedTest extends TestCase
{
    #[Test]
    public function it_pings_when_the_command_should_be_pinged()
    {
        $radio = $this->mock(Radio::class);
        $radio->shouldReceive('shouldPingCommand')->once()->with('statamic:stache:clear')->andReturnTrue();
        $radio->shouldReceive('ping')->once();

        (new PingOutpostOnCommandFinished($radio))->handle($this->event('statamic:stache:clear'));
    }

    #[Test]
    public function it_does_not_ping_when_the_command_should_be_skipped()
    {
        $radio = $this->mock(Radio::class);
        $radio->shouldReceive('shouldPingCommand')->once()->with('schedule:run')->andReturnFalse();
        $radio->shouldReceive('ping')->never();

        (new PingOutpostOnCommandFinished($radio))->handle($this->event('schedule:run'));
    }

    private function event(?string $command): CommandFinished
    {
        return new CommandFinished($command, new ArrayInput([]), new NullOutput, 0);
    }
}
