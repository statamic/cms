<?php

namespace Tests\Composer;

use Tests\TestCase;
use Mockery;
use Statamic\Extend\Addon;
use Statamic\Updater\AddonChangelog;

class AddonChangelogTest extends TestCase
{
    use ChangelogTests;

    protected function changelog()
    {
        $addon = Mockery::mock(new Addon('test'));
        $addon->shouldReceive('version')->andReturn('1.0.1');

        return new AddonChangelog($addon);
    }
}
