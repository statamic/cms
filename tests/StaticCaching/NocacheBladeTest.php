<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NocacheBladeTest extends TestCase
{
    #[Test]
    #[DefineEnvironment('bladeViewPaths')]
    public function it_renders_the_view_inline_when_there_is_no_matched_route()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        $this->assertSame('<p>region</p>', trim(Blade::render('@nocache("nocache-probe")')));
    }

    public function bladeViewPaths($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/blade',
            ...$app['config']->get('view.paths'),
        ]);
    }
}
