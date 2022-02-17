<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades;
use Statamic\Facades\Entry;
use Statamic\Fields\Values;
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
    public function it_converts_nested_arrays_to_values_classes()
    {
        $this->mockTagThatReturns(collect([
            ['slug' => 'one', 'content' => '# one'],
            ['slug' => 'two', 'content' => '# two'],
            Entry::find('three'),
        ]));

        $results = FluentTag::make('test')->fetch();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(3, $results);
        $this->assertEveryItemIsInstanceOf(Values::class, $results);

        $items = [];
        foreach ($results as $result) {
            $items[] = trim($result['content']);
            $items[] = trim($result->content);
        }
        $this->assertEquals([
            '# one',
            '# one',
            '# two',
            '# two',
            '<h1>three</h1>',
            '<h1>three</h1>',
        ], $items);
    }

    /** @test */
    public function it_converts_a_single_array_to_a_values_class()
    {
        $this->mockTagThatReturns([
            'slug' => 'one',
            'content' => '# one',
        ]);

        $results = FluentTag::make('test')->fetch();

        $this->assertInstanceOf(Values::class, $results);
        $this->assertEquals('one', $results['slug']);
        $this->assertEquals('one', $results->slug);
        $this->assertEquals('# one', $results['content']);
        $this->assertEquals('# one', $results->content);
    }

    /** @test */
    public function it_converts_a_single_augmentable_to_a_values_class()
    {
        $this->mockTagThatReturns(Entry::find('one'));

        $results = FluentTag::make('test')->fetch();

        $this->assertInstanceOf(Values::class, $results);
        $this->assertEquals('one', $results['slug']);
        $this->assertEquals('one', $results->slug);
        $this->assertEquals('<h1>one</h1>', trim($results['content']));
        $this->assertEquals('<h1>one</h1>', trim($results->content));
    }

    /** @test */
    public function it_can_disable_converting_nested_array_to_values_classes()
    {
        $this->mockTagThatReturns($rawTagReturnValue = collect([
            ['slug' => 'one', 'content' => '# one'],
            ['slug' => 'two', 'content' => '# two'],
            Entry::find('three'),
        ]));

        $results = FluentTag::make('test')->withoutConvertingValues()->fetch();

        $this->assertSame($rawTagReturnValue, $results);
    }

    /** @test */
    public function it_can_disable_converting_a_single_array_to_a_values_class()
    {
        $this->mockTagThatReturns($rawTagReturnValue = [
            'slug' => 'one',
            'content' => '# one',
        ]);

        $results = FluentTag::make('test')->withoutConvertingValues()->fetch();

        $this->assertSame($rawTagReturnValue, $results);
    }

    /** @test */
    public function it_can_disable_converting_a_single_augmentable_to_a_values_class()
    {
        $this->mockTagThatReturns($rawTagReturnValue = Entry::find('one'));

        $results = FluentTag::make('test')->withoutConvertingValues()->fetch();

        $this->assertSame($rawTagReturnValue, $results);
    }

    /** @test */
    public function it_augments_by_default()
    {
        $this->mockTagThatReturns(collect([
            Entry::find('one'),
            Entry::find('two'),
            Entry::find('three'),
        ]));

        $slugs = [];

        foreach (FluentTag::make('test') as $entry) {
            $this->assertInstanceOf(Values::class, $entry);
            $this->assertIsString($entry['id']);
            $this->assertIsString($entry['content']);
            $slugs[] = trim($entry['content']);
        }

        $expected = ['<h1>one</h1>', '<h1>two</h1>', '<h1>three</h1>'];

        $this->assertEquals($expected, $slugs);
    }

    /** @test */
    public function it_can_disable_augmentation()
    {
        $this->mockTagThatReturns(collect([
            Entry::find('one'),
            Entry::find('two'),
            Entry::find('three'),
        ]));

        $slugs = [];

        foreach (FluentTag::make('test')->withoutAugmentation() as $entry) {
            $this->assertInstanceOf(EntryContract::class, $entry);
            $slugs[] = $entry->get('content');
        }

        $expected = ['# one', '# two', '# three'];

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
    public function it_allows_array_access_on_nested_arrays()
    {
        $this->mockTagThatReturns(collect([
            ['slug' => 'one'],
            ['slug' => 'two'],
            ['slug' => 'three'],
        ]));

        $result = FluentTag::make('test');

        $this->assertInstanceOf(FluentTag::class, $result);
        $this->assertEquals('one', $result[0]['slug']);
        $this->assertEquals('two', $result[1]['slug']);
        $this->assertEquals('three', $result[2]['slug']);

        $this->assertEquals('one', $result[0]->slug);
        $this->assertEquals('two', $result[1]->slug);
        $this->assertEquals('three', $result[2]->slug);
    }

    /** @test */
    public function it_casts_string_results_to_string()
    {
        $this->mockTagThatReturns('/fanny-packs');

        $result = FluentTag::make('link')->to('fanny-packs');

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
