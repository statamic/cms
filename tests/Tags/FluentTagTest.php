<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Fields\Value;
use Statamic\Tags\Collection\Collection;
use Statamic\Tags\FluentTag;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FluentTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        (new FluentTagCollectionTag)::register();

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
    public function it_augments_to_evaluated_values_by_default_when_returning_single_augmentable()
    {
        $page = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->first() // custom param added to test version of collection tag to return single augmentable
            ->fetch();

        $this->assertIsArray($page);
        $this->assertIsString($page['content']);
    }

    /** @test */
    public function it_can_disable_evaluation_when_returning_single_augmentable()
    {
        $page = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->withoutEvaluation()
            ->first() // custom param added to test version of collection tag to return single augmentable
            ->fetch();

        $this->assertIsArray($page);
        $this->assertInstanceOf(Value::class, $page['content']);
    }

    /** @test */
    public function it_can_disable_augmentation_when_returning_single_augmentable()
    {
        $page = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->withoutAugmentation()
            ->first() // custom param added to test version of collection tag to return single augmentable
            ->fetch();

        $this->assertInstanceOf(Entry::class, $page);
    }

    /** @test */
    public function it_augments_to_evaluated_values_by_default()
    {
        $results = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->fetch();

        $page = $results[0];

        $this->assertIsArray($page);
        $this->assertIsString($page['content']);
    }

    /** @test */
    public function it_can_disable_evaluation()
    {
        $results = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->withoutEvaluation()
            ->fetch();

        $page = $results[0];

        $this->assertIsArray($page);
        $this->assertInstanceOf(Value::class, $page['content']);
    }

    /** @test */
    public function it_can_disable_augmentation()
    {
        $results = FluentTag::make('collection:pages')
            ->sort('slug:desc')->limit(3)
            ->withoutAugmentation()
            ->fetch();

        $page = $results[0];

        $this->assertInstanceOf(Entry::class, $page);
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

class FluentTagCollectionTag extends Collection
{
    protected static $handle = 'collection';

    public function __call($method, $args)
    {
        $results = parent::__call($method, $args);

        if ($this->params->bool('first')) {
            return $results->first();
        }

        return $results;
    }
}
