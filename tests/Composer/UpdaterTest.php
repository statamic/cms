<?php

namespace Tests\Composer;

use Facades\Statamic\Console\Processes\Composer;
use Statamic\Updater\Updater;
use Tests\TestCase;

class UpdaterTest extends TestCase
{
    /** @test */
    public function it_can_install()
    {
        Composer::shouldReceive('require')
            ->with('vendor/package', '1.0.1')
            ->once()
            ->andReturnTrue();

        Updater::package('vendor/package')->install('1.0.1');
    }
}
