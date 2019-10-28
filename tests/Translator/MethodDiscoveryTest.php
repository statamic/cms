<?php

namespace Tests\Translator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Statamic\Translator\MethodDiscovery;

class MethodDiscoveryTest extends TestCase
{
    /** @test */
    function it_discovers_methods()
    {
        $discovery = new MethodDiscovery(new Filesystem, [
            __DIR__.'/__fixtures__/php',
            __DIR__.'/__fixtures__/blade',
            __DIR__.'/__fixtures__/vue',
        ]);

        $expected = collect([
            'blade underscore string',
            'blade underscore :param',
            'php underscore string',
            'php underscore :param',
            'vue template',
            'vue template :count',
            'vue template :param',
            'vue script',
            'vue script :count',
            'vue script :param',
        ]);

        $actual = $discovery->discover();
        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertEquals(
            $expected->sort()->values()->all(),
            $actual->sort()->values()->all()
        );
    }
}