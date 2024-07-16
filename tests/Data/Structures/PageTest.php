<?php

namespace Tests\Data\Structures;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_gets_and_sets_the_entry()
    {
        $page = new Page;
        $entry = (new Entry)->id('a');
        $this->assertNull($page->entry());

        $return = $page->setEntry($entry);

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    #[Test]
    public function it_gets_the_entry_dynamically_when_its_set_using_a_string()
    {
        EntryAPI::shouldReceive('find')
            ->with('example-page')
            ->andReturn($entry = new Entry);

        $tree = $this->mock(Tree::class)->shouldReceive('entry')->with('example-page')->once()->andReturn($entry)->getMock();

        $page = (new Page)->setTree($tree);
        $this->assertNull($page->entry());

        $return = $page->setEntry('example-page');

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    #[Test]
    public function it_gets_the_entry_dynamically_when_its_set_using_an_int()
    {
        EntryAPI::shouldReceive('find')
            ->with(3)
            ->andReturn($entry = new Entry);

        $tree = $this->mock(Tree::class)->shouldReceive('entry')->with(3)->once()->andReturn($entry)->getMock();

        $page = (new Page)->setTree($tree);
        $this->assertNull($page->entry());

        $return = $page->setEntry(3);

        $this->assertEquals($entry, $page->entry());
        $this->assertEquals($page, $return);
    }

    #[Test]
    public function it_gets_the_title()
    {
        $page = new Page;

        $this->assertNull($page->title());
        $this->assertFalse($page->hasCustomTitle());

        $page->setTitle('Test');

        $this->assertEquals('Test', $page->title());
        $this->assertTrue($page->hasCustomTitle());
    }

    #[Test]
    public function it_gets_the_title_when_referencing_an_entry()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('value')->andReturn('Entry Title');

        $page = new Page;

        $this->assertNull($page->title());
        $this->assertFalse($page->hasCustomTitle());

        $page->setEntry($entry);

        $this->assertEquals('Entry Title', $page->title());
        $this->assertFalse($page->hasCustomTitle());
    }

    #[Test]
    public function it_gets_the_custom_title_when_referencing_an_entry()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('value')->andReturn('Entry Title');

        $page = new Page;

        $this->assertNull($page->title());
        $this->assertFalse($page->hasCustomTitle());

        $page
            ->setEntry($entry)
            ->setTitle('Custom Title');

        $this->assertEquals('Custom Title', $page->title());
        $this->assertTrue($page->hasCustomTitle());
    }

    #[Test]
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

    #[Test]
    public function it_gets_and_sets_the_route()
    {
        $page = new Page;
        $this->assertNull($page->route());

        $return = $page->setRoute('test');

        $this->assertEquals('test', $page->route());
        $this->assertEquals($page, $return);
    }

    #[Test]
    public function it_builds_a_uri_based_on_the_position_in_the_structure_when_the_structure_has_a_collection()
    {
        $entry = new class extends Entry
        {
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

        $tree = $this->newTree()->setStructure(
            $this->mock(CollectionStructure::class)->shouldReceive('collection')->andReturn($collection)->getMock()
        );

        $page = (new Page)
            ->setTree($tree)
            ->setRoute('/foo/{parent_uri}/bar/{slug}')
            ->setParent($parent)
            ->setEntry($entry);

        $this->assertEquals('/foo/the/parent/uri/bar/entry-slug', $page->uri());
        $this->assertFalse($page->hasCustomUrl());
    }

    #[Test]
    #[DataProvider('stripExtensionFromParentUriProvider')]
    public function it_builds_a_uri_and_strips_out_file_extensions_from_parent_uri($ext)
    {
        $entry = new class extends Entry
        {
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
        $parent->shouldReceive('id')->andReturn('the-parent-entry');
        $parent->shouldReceive('uri')->andReturn('/the-parent-entry.'.$ext);
        $parent->shouldReceive('isRoot')->andReturnFalse();

        $tree = $this->newTree()->setStructure(
            $this->mock(CollectionStructure::class)->shouldReceive('collection')->andReturn($collection)->getMock()
        );

        $page = (new Page)
            ->setTree($tree)
            ->setRoute('/{parent_uri}/{slug}.'.$ext)
            ->setParent($parent)
            ->setEntry($entry);

        $this->assertEquals('/the-parent-entry/entry-slug.'.$ext, $page->uri());
        $this->assertFalse($page->hasCustomUrl());
    }

    public static function stripExtensionFromParentUriProvider()
    {
        return [
            'html' => ['html'],
            'htm' => ['htm'],
        ];
    }

    #[Test]
    public function it_gets_the_entrys_uri_when_the_structure_does_not_have_a_collection()
    {
        $entry = $this->partialMock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('locale')->andReturn('en');
        $entry->shouldReceive('uri')->andReturn('/the/actual/entry/uri');
        $entry->shouldReceive('value')->with('redirect')->andReturnNull();

        $tree = $this->newTree()->setStructure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setEntry($entry);

        $this->assertEquals('/the/actual/entry/uri', $page->uri());
        $this->assertEquals('/the/actual/entry/uri', $page->url());
        $this->assertEquals('/the/actual/entry/uri', $page->urlWithoutRedirect());
        $this->assertEquals('http://localhost/the/actual/entry/uri', $page->absoluteUrl());
        $this->assertEquals('http://localhost/the/actual/entry/uri', $page->absoluteUrlWithoutRedirect());
        $this->assertFalse($page->isRedirect());
        $this->assertFalse($page->hasCustomUrl());
    }

    #[Test]
    public function it_gets_the_uri_of_a_redirect_entry()
    {
        $entry = $this->partialMock(Entry::class);
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('locale')->andReturn('en');
        $entry->shouldReceive('uri')->andReturn('/the/actual/entry/uri');
        $entry->shouldReceive('value')->with('redirect')->andReturn('http://example.com/page');

        $tree = $this->newTree()->setStructure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setEntry($entry);

        $this->assertEquals('/the/actual/entry/uri', $page->uri());
        $this->assertEquals('http://example.com/page', $page->url());
        $this->assertEquals('/the/actual/entry/uri', $page->urlWithoutRedirect());
        $this->assertEquals('http://example.com/page', $page->absoluteUrl());
        $this->assertEquals('http://localhost/the/actual/entry/uri', $page->absoluteUrlWithoutRedirect());
        $this->assertTrue($page->isRedirect());
        $this->assertFalse($page->hasCustomUrl());
    }

    #[Test]
    public function it_gets_the_uri_of_a_hardcoded_relative_link()
    {
        $tree = $this->newTree()->setStructure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setUrl('/blog');

        $this->assertEquals('/blog', $page->uri());
        $this->assertEquals('/blog', $page->url());
        $this->assertEquals('/blog', $page->urlWithoutRedirect());
        $this->assertEquals('http://localhost/blog', $page->absoluteUrl());
        $this->assertEquals('http://localhost/blog', $page->absoluteUrlWithoutRedirect());
        $this->assertFalse($page->isRedirect());
        $this->assertTrue($page->hasCustomUrl());
    }

    #[Test]
    public function it_gets_the_uri_of_a_hardcoded_absolute_link()
    {
        $tree = $this->newTree()->setStructure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setUrl('https://google.com');

        $this->assertEquals('https://google.com', $page->uri());
        $this->assertEquals('https://google.com', $page->url());
        $this->assertEquals('https://google.com', $page->urlWithoutRedirect());
        $this->assertEquals('https://google.com', $page->absoluteUrl());
        $this->assertEquals('https://google.com', $page->absoluteUrlWithoutRedirect());
        $this->assertFalse($page->isRedirect());
        $this->assertTrue($page->hasCustomUrl());
    }

    #[Test]
    public function it_gets_the_uri_of_a_hardcoded_text_only_page()
    {
        $tree = $this->newTree()->setStructure(
            $this->mock(Nav::class)
        );

        $page = (new Page)
            ->setTree($tree)
            ->setTitle('Test');

        $this->assertNull($page->uri());
        $this->assertNull($page->url());
        $this->assertNull($page->urlWithoutRedirect());
        $this->assertNull($page->absoluteUrl());
        $this->assertNull($page->absoluteUrlWithoutRedirect());
        $this->assertFalse($page->isRedirect());
        $this->assertFalse($page->hasCustomUrl());
    }

    #[Test]
    public function it_gets_child_pages()
    {
        $tree = $this->newTree()->setStructure($this->mock(Structure::class));

        $page = (new Page)
            ->setTree($tree)
            ->setEntry((new Entry)->id('123'))
            ->setRoute('')
            ->setChildren([
                ['id' => 'one'],
                ['id' => 'two', 'children' => [
                    ['id' => 'three'],
                ]],
            ]);

        $pages = $page->pages();
        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
        $this->assertEveryItemIsInstanceOf(Page::class, $pages->all());
        $this->assertEquals(['one', 'two'], $pages->all()->map->id()->all());
    }

    #[Test]
    public function it_gets_flattened_pages()
    {
        $page = (new Page)
            ->setTree($this->newTree())
            ->setChildren([
                ['id' => 'one'],
                ['id' => 'two', 'children' => [
                    ['id' => 'three', 'children' => [
                        ['id' => 'four'],
                    ]],
                ]],
            ]);

        $flattened = $page->flattenedPages();
        $this->assertInstanceOf(Collection::class, $flattened);
        $this->assertCount(4, $flattened);
        $this->assertEveryItemIsInstanceOf(Page::class, $flattened);
        $this->assertEquals(['one', 'two', 'three', 'four'], $flattened->map->id()->all());
    }

    #[Test]
    public function it_forwards_calls_to_the_entry()
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('1');
        $entry->shouldReceive('testing')->with('123')->once()->andReturn('hello');

        $page = new Page;
        $page->setEntry($entry);

        $this->assertEquals('hello', $page->testing('123'));
    }

    #[Test]
    public function it_gets_values()
    {
        $page = new Page;

        $this->assertInstanceOf(Collection::class, $page->data());
        $this->assertEquals([], $page->data()->all());
        $this->assertInstanceOf(Collection::class, $page->pageData());
        $this->assertEquals([], $page->pageData()->all());
        $this->assertInstanceOf(Collection::class, $page->values());
        $this->assertEquals([], $page->values()->all());
        $this->assertNull($page->value('foo'));
        $this->assertNull($page->get('foo'));
        $this->assertEquals('fallback', $page->get('unknown', 'fallback'));

        $page->setPageData(['foo' => 'bar']);

        $this->assertInstanceOf(Collection::class, $page->data());
        $this->assertEquals(['foo' => 'bar'], $page->data()->all());
        $this->assertInstanceOf(Collection::class, $page->pageData());
        $this->assertEquals(['foo' => 'bar'], $page->pageData()->all());
        $this->assertInstanceOf(Collection::class, $page->values());
        $this->assertEquals(['foo' => 'bar'], $page->values()->all());
        $this->assertEquals('bar', $page->value('foo'));
        $this->assertEquals('bar', $page->get('foo'));
        $this->assertEquals('fallback', $page->get('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_values_and_falls_back_to_values_from_the_entry()
    {
        $entry = EntryFactory::id('test-entry')->collection('test')->data([
            'foo' => 'entry bar',
            'baz' => 'entry qux',
        ])->create();

        $tree = $this->mock(Tree::class)->shouldReceive('entry')->with('test-entry')->andReturn($entry)->getMock();

        $page = new Page;
        $page->setEntry('test-entry');
        $page->setTree($tree);

        $this->assertInstanceOf(Collection::class, $page->data());
        $this->assertEquals([
            'foo' => 'entry bar',
            'baz' => 'entry qux',
        ], $page->data()->all());
        $this->assertInstanceOf(Collection::class, $page->pageData());
        $this->assertEquals([], $page->pageData()->all());
        $this->assertInstanceOf(Collection::class, $page->values());
        $this->assertEquals([
            'foo' => 'entry bar',
            'baz' => 'entry qux',
        ], $page->values()->all());
        $this->assertEquals('entry bar', $page->value('foo'));
        $this->assertEquals('entry bar', $page->get('foo'));
        $this->assertEquals('entry qux', $page->value('baz'));
        $this->assertEquals('entry qux', $page->get('baz'));
        $this->assertEquals('fallback', $page->get('unknown', 'fallback'));

        $page->setPageData(['foo' => 'page bar']);

        $this->assertInstanceOf(Collection::class, $page->data());
        $this->assertEquals([
            'foo' => 'page bar',
            'baz' => 'entry qux',
        ], $page->data()->all());
        $this->assertInstanceOf(Collection::class, $page->pageData());
        $this->assertEquals(['foo' => 'page bar'], $page->pageData()->all());
        $this->assertInstanceOf(Collection::class, $page->values());
        $this->assertEquals([
            'foo' => 'page bar',
            'baz' => 'entry qux',
        ], $page->values()->all());
        $this->assertEquals('page bar', $page->value('foo'));
        $this->assertEquals('page bar', $page->get('foo'));
        $this->assertEquals('entry qux', $page->value('baz'));
        $this->assertEquals('entry qux', $page->get('baz'));
        $this->assertEquals('fallback', $page->get('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $entry = EntryFactory::id('test-entry')->collection('test')->data([
            'foo' => 'entry bar',
            'baz' => 'entry qux',
        ])->create();

        $tree = $this->mock(Tree::class);
        $tree->shouldReceive('entry')->with('test-entry')->andReturn($entry);
        $tree->shouldReceive('structure')->andReturnNull(); // just make the blueprint method quiet for now.

        $page = new Page;
        $page->setEntry('test-entry');
        $page->setTree($tree);

        $page
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $page->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $page[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $entry = EntryFactory::id('test-entry')->collection('test')->data([
            'foo' => 'entry bar',
            'baz' => 'entry qux',
        ])->create();

        $tree = $this->mock(Tree::class);
        $tree->shouldReceive('entry')->with('test-entry')->andReturn($entry);
        $tree->shouldReceive('structure')->andReturnNull(); // just make the blueprint method quiet for now.

        $page = new Page;
        $page->setEntry('test-entry');
        $page->setTree($tree);

        $this->assertInstanceOf(Arrayable::class, $page);

        collect($arr = $page->toArray())
            ->except(['collection', 'blueprint'])
            ->each(fn ($value, $key) => $this->assertEquals($value, $page->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value, $page[$key]));

        $this->assertEquals($page->collection()->toArray(), $arr['collection']);
        $this->assertEquals($page->blueprint->toArray(), $arr['blueprint']);
    }

    protected function newTree()
    {
        return new class extends Tree
        {
            private $structure;

            public function path()
            {
                //
            }

            public function structure()
            {
                return $this->structure;
            }

            public function setStructure($structure)
            {
                $this->structure = $structure;

                return $this;
            }

            protected function repository()
            {
                //
            }
        };
    }
}
