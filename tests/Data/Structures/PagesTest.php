<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Statamic\Structures\Tree;
use Tests\TestCase;

class PagesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $dir = __DIR__.'/../../Stache/__fixtures__';
        $stache->store('collections')->directory($dir.'/content/collections');
        $stache->store('entries')->directory($dir.'/content/collections');
    }

    /** @test */
    public function it_gets_a_list_of_pages()
    {
        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('reference')->andReturn('the-root');
        $parent->shouldReceive('flattenedPages')->andReturn(collect());
        $parent->shouldReceive('uri')->andReturn('/root');

        $pages = (new Pages)
            ->setParent($parent)
            ->setPages([
                ['entry' => 'one', 'children' => [
                    ['entry' => 'one-one'],
                    ['entry' => 'one-two', 'children' => [
                        ['entry' => 'one-two-one'],
                    ]],
                ]],
                ['entry' => 'two'],
            ]);

        $list = $pages->all();
        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(3, $list);
        $this->assertEveryItemIsInstanceOf(Page::class, $list);
        $this->assertEquals(['the-root', 'one', 'two'], $list->map->reference()->all());
    }

    /** @test */
    public function it_gets_flattened_pages()
    {
        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('reference')->andReturn('the-root');
        $parent->shouldReceive('flattenedPages')->andReturn(collect());
        $parent->shouldReceive('uri')->andReturn('/root');

        $pages = (new Pages)
            ->setTree(new Tree)
            ->setParent($parent)
            ->setRoute('{parent_uri}/{slug}')
            ->setPages([
                ['entry' => 'one', 'children' => [
                    ['entry' => 'one-one'],
                    ['entry' => 'one-two', 'children' => [
                        ['entry' => 'one-two-one'],
                    ]],
                ]],
                ['entry' => 'two'],
            ]);

        $this->assertEquals([
            'the-root',
            'one',
            'one-one',
            'one-two',
            'one-two-one',
            'two',
        ], $pages->flattenedPages()->map->reference()->all());
    }
}
