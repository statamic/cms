<?php

namespace Tests\Composer;

use Facades\Statamic\Version;
use Statamic\Updater\CoreChangelog;
use Tests\TestCase;

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
