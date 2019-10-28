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
            'blade underscore single quote string',
            'blade underscore single quote :param',
            'blade underscore double quote string',
            'blade underscore double quote :param',
            'php underscore single quote string',
            'php underscore single quote :param',
            'php underscore double quote string',
            'php underscore double quote :param',
            'vue template single quote',
            'vue template single quote :count',
            'vue template single quote :param',
            'vue template double quote',
            'vue template double quote :count',
            'vue template double quote :param',
            'vue template backtick quote',
            'vue template backtick quote :count',
            'vue template backtick quote :param',
            'vue script single quote',
            'vue script single quote :count',
            'vue script single quote :param',
            'vue script double quote',
            'vue script double quote :count',
            'vue script double quote :param',
            'vue script backtick quote',
            'vue script backtick quote :count',
            'vue script backtick quote :param',
        ]);

        $actual = $discovery->discover();
        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertEquals(
            $expected->sort()->values()->all(),
            $actual->sort()->values()->all()
        );
    }
}