<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
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
        Taxonomy::make('colors')->save();
        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('categories')->data([])->save();
        Term::make('c')->taxonomy('colors')->data([])->save();
        Term::make('d')->taxonomy('tags')->data([])->save();

        $terms = Term::query()->where('taxonomy', 'tags')->get();
        $this->assertEquals(['a', 'd'], $terms->map->slug()->sort()->values()->all());

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

    /** @test */
    public function it_filters_usage_in_collections()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('cats')->save();

        Collection::make('blog')->taxonomies(['tags', 'cats'])->save();
        Collection::make('news')->taxonomies(['tags', 'cats'])->save();

        EntryFactory::collection('blog')->data(['tags' => ['a'], 'cats' => ['f']])->create();
        EntryFactory::collection('blog')->data(['tags' => ['c'], 'cats' => ['g']])->create();
        EntryFactory::collection('news')->data(['tags' => ['a'], 'cats' => ['f']])->create();
        EntryFactory::collection('news')->data(['tags' => ['b'], 'cats' => ['h']])->create();

        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('tags')->data([])->save();
        Term::make('c')->taxonomy('tags')->data([])->save();
        Term::make('d')->taxonomy('tags')->data([])->save();
        Term::make('e')->taxonomy('cats')->data([])->save();
        Term::make('f')->taxonomy('cats')->data([])->save();
        Term::make('g')->taxonomy('cats')->data([])->save();
        Term::make('h')->taxonomy('cats')->data([])->save();

        $this->assertEquals(['cats::f', 'cats::g', 'tags::a', 'tags::c'],
            Term::query()
                ->where('collection', 'blog')
                ->get()->map->id()->sort()->values()->all()
        );

        $this->assertEquals(['tags::a', 'tags::c'],
            Term::query()
                ->where('collection', 'blog')
                ->where('taxonomy', 'tags')
                ->get()->map->id()->sort()->values()->all()
        );

        $this->assertEquals(['cats::f', 'cats::h', 'tags::a', 'tags::b'],
            Term::query()
                ->where('collection', 'news')
                ->get()->map->id()->sort()->values()->all()
        );

        $this->assertEquals(['tags::a', 'tags::b'],
            Term::query()
                ->where('collection', 'news')
                ->where('taxonomy', 'tags')
                ->get()->map->id()->sort()->values()->all()
        );

        $this->assertEquals(['cats::f', 'cats::g', 'cats::h', 'tags::a', 'tags::b', 'tags::c'],
            Term::query()
                ->whereIn('collection', ['blog', 'news'])
                ->get()->map->id()->sort()->values()->all()
        );

        $this->assertEquals(['tags::a', 'tags::b', 'tags::c'],
            Term::query()
                ->whereIn('collection', ['blog', 'news'])
                ->where('taxonomy', 'tags')
                ->get()->map->id()->sort()->values()->all()
        );
    }
}
