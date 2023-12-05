<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Parse;
use Statamic\Facades\Search;
use Statamic\Search\QueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_results()
    {
        $entryA = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();
        $entryB = EntryFactory::id('b')->collection('test')->data(['title' => 'entry b'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entryA, $entryB]));

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->get('/whatever?q=foo'); // just a way to get a query param into the request(). the url is irrelevant.

        $this->assertEquals(
            '<entry a><entry b>',
            $this->tag(
                '{{ search:results }}{{ if no_results }}No results{{ else }}<{{ title }}>{{ /if }}{{ /search:results }}'
            )
        );
    }

    /** @test */
    public function it_outputs_results_using_alias()
    {
        $entryA = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();
        $entryB = EntryFactory::id('b')->collection('test')->data(['title' => 'entry b'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entryA, $entryB]));

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->get('/whatever?q=foo'); // just a way to get a query param into the request(). the url is irrelevant.

        $this->assertEquals(
            '<entry a><entry b>',
            $this->tag(
                '{{ search:results as="stuff" }}{{ if no_results }}No results{{ else }}{{ stuff }}<{{ title }}>{{ /stuff }}{{ /if }}{{ /search:results }}'
            )
        );
    }

    /** @test */
    public function it_outputs_results_where_there_are_no_statuses()
    {
        Storage::fake('images');
        AssetContainer::make('images')->disk('images')->save();

        $entryA = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();
        $entryB = EntryFactory::id('b')->collection('test')->data(['title' => 'entry b'])->create();
        $assetA = tap(Asset::make()->container('images')->path('a.jpg')->data(['title' => 'asset a']))->save();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entryA, $entryB, $assetA]));

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->get('/whatever?q=foo'); // just a way to get a query param into the request(). the url is irrelevant.

        $this->assertEquals(
            '<entry a><entry b><asset a>',
            $this->tag(
                '{{ search:results }}{{ if no_results }}No results{{ else }}<{{ title }}>{{ /if }}{{ /search:results }}'
            )
        );
    }
}
