<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MountTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_collection_mount()
    {
        Collection::make('pages')->routes('pages/{slug}')->save();
        $mount = EntryFactory::collection('pages')->slug('blog')->create();
        Collection::make('blog')->routes('{mount}/{slug}')->mount($mount->id())->save();

        $this->assertParseEquals(
            '/pages/blog',
            '{{ mount:blog }}',
        );

        $this->assertParseEquals(
            '/pages/blog',
            '{{ mount handle="blog" }}',
        );
    }

    private function assertParseEquals($expected, $template, $context = [])
    {
        $this->assertEquals($expected, (string) Antlers::parse($template, $context));
    }
}
