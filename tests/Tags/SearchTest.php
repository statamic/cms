<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\Search;
use Statamic\Search\QueryBuilder;
use Statamic\Search\Typesense\Index as TypesenseIndex;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data, trusted: true);
    }

    #[Test]
    public function it_outputs_results()
    {
        $entryA = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();
        $entryB = EntryFactory::id('b')->collection('test')->data(['title' => 'entry b'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'naturalLanguage', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entryA, $entryB]));
        $builder->shouldReceive('naturalLanguageParams')->andReturn([]);

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->get('/whatever?q=foo'); // just a way to get a query param into the request(). the url is irrelevant.

        $this->assertEquals(
            '<entry a><entry b>',
            $this->tag(
                '{{ search:results }}{{ if no_results }}No results{{ else }}<{{ title }}>{{ /if }}{{ /search:results }}'
            )
        );
    }

    #[Test]
    public function it_outputs_results_using_alias()
    {
        $entryA = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();
        $entryB = EntryFactory::id('b')->collection('test')->data(['title' => 'entry b'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'naturalLanguage', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entryA, $entryB]));
        $builder->shouldReceive('naturalLanguageParams')->andReturn([]);

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->get('/whatever?q=foo'); // just a way to get a query param into the request(). the url is irrelevant.

        $this->assertEquals(
            '<entry a><entry b>',
            $this->tag(
                '{{ search:results as="stuff" }}{{ if no_results }}No results{{ else }}{{ stuff }}<{{ title }}>{{ /stuff }}{{ /if }}{{ /search:results }}'
            )
        );
    }

    #[Test]
    public function it_enables_natural_language_search_and_exposes_generated_parameters()
    {
        $entry = EntryFactory::id('a')->collection('test')->data(['title' => 'entry a'])->create();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('ensureExists', 'search', 'withData', 'limit', 'offset', 'where')->andReturnSelf();
        $builder->shouldReceive('naturalLanguage')->with(true)->once()->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$entry]));
        $builder->shouldReceive('naturalLanguageParams')->andReturn([
            'q' => 'shirts',
            'filter_by' => 'color:=red',
        ]);

        Search::shouldReceive('index')->with(null)->once()->andReturn($builder);

        $this->assertEquals(
            'shirts<entry a>',
            $this->tag(
                '{{ search:results for="show me red shirts" natural_language="true" as="stuff" }}'.
                '{{ natural_language:q }}{{ stuff }}<{{ title }}>{{ /stuff }}'.
                '{{ /search:results }}'
            )
        );
    }

    #[Test]
    public function it_outputs_a_typesense_scoped_search_key()
    {
        $index = $this->mock(TypesenseIndex::class);
        $index
            ->shouldReceive('scopedSearchKey')
            ->with(['filter_by' => 'type:=entry'], 60)
            ->once()
            ->andReturn('scoped-key');

        Search::shouldReceive('index')->with('blog')->once()->andReturn($index);

        $this->assertEquals(
            'scoped-key',
            $this->tag(
                '{{ search:scoped_key index="blog" expires="60" filter_by="type:=entry" }}'
            )
        );
    }
}
