<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Structures\Nav;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Structures\CollectionStructure;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Statamic\Structures\Structure;
use Statamic\Structures\Tree;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PageTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_and_sets_the_entry()
    {
        $page = new Page;
        $entry = (new Entry)->id('a');
        $this->assertNull($page->entry());

        $return = $page->setEntry($entry);

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    /** @test */
    public function it_gets_the_entry_dynamically_when_its_set_using_a_string()
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
    public function it_gets_and_sets_the_parent()
    {
        $page = new Page;
        $parent = new Page;
        $this->assertNull($page->parent());

        $return = $page->setParent($parent);

        $this->assertEquals($parent, $page->parent());
        $this->assertNotEquals($parent, $page);
        $this->assertEquals($page, $return);
    }

    /** @test */
    public function it_gets_and_sets_the_route()
    {
        $page = new Page;
        $this->assertNull($page->route());

        $return = $page->setRoute('test');

        $this->assertEquals('test', $page->route());
        $this->assertEquals($page, $return);
    }

    /** @test */
    public function it_builds_a_uri_based_on_the_position_in_the_structure_when_the_structure_has_a_collection()
    {
        $entry = new class extends Entry {
            public function id($id = null)
            {
                return 'a';
            }

            public function slug($slug = null)
            {
                return 'entry-slug';
            }
        };
        $collection = tap(\Statamic\Facades\Collection::make('test'))->save();
        $entry->collection('test');

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('id')->andReturn('not-the-entry');
        $parent->shouldReceive('uri')->andReturn('/the/parent/uri');
        $parent->shouldReceive('isRoot')->andReturnFalse();

        $tree = (new Tree)->structure(
            $this->mock(CollectionStructure::class)->shouldReceive('collection')->andReturn($collection)->getMock()
        );

        $page = (new Page)
            ->setTree($tree)
            ->setRoute('/foo/{parent_uri}/bar/{slug}')
            ->setParent($parent)
            ->setEntry($entry);

        $this->assertEquals('/foo/the/parent/uri/bar/entry-slug', $page->uri());
    }

    /** @test */
    public function it_gets_the_entrys_uri_when_the_structure_does_not_have_a_collection()
    {
        $entry = $this->partialMock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('locale')->andReturn('en');
        $entry->shouldReceive('uri')->andReturn('/the/actual/entry/uri');
        $entry->shouldReceive('value')->with('redirect')->andReturnNull();

        $tree = (new Tree)->structure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setEntry($entry);

        $this->assertEquals('/the/actual/entry/uri', $page->uri());
        $this->assertEquals('/the/actual/entry/uri', $page->url());
        $this->assertEquals('http://localhost/the/actual/entry/uri', $page->absoluteUrl());
        $this->assertFalse($page->isRedirect());
    }

    /** @test */
    public function it_gets_the_uri_of_a_redirect_entry()
    {
        $entry = $this->partialMock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('locale')->andReturn('en');
        $entry->shouldReceive('uri')->andReturn('/the/actual/entry/uri');
        $entry->shouldReceive('value')->with('redirect')->andReturn('http://example.com/page');

        $tree = (new Tree)->structure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setEntry($entry);

        $this->assertEquals('/the/actual/entry/uri', $page->uri());
        $this->assertEquals('http://example.com/page', $page->url());
        $this->assertEquals('http://example.com/page', $page->absoluteUrl());
        $this->assertTrue($page->isRedirect());
    }

    /** @test */
    public function it_gets_child_pages()
    {
        $tree = (new Tree)->structure($this->mock(Structure::class));

        $page = (new Page)
            ->setTree($tree)
            ->setEntry((new Entry)->id('123'))
            ->setRoute('')
            ->setChildren([
                ['entry' => 'one'],
                ['entry' => 'two', 'children' => [
                    ['entry' => 'three'],
                ]],
            ]);

        $pages = $page->pages();
        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
        $this->assertEveryItemIsInstanceOf(Page::class, $pages->all());
        $this->assertEquals(['one', 'two'], $pages->all()->map->reference()->all());
    }

    /** @test */
    public function it_gets_flattened_pages()
    {
        EntryAPI::shouldReceive('find')->with('one')
            ->andReturn(new class extends Entry {
                public function id($slug = null)
                {
                    return 'one';
                }

                public function slug($slug = null)
                {
                    return 'one';
                }
            });

        EntryAPI::shouldReceive('find')->with('two')
            ->andReturn(new class extends Entry {
                public function id($slug = null)
                {
                    return 'two';
                }

                public function slug($slug = null)
                {
                    return 'two';
                }
            });

        EntryAPI::shouldReceive('find')->with('three')
            ->andReturn(new class extends Entry {
                public function id($slug = null)
                {
                    return 'three';
                }

                public function slug($slug = null)
                {
                    return 'three';
                }
            });

        EntryAPI::shouldReceive('find')->with('four')
            ->andReturn(new class extends Entry {
                public function id($slug = null)
                {
                    return 'four';
                }

                public function slug($slug = null)
                {
                    return 'four';
                }
            });

        $entry = Mockery::mock(Page::class);
        $entry->shouldReceive('id')->andReturn('root');
        $entry->shouldReceive('slug')->andReturn('');

        $tree = (new Tree)->structure(
            $this->mock(Structure::class)->shouldReceive('collection')->andReturnFalse()->getMock()
        );

        $page = (new Page)
            ->setTree($tree)
            ->setEntry($entry)
            ->setChildren([
                ['entry' => 'one'],
                ['entry' => 'two', 'children' => [
                    ['entry' => 'three', 'children' => [
                        ['entry' => 'four'],
                    ]],
                ]],
            ]);

        $flattened = $page->flattenedPages();
        $this->assertInstanceOf(Collection::class, $flattened);
        $this->assertCount(4, $flattened);
        $this->assertEveryItemIsInstanceOf(Page::class, $flattened);
        $this->assertEquals(['one', 'two', 'three', 'four'], $flattened->map->reference()->all());
    }

    /** @test */
    public function it_forwards_calls_to_the_entry()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('1');
        $entry->shouldReceive('testing')->with('123')->once()->andReturn('hello');

        $page = new Page;
        $page->setEntry($entry);

        $this->assertEquals('hello', $page->testing('123'));
    }
}
