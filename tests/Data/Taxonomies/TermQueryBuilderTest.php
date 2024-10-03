<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Query\Scopes\Scope;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_terms()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Term::make('a')->taxonomy('tags')->dataForLocale('en', ['title' => 'Alfa'])->dataForLocale('fr', ['title' => 'Le Alfa'])->save();
        Term::make('b')->taxonomy('tags')->dataForLocale('en', ['title' => 'Bravo'])->dataForLocale('fr', ['title' => 'Le Bravo'])->save();
        Term::make('c')->taxonomy('tags')->dataForLocale('en', ['title' => 'Charlie'])->save(); // intentionally no french translation

        $terms = Term::query()->get();
        $this->assertInstanceOf(TermCollection::class, $terms);
        $this->assertCount(6, $terms);
        $this->assertEveryItemIsInstanceOf(LocalizedTerm::class, $terms);
        $this->assertEquals([
            'en Alfa',
            'fr Le Alfa',
            'en Bravo',
            'fr Le Bravo',
            'en Charlie',
            'fr Charlie', // term still exists in fr site, just not translated
        ], $terms
            ->sortBy->slug()
            ->map(fn ($t) => $t->locale().' '.$t->title())
            ->values()->all());
    }

    #[Test]
    public function it_filters_using_wheres()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'foo'])->save();

        $terms = Term::query()->where('test', 'foo')->get();
        $this->assertEquals(['a', 'c'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_or_wheres()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();

        $terms = Term::query()->where('test', 'foo')->orWhere('test', 'bar')->get();
        $this->assertEquals(['a', 'd', 'b'], $terms->map->slug()->values()->all());
    }

    #[Test]
    public function it_filters_using_or_where_ins()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();

        $terms = Term::query()->whereIn('test', ['foo', 'bar'])->orWhereIn('test', ['foo', 'raz'])->get();

        $this->assertEquals(['a', 'b', 'd', 'e'], $terms->map->slug()->values()->all());
    }

    #[Test]
    public function it_filters_using_or_where_not_ins()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();
        Term::make('f')->taxonomy('tags')->data(['test' => 'taz'])->save();

        $terms = Term::query()->whereNotIn('test', ['foo', 'bar'])->orWhereNotIn('test', ['foo', 'raz'])->get();

        $this->assertEquals(['c', 'f'], $terms->map->slug()->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_wheres()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();

        $terms = Term::query()
            ->where(function ($query) {
                $query->where('test', 'foo');
            })
            ->orWhere(function ($query) {
                $query->where('test', 'baz');
            })
            ->orWhere('test', 'raz')
            ->get();

        $this->assertCount(4, $terms);
        $this->assertEquals(['a', 'c', 'd', 'e'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_where_ins()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();
        Term::make('f')->taxonomy('tags')->data(['test' => 'chaz'])->save();

        $terms = Term::query()
            ->where(function ($query) {
                $query->where('test', 'foo');
            })
            ->orWhere(function ($query) {
                $query->whereIn('test', ['baz', 'raz']);
            })
            ->orWhere('test', 'chaz')
            ->get();

        $this->assertCount(5, $terms);
        $this->assertEquals(['a', 'c', 'd', 'e', 'f'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function it_filters_using_nested_where_not_ins()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('b')->taxonomy('tags')->data(['test' => 'bar'])->save();
        Term::make('c')->taxonomy('tags')->data(['test' => 'baz'])->save();
        Term::make('d')->taxonomy('tags')->data(['test' => 'foo'])->save();
        Term::make('e')->taxonomy('tags')->data(['test' => 'raz'])->save();

        $terms = Term::query()
            ->where('test', 'foo')
            ->orWhere(function ($query) {
                $query->whereNotIn('test', ['baz', 'raz']);
            })
            ->get();

        $this->assertCount(3, $terms);
        $this->assertEquals(['a', 'b', 'd'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
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

    #[Test]
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

        $terms = Term::query()->orderByDesc('test')->get();
        $this->assertEquals(['d', 'a', 'e', 'b', 'c'], $terms->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_where_column()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['title' => 'Post 1', 'other_title' => 'Not Post 1'])->save();
        Term::make('b')->taxonomy('tags')->data(['title' => 'Post 2', 'other_title' => 'Not Post 2'])->save();
        Term::make('c')->taxonomy('tags')->data(['title' => 'Post 3', 'other_title' => 'Post 3'])->save();
        Term::make('d')->taxonomy('tags')->data(['title' => 'Post 4', 'other_title' => 'Post 4'])->save();
        Term::make('e')->taxonomy('tags')->data(['title' => 'Post 5', 'other_title' => 'Not Post 5'])->save();

        $terms = Term::query()->whereColumn('title', 'other_title')->get();

        $this->assertCount(2, $terms);
        $this->assertEquals(['c', 'd'], $terms->map->slug()->all());

        $terms = Term::query()->whereColumn('title', '!=', 'other_title')->get();

        $this->assertCount(3, $terms);
        $this->assertEquals(['a', 'b', 'e'], $terms->map->slug()->all());
    }

    #[Test]
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

    #[Test]
    public function it_substitutes_terms_by_id()
    {
        Taxonomy::make('tags')->save();
        Term::make('tag-1')->dataForLocale('en', [])->taxonomy('tags')->save();
        Term::make('tag-2')->dataForLocale('en', [])->taxonomy('tags')->save();
        Term::make('tag-3')->dataForLocale('en', [])->taxonomy('tags')->save();

        $substitute = Term::make('tag-2')->taxonomy('tags')->dataForLocale('en', ['title' => 'Replaced'])->in('en');

        $found = Term::query()->where('id', 'tags::tag-2')->first();
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substitute);

        Term::substitute($substitute);

        $found = Term::query()->where('id', 'tags::tag-2')->first();
        $this->assertNotNull($found);
        $this->assertSame($found, $substitute);
    }

    #[Test]
    public function it_substitutes_terms_by_uri()
    {
        Taxonomy::make('tags')->save();
        Term::make('tag-1')->taxonomy('tags')->dataForLocale('en', [])->save();
        Term::make('tag-2')->taxonomy('tags')->dataForLocale('en', [])->save();
        Term::make('tag-3')->taxonomy('tags')->dataForLocale('en', [])->save();

        $substitute = Term::make('tag-2')->slug('replaced-tag-2')->taxonomy('tags')->dataForLocale('en', []);

        $found = Term::findByUri('/tags/tag-2');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substitute);

        $this->assertNull(Term::findByUri('/tags/replaced-tag-2'));

        Term::substitute($substitute);

        $found = Term::findByUri('/tags/replaced-tag-2');
        $this->assertNotNull($found);
        $this->assertSame($found, $substitute);
    }

    #[Test]
    public function it_substitutes_terms_by_uri_and_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Term::make('tag-1')->slug('tag-1')->taxonomy('tags')
            ->dataForlocale('en', [])
            ->dataForlocale('fr', [])
            ->save();
        Term::make('tag-2')->slug('tag-2')->taxonomy('tags')
            ->dataForlocale('en', [])
            ->dataForlocale('fr', [])
            ->save();
        Term::make('tag-3')->slug('tag-3')->taxonomy('tags')
            ->dataForlocale('en', [])
            ->dataForlocale('fr', [])
            ->save();

        $substitute = Term::make('tag-2')
            ->slug('replaced-tag-2')
            ->taxonomy('tags')
            ->dataForLocale('en', [])
            ->dataForLocale('fr', []);
        $substituteEn = $substitute->in('en');
        $substituteFr = $substitute->in('fr');

        $found = Term::findByUri('/tags/tag-2');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteEn);

        $found = Term::findByUri('/tags/tag-2', 'en');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteEn);

        $found = Term::findByUri('/tags/tag-2', 'fr');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteFr);

        $this->assertNull(Term::findByUri('/tags/replaced-tag-2'));
        $this->assertNull(Term::findByUri('/tags/replaced-tag-2', 'en'));
        $this->assertNull(Term::findByUri('/tags/replaced-tag-2', 'fr'));

        Term::substitute($substituteEn);
        Term::substitute($substituteFr);

        $found = Term::findByUri('/tags/replaced-tag-2');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteEn);

        $found = Term::findByUri('/tags/replaced-tag-2', 'en');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteEn);

        $found = Term::findByUri('/tags/replaced-tag-2', 'fr');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteFr);
    }

    #[Test]
    public function entries_are_found_using_where_date()
    {
        $this->createWhereDateTestTerms();

        $entries = Term::query()->whereDate('test_date', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Term::query()->whereDate('test_date', 1637000264)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Term::query()->whereDate('test_date', '>=', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_month()
    {
        $this->createWhereDateTestTerms();

        $entries = Term::query()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Term::query()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_day()
    {
        $this->createWhereDateTestTerms();

        $entries = Term::query()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Term::query()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 2', 'Post 4'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_year()
    {
        $this->createWhereDateTestTerms();

        $entries = Term::query()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Term::query()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_time()
    {
        $this->createWhereDateTestTerms();

        $entries = Term::query()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());

        $entries = Term::query()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 4'], $entries->map->title->all());
    }

    private function createWhereDateTestTerms()
    {
        $blueprint = Blueprint::makeFromFields(['test_date' => ['type' => 'date', 'time_enabled' => true]]);
        Blueprint::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));

        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['title' => 'Post 1', 'test_date' => '2021-11-15 20:31:04'])->save();
        Term::make('b')->taxonomy('tags')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->save();
        Term::make('c')->taxonomy('tags')->data(['title' => 'Post 3', 'test_date' => '2021-11-15 00:00:00'])->save();
        Term::make('d')->taxonomy('tags')->data(['title' => 'Post 4', 'test_date' => '2020-09-13 14:44:24'])->save();
        Term::make('e')->taxonomy('tags')->data(['title' => 'Post 5', 'test_date' => null])->save();
    }

    #[Test]
    public function terms_are_found_using_where_json_contains()
    {
        Taxonomy::make('tags')->save();
        Term::make('1')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Term::make('2')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Term::make('3')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Term::make('4')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Term::make('5')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $entries = Term::query()->whereJsonContains('test_taxonomy', ['taxonomy-1', 'taxonomy-5'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['1', '3', '5'], $entries->map->slug()->all());

        $entries = Term::query()->whereJsonContains('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['1', '3'], $entries->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_where_json_doesnt_contain()
    {
        Taxonomy::make('tags')->save();
        Term::make('1')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Term::make('2')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Term::make('3')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Term::make('4')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Term::make('5')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $entries = Term::query()->whereJsonDoesntContain('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['2', '4', '5'], $entries->map->slug()->all());

        $entries = Term::query()->whereJsonDoesntContain('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['2', '4', '5'], $entries->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_or_where_json_contains()
    {
        Taxonomy::make('tags')->save();
        Term::make('1')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Term::make('2')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Term::make('3')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Term::make('4')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Term::make('5')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $entries = Term::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonContains('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['1', '3', '5'], $entries->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_or_where_json_doesnt_contain()
    {
        Taxonomy::make('tags')->save();
        Term::make('1')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Term::make('2')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Term::make('3')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Term::make('4')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Term::make('5')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $entries = Term::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntContain('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['1', '3', '2', '4'], $entries->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_where_json_length()
    {
        Taxonomy::make('tags')->save();
        Term::make('1')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Term::make('2')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Term::make('3')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Term::make('4')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4', 'taxonomy-5']])->save();
        Term::make('5')->taxonomy('tags')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $entries = Term::query()->whereJsonLength('test_taxonomy', 1)->orWhereJsonLength('test_taxonomy', 3)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['2', '5', '4'], $entries->map->slug()->all());
    }

    #[Test]
    public function terms_are_found_using_scopes()
    {
        CustomScope::register();
        Term::allowQueryScope(CustomScope::class);
        Term::allowQueryScope(CustomScope::class, 'whereCustom');

        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data(['title' => 'Post 1'])->save();
        Term::make('b')->taxonomy('tags')->data(['title' => 'Post 2'])->save();

        $this->assertCount(1, Term::query()->customScope(['title' => 'Post 1'])->get());
        $this->assertCount(1, Term::query()->whereCustom(['title' => 'Post 1'])->get());
    }

    #[Test]
    public function terms_are_found_using_offset()
    {
        Taxonomy::make('tags')->save();
        Term::make('a')->taxonomy('tags')->data([])->save();
        Term::make('b')->taxonomy('tags')->data([])->save();
        Term::make('c')->taxonomy('tags')->data([])->save();

        $terms = Term::query()->get();
        $this->assertEquals(['a', 'b', 'c'], $terms->map->slug()->all());

        $terms = Term::query()->offset(1)->get();
        $this->assertEquals(['b', 'c'], $terms->map->slug()->all());
    }
}

class CustomScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', $params['title']);
    }
}
