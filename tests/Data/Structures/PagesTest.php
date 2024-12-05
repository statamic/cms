<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_gets_a_list_of_pages()
    {
        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('id')->andReturn('the-root');

        $pages = (new Pages)
            ->setTree($this->newTree())
            ->setParent($parent)
            ->setPages([
                ['id' => 'one', 'data' => ['foo' => 'bar'], 'children' => [
                    ['id' => 'one-one'],
                    ['id' => 'one-two', 'children' => [
                        ['id' => 'one-two-one'],
                    ]],
                ]],
                ['id' => 'two'],
            ]);

        $list = $pages->all();
        $this->assertInstanceOf(Collection::class, $list);
        $this->assertCount(3, $list);
        $this->assertEveryItemIsInstanceOf(Page::class, $list);
        $this->assertEquals(['the-root', 'one', 'two'], $list->map->id()->all());
        $this->assertEquals(['foo' => 'bar'], $list[1]->pageData()->all());
    }

    #[Test]
    public function it_gets_flattened_pages()
    {
        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('flattenedPages')->andReturn(collect());
        $parent->shouldReceive('id')->andReturn('the-root');

        $pages = (new Pages)
            ->setTree($this->newTree())
            ->setParent($parent)
            ->setRoute('{parent_uri}/{slug}')
            ->setPages([
                ['id' => 'one', 'children' => [
                    ['id' => 'one-one'],
                    ['id' => 'one-two', 'children' => [
                        ['id' => 'one-two-one'],
                    ]],
                ]],
                ['id' => 'two'],
            ]);

        $this->assertEquals([
            'the-root',
            'one',
            'one-one',
            'one-two',
            'one-two-one',
            'two',
        ], $pages->flattenedPages()->map->id()->all());
    }

    protected function newTree()
    {
        return new class extends Tree
        {
            public function path()
            {
                //
            }

            public function structure()
            {
                //
            }

            protected function repository()
            {
                //
            }
        };
    }
}
