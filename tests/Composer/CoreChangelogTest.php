<?php

namespace Tests\Composer;

use Tests\TestCase;
use Facades\Statamic\Version;
use Statamic\Updater\CoreChangelog;

class CoreChangelogTest extends TestCase
{
    use ChangelogTests;

    protected $shouldFakeVersion = false;

    protected function changelog()
    {
        Version::shouldReceive('get')->andReturn('1.0.1');

        return new CoreChangelog;
    }
}
