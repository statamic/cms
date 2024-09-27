<?php

namespace Tests\Translator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\Translator\MethodDiscovery;

class MethodDiscoveryTest extends TestCase
{
    protected $discovery;

    public function setUp(): void
    {
        $this->discovery = new MethodDiscovery(new Filesystem, [
            __DIR__.'/__fixtures__/php',
            __DIR__.'/__fixtures__/blade',
            __DIR__.'/__fixtures__/vue',
        ]);
    }

    #[Test]
    public function it_discovers_methods()
    {
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
            'vue template with/slash',
            'vue template with bracket(s)',
            'vue script single quote',
            'vue script single quote :count',
            'vue script single quote :param',
            'vue script double quote',
            'vue script double quote :count',
            'vue script double quote :param',
            'vue script backtick quote',
            'vue script backtick quote :count',
            'vue script backtick quote :param',
            'vue script with/slash',
            'vue script with bracket(s)',

            'blade trans single quote string',
            'blade trans single quote :param',
            'blade trans double quote string',
            'blade trans double quote :param',

            'blade trans_choice single quote string',
            'blade trans_choice single quote :param',
            'blade trans_choice double quote string',
            'blade trans_choice double quote :param',

            'blade with/slash',
            'blade with bracket(s)',

            'php trans single quote string',
            'php trans single quote :param',
            'php trans double quote string',
            'php trans double quote :param',

            'php trans_choice single quote string',
            'php trans_choice single quote :param',
            'php trans_choice double quote string',
            'php trans_choice double quote :param',

            'php annotated return single quote string',
            'php annotated return single quote :param',
            'php annotated return double quote string',
            'php annotated return double quote :param',
            'php annotated return with single asterisk',

            'php with/slash',
            'php annotated return with/slash',
            'php with bracket(s)',
            'php annotated return with bracket(s)',

            'vue template trans single quote',
            'vue template trans single quote :param',
            'vue template trans double quote',
            'vue template trans double quote :param',
            'vue template trans backtick quote',
            'vue template trans backtick quote :param',
            'vue script trans single quote',
            'vue script trans single quote :param',
            'vue script trans double quote',
            'vue script trans double quote :param',
            'vue script trans backtick quote',
            'vue script trans backtick quote :param',

            'vue template trans_choice single quote',
            'vue template trans_choice single quote :param',
            'vue template trans_choice double quote',
            'vue template trans_choice double quote :param',
            'vue template trans_choice backtick quote',
            'vue template trans_choice backtick quote :param',
            'vue script trans_choice single quote',
            'vue script trans_choice single quote :param',
            'vue script trans_choice double quote',
            'vue script trans_choice double quote :param',
            'vue script trans_choice backtick quote',
            'vue script trans_choice backtick quote :param',
        ]);

        $actual = $this->discovery->discover();
        $this->assertInstanceOf(Collection::class, $actual);
        $this->assertEquals(
            $expected->sort()->values()->all(),
            $actual->sort()->values()->all()
        );
    }
}
