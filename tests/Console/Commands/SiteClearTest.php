<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteClearTest extends TestCase
{
    #[Test]
    public function it_has_up_to_date_config_stubs()
    {
        // site:clear overwrites the published stache.php and users.php with these
        // stubs (see SiteClear::resetStatamicConfigs), copying them verbatim, so
        // they need to stay in sync with the package's own config defaults, aside
        // from the values a fresh Statamic site intentionally overrides.
        $stubs = statamic_path('src/Console/Commands/stubs/config');

        $stache = require $stubs.'/stache.php.stub';
        $stacheDefaults = require statamic_path('config/stache.php');

        // A fresh Statamic install leaves 'stores' empty and relies on the
        // native defaults being merged in by Stache\ServiceProvider, rather
        // than duplicating every store definition in the published config.
        $this->assertSame([], $stache['stores']);

        $this->assertEquals(
            Arr::except($stacheDefaults, 'stores'),
            Arr::except($stache, 'stores')
        );

        $users = require $stubs.'/users.php.stub';
        $usersDefaults = require statamic_path('config/users.php');

        // Fresh Statamic sites intentionally use the file repository, not
        // eloquent, which is only the default when installing into an
        // existing Laravel app.
        $this->assertSame('file', $users['repository']);

        $this->assertEquals(
            Arr::except($usersDefaults, 'repository'),
            Arr::except($users, 'repository')
        );
    }
}
