<?php

namespace Tests\Console\Commands\Concerns;

use Facades\Statamic\Console\Processes\Composer;

trait FakesComposerInstalls
{
    private function fakeSuccessfulComposerRequire()
    {
        Composer::shouldReceive('withoutQueue', 'throwOnFailure', 'require')->andReturnSelf();
    }
}
