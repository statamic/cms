<?php

namespace Tests\Composer;

use Mockery;
use Statamic\Extend\Addon;
use Statamic\Updater\AddonChangelog;
use Tests\TestCase;

class AddonChangelogTest extends TestCase
{
    use ChangelogTests;

    protected function changelog()
    {
        $addon = Mockery::mock(new Addon('test'));
        $addon->shouldReceive('version')->andReturn('1.0.1');
        $addon->shouldReceive('marketplaceSellerSlug')->andReturn('seller');
        $addon->shouldReceive('marketplaceSlug')->andReturn('addon');

        return new AddonChangelog($addon);
    }
}
