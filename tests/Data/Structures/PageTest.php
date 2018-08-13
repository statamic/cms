<?php

namespace Tests\Data\Structures;

use Mockery;
use Tests\TestCase;
use Statamic\Data\Entries\Entry;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Page;
use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Structures\Pages;

class PageTest extends TestCase
{
    /** @test */
    function it_gets_and_sets_the_entry()
    {
        $page = new Page;
        $entry = new Entry;
        $this->assertNull($page->entry());

        $return = $page->setEntry($entry);

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    /** @test */
    function it_gets_the_entry_dynamically_when_its_set_using_a_string()
    {
        EntryAPI::shouldReceive('find')
            ->with('example-page')
            ->andReturn($entry = new Entry);

        $page = new Page;
        $this->assertNull($page->entry());

        $return = $page->setEntry('example-page');

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    /** @test */
    function it_gets_and_sets_the_parent_uri()
    {
        $page = new Page;
        $this->assertNull($page->parentUri());

        $return = $page->setParentUri('/parent-uri');

        $this->assertEquals('/parent-uri', $page->parentUri());
        $this->assertEquals($page, $return);
    }

    /** @test */
    function it_gets_and_sets_the_route()
    {
        $page = new Page;
        $this->assertNull($page->route());

        $return = $page->setRoute('test');

        $this->assertEquals('test', $page->route());
        $this->assertEquals($page, $return);
    }

    /** @test */
    function it_gets_the_uri()
    {
        $entry = new class extends Entry {
            public function slug($slug = null) {
                return 'entry-slug';
            }
        };

        $page = (new Page)
            ->setRoute('/foo/{parent_uri}/bar/{slug}')
            ->setParentUri('/the/parent/uri')
            ->setEntry($entry);

        $this->assertEquals('/foo/the/parent/uri/bar/entry-slug', $page->uri());
    }

    /** @test */
    function it_gets_child_pages()
    {
        $page = (new Page)
            ->setEntry(new Entry)
            ->setRoute('')
            ->setChildren([
                ['entry' => 'one'],
                ['entry' => 'two', 'children' => [
                    ['entry' => 'three']
                ]]
            ]);

        $pages = $page->pages();
        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
        $this->assertEveryItemIsInstanceOf(Page::class, $pages->all());
        $this->assertEquals(['one', 'two'], $pages->all()->keys()->all());
    }

    /** @test */
    function it_gets_flattened_pages()
    {
        EntryAPI::shouldReceive('find')->with('one')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'one'; }
            });

        EntryAPI::shouldReceive('find')->with('two')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'two'; }
            });

        EntryAPI::shouldReceive('find')->with('three')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'three'; }
            });

        EntryAPI::shouldReceive('find')->with('four')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'four'; }
            });

        $page = (new Page)
            ->setEntry(new Entry)
            ->setRoute('{parent_uri}/{slug}')
            ->setChildren([
                ['entry' => 'one'],
                ['entry' => 'two', 'children' => [
                    ['entry' => 'three', 'children' => [
                        ['entry' => 'four']
                    ]]
                ]],
            ]);

        $flattened = $page->flattenedPages();
        $this->assertInstanceOf(Collection::class, $flattened);
        $this->assertCount(4, $flattened);
        $this->assertEquals([
            'one' => '/one',
            'two' => '/two',
            'three' => '/two/three',
            'four' => '/two/three/four',
        ], $flattened->map->uri()->all());
    }
}
