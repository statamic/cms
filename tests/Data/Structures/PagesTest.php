<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Statamic\Data\Entries\Entry;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Page;
use Statamic\Data\Structures\Pages;
use Statamic\API\Entry as EntryAPI;
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
            ->setParentUri('')
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

        $pages = (new Pages)
            ->setParentUri('/the-parent')
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
        $this->assertCount(2, $pages->uris());
        $this->assertEquals([
            'one' => '/the-parent/one',
            'two' => '/the-parent/two'
        ], $pages->uris()->all());
    }

    /** @test */
    function it_gets_flattened_pages()
    {
        EntryAPI::shouldReceive('find')->with('one')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'one'; }
            });

        EntryAPI::shouldReceive('find')->with('one-one')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'one-one'; }
            });

        EntryAPI::shouldReceive('find')->with('one-two')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'one-two'; }
            });

        EntryAPI::shouldReceive('find')->with('one-two-one')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'one-two-one'; }
            });

        EntryAPI::shouldReceive('find')->with('two')
            ->andReturn(new class extends Entry {
                public function slug($slug = null) { return 'two'; }
            });


        $pages = (new Pages)
            ->setParentUri('/root')
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
            'one' => '/root/one',
            'one-one' => '/root/one/one-one',
            'one-two' => '/root/one/one-two',
            'one-two-one' => '/root/one/one-two/one-two-one',
            'two' => '/root/two',
        ], $pages->flattenedPages()->map->uri()->all());
    }
}
