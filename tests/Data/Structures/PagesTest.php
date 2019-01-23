<?php

namespace Tests\Data\Structures;

use Mockery;
use Tests\TestCase;
use Statamic\Data\Entries\Entry;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Page;
use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Structures\Pages;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class PagesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->make('stache')->withoutBooting(function ($stache) {
            $dir = __DIR__.'/../../Stache/__fixtures__';
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
        });
    }

    /** @test */
    function it_gets_a_list_of_pages()
    {
        $pages = (new Pages)
            ->setRoute('irrelevant')
            ->setTree([
                ['entry' => 'one', 'children' => [
                    ['entry' => 'one-one'],
                    ['entry' => 'one-two', 'children' => [
                        ['entry' => 'one-two-one']
                    ]],
                ]],
                ['entry' => 'two']
            ]);

        $list = $pages->all();
        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(2, $list);
        $this->assertEveryItemIsInstanceOf(Page::class, $list);
        $this->assertEquals(['one', 'two'], $list->keys()->all());
    }

    /** @test */
    function it_gets_uris_of_pages()
    {
        $entryOne = new class extends Entry {
            public function slug($slug = null) { return 'one'; }
        };
        $entryTwo = new class extends Entry {
            public function slug($slug = null) { return 'two'; }
        };
        EntryAPI::shouldReceive('find')->with('one')->andReturn($entryOne);
        EntryAPI::shouldReceive('find')->with('two')->andReturn($entryTwo);

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('reference')->andReturn('root');
        $parent->shouldReceive('uri')->andReturn('/the-parent');

        $pages = (new Pages)
            ->setParent($parent)
            ->setRoute('{parent_uri}/{slug}')
            ->setTree([
                ['entry' => 'one', 'children' => [
                    ['entry' => 'one-one'],
                    ['entry' => 'one-two', 'children' => [
                        ['entry' => 'one-two-one']
                    ]],
                ]],
                ['entry' => 'two']
            ]);

        $this->assertInstanceOf(Collection::class, $pages->uris());
        $this->assertCount(3, $pages->uris());
        $this->assertEquals([
            'root' => '/the-parent',
            'one' => '/the-parent/one',
            'two' => '/the-parent/two'
        ], $pages->uris()->all());
    }

    /** @test */
    function it_gets_flattened_pages()
    {
        EntryAPI::shouldReceive('find')->with('one')
            ->andReturn(new class extends Entry {
                public function id($id = null) { return 'one'; }
                public function slug($slug = null) { return 'one'; }
            });

        EntryAPI::shouldReceive('find')->with('one-one')
            ->andReturn(new class extends Entry {
                public function id($id = null) { return 'one-one'; }
                public function slug($slug = null) { return 'one-one'; }
            });

        EntryAPI::shouldReceive('find')->with('one-two')
            ->andReturn(new class extends Entry {
                public function id($id = null) { return 'one-two'; }
                public function slug($slug = null) { return 'one-two'; }
            });

        EntryAPI::shouldReceive('find')->with('one-two-one')
            ->andReturn(new class extends Entry {
                public function id($id = null) { return 'one-two-one'; }
                public function slug($slug = null) { return 'one-two-one'; }
            });

        EntryAPI::shouldReceive('find')->with('two')
            ->andReturn(new class extends Entry {
                public function id($id = null) { return 'two'; }
                public function slug($slug = null) { return 'two'; }
            });

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('reference')->andReturn('the-root');
        $parent->shouldReceive('flattenedPages')->andReturn(collect());
        $parent->shouldReceive('uri')->andReturn('/root');

        $pages = (new Pages)
            ->setParent($parent)
            ->setRoute('{parent_uri}/{slug}')
            ->setTree([
                ['entry' => 'one', 'children' => [
                    ['entry' => 'one-one'],
                    ['entry' => 'one-two', 'children' => [
                        ['entry' => 'one-two-one']
                    ]],
                ]],
                ['entry' => 'two']
            ]);

        $this->assertEquals([
            'the-root' => '/root',
            'one' => '/root/one',
            'one-one' => '/root/one/one-one',
            'one-two' => '/root/one/one-two',
            'one-two-one' => '/root/one/one-two/one-two-one',
            'two' => '/root/two',
        ], $pages->flattenedPages()->map->uri()->all());
    }
}
