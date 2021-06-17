<?php

namespace Tests\Data\Taxonomies;

use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_terms()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('tags')->data([])->save();
        Term::make('c')->taxonomy('tags')->data([])->save();

        $terms = Term::query()->get();
        $this->assertInstanceOf(TermCollection::class, $terms);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $terms);
    }

    /** @test */
    public function it_filters_using_wheres()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'foo'])->save();

        $terms = Term::query()->where('test', 'foo')->get();
        $this->assertEquals(['a', 'c'], $terms->map->slug()->sort()->values()->all());
    }

    /** @test */
    public function it_filters_by_taxonomy()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('categories')->data([])->save();
        Term::make('c')->taxonomy('tags')->data([])->save();

        $terms = Term::query()->where('taxonomy', 'tags')->get();
        $this->assertEquals(['a', 'c'], $terms->map->slug()->sort()->values()->all());
    }

    /** @test */
    public function it_filters_by_multiple_taxonomies()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        Taxonomy::make('colors')->save();
        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('categories')->data([])->save();
        Term::make('c')->taxonomy('colors')->data([])->save();
        Term::make('d')->taxonomy('tags')->data([])->save();

        $terms = Term::query()->whereIn('taxonomy', ['tags', 'categories'])->get();
        $this->assertEquals(['a', 'b', 'd'], $terms->map->slug()->sort()->values()->all());
    }

    /** @test */
    public function it_sorts()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 4])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 2])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 1])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 5])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 3])->save();

        $terms = Term::query()->orderBy('test')->get();
        $this->assertEquals(['c', 'b', 'e', 'a', 'd'], $terms->map->slug()->all());
    }
}
