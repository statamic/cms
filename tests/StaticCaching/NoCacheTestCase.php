<?php

namespace Tests\StaticCaching;

use Statamic\StaticCaching\NoCache\NoCacheManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NoCacheTestCase extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withStandardFakeViews();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.static_caching.strategy', 'half');
    }

    protected function flush()
    {
        $this->app[NoCacheManager::class]->flush();
    }

    protected function assertSameNle($expected, $actual)
    {
        $this->assertSame(trim(StringUtilities::normalizeLineEndings($expected)), trim(StringUtilities::normalizeLineEndings($actual)));
    }
}
