<?php

namespace Tests\Search\Searchables;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Search\Searchables\Terms;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_finds_terms_from_references()
    {
        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Term::make('alfa')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();
        Term::make('bravo')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();
        Term::make('charlie')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();

        $found = (new Terms)->find([
            'tags::alfa::en',
            'tags::alfa::fr',
            'tags::bravo::fr',
        ]);

        $this->assertCount(3, $found);
        $this->assertEquals([
            'term::tags::alfa::en',
            'term::tags::alfa::fr',
            // 'term::tags::bravo::en exists, but shouldn't be returned since it's not in the references
            'term::tags::bravo::fr',
        ], $found->map->reference()->all());
    }

    #[Test]
    #[DataProvider('termsProvider')]
    public function it_gets_terms($locale, $config, $expected)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Taxonomy::make('categories')->sites(['en'])->save();

        Term::make('alfa')->taxonomy('tags')->data([])->save();
        Term::make('bravo')->taxonomy('tags')->data([])->save();
        Term::make('yankee')->taxonomy('categories')->data([])->save();
        Term::make('zulu')->taxonomy('categories')->data([])->save();

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected entries.
        $this->assertEquals($expected, $provider->provide()->map->reference()->all());

        // Check if the entries are contained by the provider or not.
        foreach (Term::all() as $term) {
            $this->assertEquals(
                $shouldBeIn = in_array($term->reference(), $expected),
                $provider->contains($term),
                "Term {$term->reference()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function termsProvider()
    {
        return [
            'all' => [
                null,
                ['searchables' => 'all'],
                [
                    'term::tags::alfa::en',
                    'term::tags::alfa::fr',
                    'term::tags::bravo::en',
                    'term::tags::bravo::fr',
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],
            'all taxonomies' => [
                null,
                ['searchables' => ['taxonomy:*']],
                [
                    'term::tags::alfa::en',
                    'term::tags::alfa::fr',
                    'term::tags::bravo::en',
                    'term::tags::bravo::fr',
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],
            'tags' => [
                null,
                ['searchables' => ['taxonomy:tags']],
                [
                    'term::tags::alfa::en',
                    'term::tags::alfa::fr',
                    'term::tags::bravo::en',
                    'term::tags::bravo::fr',
                ],
            ],
            'categories' => [
                null,
                ['searchables' => ['taxonomy:categories']],
                [
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],

            'all, english' => [
                'en',
                ['searchables' => 'all'],
                [
                    'term::tags::alfa::en',
                    'term::tags::bravo::en',
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],
            'all taxonomies, english' => [
                'en',
                ['searchables' => ['taxonomy:*']],
                [
                    'term::tags::alfa::en',
                    'term::tags::bravo::en',
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],
            'tags, english' => [
                'en',
                ['searchables' => ['taxonomy:tags']],
                [
                    'term::tags::alfa::en',
                    'term::tags::bravo::en',
                ],
            ],
            'categories, english' => [
                'en',
                ['searchables' => ['taxonomy:categories']],
                [
                    'term::categories::yankee::en',
                    'term::categories::zulu::en',
                ],
            ],

            'all, french' => [
                'fr',
                ['searchables' => 'all'],
                [
                    'term::tags::alfa::fr',
                    'term::tags::bravo::fr',
                ],
            ],
            'all taxonomies, french' => [
                'fr',
                ['searchables' => ['taxonomy:*']],
                [
                    'term::tags::alfa::fr',
                    'term::tags::bravo::fr',
                ],
            ],
            'tags, french' => [
                'fr',
                ['searchables' => ['taxonomy:tags']],
                [
                    'term::tags::alfa::fr',
                    'term::tags::bravo::fr',
                ],
            ],
            'categories, french' => [
                'fr',
                ['searchables' => ['taxonomy:categories']],
                [],
            ],
        ];
    }

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        Taxonomy::make('tags')->sites(['en'])->save();
        $a = tap(Term::make('a')->taxonomy('tags')->dataForLocale('en', []))->save();
        $b = tap(Term::make('b')->taxonomy('tags')->dataForLocale('en', ['is_searchable' => false]))->save();
        $c = tap(Term::make('c')->taxonomy('tags')->dataForLocale('en', ['is_searchable' => true]))->save();
        $d = tap(Term::make('d')->taxonomy('tags')->dataForLocale('en', []))->save();

        $provider = $this->makeProvider(null, [
            'searchables' => 'all',
            'filter' => $filter,
        ]);

        $this->assertEquals(['a', 'c', 'd'], $provider->provide()->map->slug()->all());

        $this->assertTrue($provider->contains($a->in('en')));
        $this->assertFalse($provider->contains($b->in('en')));
        $this->assertTrue($provider->contains($c->in('en')));
        $this->assertTrue($provider->contains($d->in('en')));
    }

    public static function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableTermsFilter::class],
            'closure' => [
                function ($entry) {
                    return $entry->get('is_searchable') !== false;
                },
            ],
        ];
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new Terms)->setIndex($index)->setKeys($keys);
    }

    private function makeIndex($locale, $config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);
        $index->shouldReceive('locale')->andReturn($locale);

        return $index;
    }

    private function normalizeSearchableKeys($keys)
    {
        // a bit of duplicated implementation logic.
        // but it makes the test look more like the real thing.
        return collect($keys === 'all' ? ['*'] : $keys)
            ->map(fn ($key) => str_replace('taxonomy:', '', $key))
            ->all();
    }
}

class TestSearchableTermsFilter
{
    public function handle($item)
    {
        return $item->get('is_searchable') !== false;
    }
}
