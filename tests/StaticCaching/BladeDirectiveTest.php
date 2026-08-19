<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BladeDirectiveTest extends TestCase
{
    #[Test]
    public function it_renders_the_view_inline_when_there_is_no_matched_route()
    {
        config(['statamic.static_caching.strategy' => 'half']);

        View::addLocation(__DIR__.'/blade');

        $this->assertSame('<p>region</p>', trim(Blade::render('@nocache("nocache-probe")')));
    }
}
