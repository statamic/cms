<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Mockery;
use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Tags\FluentTag;
use Statamic\Tags\Loader;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Parser;
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

    /**
     * @test
     * @dataProvider fluentTagProvider
     **/
    public function it_handles_params_fluently($usedTag, $expectedTagName, $expectedTag, $expectedTagMethod, $expectedClassMethod)
    {
        $tag = Mockery::mock(Tags::class);
        $tag->shouldReceive($expectedClassMethod)->andReturn('tag return value');

        $this->mock(Loader::class)
            ->shouldReceive('load')
            ->withArgs(function ($arg1, $arg2) use ($expectedTag, $expectedTagName, $expectedTagMethod) {
                return $arg1 === $expectedTagName
                    && is_array($arg2)
                    && $arg2['parser'] instanceof Parser
                    && Arr::except($arg2, 'parser') === [
                        'params' => [
                            'sort' => 'slug:desc',
                            'limit' => 3,
                            'title:contains' => 'chewy',
                            'slug:contains' => 'han',
                            'description:contains' => 'luke',
                        ],
                        'content' => '',
                        'context' => [],
                        'tag' => $expectedTag,
                        'tag_method' => $expectedTagMethod,
                    ];
            })
            ->once()
            ->andReturn($tag);

        $fluentTag = FluentTag::make($usedTag)
            ->sort('slug:desc')
            ->limit(3)
            ->param('title:contains', 'chewy')
            ->params([
                'slug:contains' => 'han',
                'description:contains' => 'luke',
            ]);

        $this->assertInstanceOf(FluentTag::class, $fluentTag);

        $this->assertEquals('tag return value', $fluentTag->fetch());
    }

    public function fluentTagProvider()
    {
        return [
            'foo' => ['foo', 'foo', 'foo:index', 'index', 'index'],
            'foo:bar' => ['foo:bar', 'foo', 'foo:bar', 'bar', 'bar'],
            'foo:bar_baz' => ['foo:bar_baz', 'foo', 'foo:bar_baz', 'bar_baz', 'barBaz'],
        ];
    }

    /** @test */
    public function it_can_iterate_over_tag_results()
    {
        $this->mockTagThatReturns(collect([
            ['slug' => 'one'],
            ['slug' => 'two'],
            ['slug' => 'three'],
            ['slug' => 'four'],
            ['slug' => 'five'],
        ]));

        $slugs = [];

        foreach (FluentTag::make('test') as $page) {
            $slugs[] = $page['slug'];
        }

        $expected = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_allows_array_access()
    {
        $this->mockTagThatReturns([
            'foo' => 'bar',
        ]);

        $result = FluentTag::make('test');

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('bar', $result->foo);
    }

    /** @test */
    public function it_casts_string_results_to_string()
    {
        $this->mockTagThatReturns('/fanny-packs');

        $result = FluentTag::make('link');

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertEquals('/fanny-packs', (string) $result);
    }

    private function mockTagThatReturns($return)
    {
        $tag = Mockery::mock(Tags::class);
        $tag->shouldReceive('index')->once()->andReturn($return);
        $this->mock(Loader::class)->shouldReceive('load')->andReturn($tag);
    }
}
