<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades;
use Statamic\Tags\FluentTag;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FluentTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('pages')->save();

        collect(['one', 'two', 'three', 'four', 'five'])->each(function ($slug) {
            EntryFactory::id($slug)
                ->collection('pages')
                ->slug($slug)
                ->make()
                ->data(['content' => "# $slug"])
                ->save();
        });
    }

    /** @test */
    public function it_handles_params_fluently()
    {
        $result = FluentTag::make('collection:pages')->sort('slug:desc')->limit(3);

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertCount(3, $result);
    }

    /** @test */
    public function it_can_explicitly_fetch_result()
    {
        $result = FluentTag::make('collection:pages')->sort('slug:desc')->limit(3)->fetch();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /** @test */
    public function it_can_iterate_over_tag_results()
    {
        $slugs = [];

        foreach (FluentTag::make('collection:pages') as $page) {
            $slugs[] = (string) $page['slug'];
        }

        $expected = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_can_pass_params_fluently_and_terate_over_results()
    {
        $slugs = [];

        foreach (FluentTag::make('collection:pages')->sort('slug:desc')->limit(3) as $page) {
            $slugs[] = (string) $page['slug'];
        }

        $expected = ['two', 'three', 'one'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_augments_by_default()
    {
        $slugs = [];

        foreach (FluentTag::make('collection:pages')->sort('slug:desc')->limit(3) as $page) {
            $slugs[] = (string) trim($page['content']);
        }

        $expected = ['<h1>two</h1>', '<h1>three</h1>', '<h1>one</h1>'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_can_disable_augmentation()
    {
        $slugs = [];

        foreach (FluentTag::make('collection:pages')->sort('slug:desc')->limit(3)->withoutAugmentation() as $page) {
            $slugs[] = (string) $page->content;
        }

        $expected = ['# two', '# three', '# one'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_allows_array_access()
    {
        $result = FluentTag::make('collection:pages');

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertEquals('one', $result[0]['slug']);
        $this->assertEquals('two', $result[1]['slug']);
        $this->assertEquals('three', $result[2]['slug']);
    }

    /** @test */
    public function it_casts_string_results_to_string()
    {
        $result = FluentTag::make('link')->to('fanny-packs');

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertEquals('/fanny-packs', (string) $result);
    }
}
