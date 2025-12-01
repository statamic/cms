<?php

namespace Tests\Search\Searchables;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Scope;
use Statamic\Search\Searchables\Entries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('entriesProvider')]
    public function it_gets_entries($locale, $config, $expected)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        Collection::make('blog')->sites(['en', 'fr'])->save();
        Collection::make('pages')->sites(['en'])->save();

        EntryFactory::collection('blog')->id('alfa')->create();
        EntryFactory::collection('blog')->id('bravo')->published(false)->create();
        EntryFactory::collection('blog')->id('charlie')->create();
        EntryFactory::collection('blog')->id('delta')->locale('fr')->create();
        EntryFactory::collection('blog')->id('echo')->locale('fr')->published(false)->create();
        EntryFactory::collection('blog')->id('foxtrot')->locale('fr')->create();

        EntryFactory::collection('pages')->id('xray')->create();
        EntryFactory::collection('pages')->id('yankee')->published(false)->create();
        EntryFactory::collection('pages')->id('zulu')->create();

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected entries.
        $this->assertEquals($expected, $provider->provide()->all());

        // Check if the entries are contained by the provider or not.
        foreach (Entry::all() as $entry) {
            $this->assertEquals(
                $shouldBeIn = in_array($entry->reference(), $expected),
                $provider->contains($entry),
                "Entry {$entry->slug()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function entriesProvider()
    {
        return [
            'content' => [
                null,
                ['searchables' => 'content'],
                ['entry::alfa', 'entry::charlie', 'entry::delta', 'entry::foxtrot', 'entry::xray', 'entry::zulu'],
            ],
            'all collections' => [
                null,
                ['searchables' => ['collection:*']],
                ['entry::alfa', 'entry::charlie', 'entry::delta', 'entry::foxtrot', 'entry::xray', 'entry::zulu'],
            ],
            'blog' => [
                null,
                ['searchables' => ['collection:blog']],
                ['entry::alfa', 'entry::charlie', 'entry::delta', 'entry::foxtrot'],
            ],
            'pages' => [
                null,
                ['searchables' => ['collection:pages']],
                ['entry::xray', 'entry::zulu'],
            ],

            'content, english' => [
                'en',
                ['searchables' => 'content'],
                ['entry::alfa', 'entry::charlie', 'entry::xray', 'entry::zulu'],
            ],
            'all collections, english' => [
                'en',
                ['searchables' => ['collection:*']],
                ['entry::alfa', 'entry::charlie', 'entry::xray', 'entry::zulu'],
            ],
            'blog, english' => [
                'en',
                ['searchables' => ['collection:blog']],
                ['entry::alfa', 'entry::charlie'],
            ],
            'pages, english' => [
                'en',
                ['searchables' => ['collection:pages']],
                ['entry::xray', 'entry::zulu'],
            ],

            'content, french' => [
                'fr',
                ['searchables' => 'content'],
                ['entry::delta', 'entry::foxtrot'],
            ],
            'all collections, french' => [
                'fr',
                ['searchables' => ['collection:*']],
                ['entry::delta', 'entry::foxtrot'],
            ],
            'blog, french' => [
                'fr',
                ['searchables' => ['collection:blog']],
                ['entry::delta', 'entry::foxtrot'],
            ],
            'pages, french' => [
                'fr',
                ['searchables' => ['collection:pages']],
                [],
            ],
        ];
    }

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        Collection::make('blog')->save();
        $a = EntryFactory::collection('blog')->id('a')->create();
        $b = EntryFactory::collection('blog')->id('b')->published(false)->create();
        $c = EntryFactory::collection('blog')->id('c')->data(['is_searchable' => false])->create();
        $d = EntryFactory::collection('blog')->id('d')->data(['is_searchable' => true])->create();
        $e = EntryFactory::collection('blog')->id('e')->create();

        $provider = $this->makeProvider(null, [
            'searchables' => 'content',
            'filter' => $filter,
        ]);

        $this->assertEquals(
            ['entry::a', 'entry::b', 'entry::d', 'entry::e'],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains($a));
        $this->assertTrue($provider->contains($b));
        $this->assertFalse($provider->contains($c));
        $this->assertTrue($provider->contains($d));
        $this->assertTrue($provider->contains($e));
    }

    public static function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableEntriesFilter::class],
            'closure' => [
                function ($entry) {
                    return $entry->get('is_searchable') !== false;
                },
            ],
        ];
    }

    #[Test]
    public function it_can_use_a_query_scope()
    {
        CustomEntriesScope::register();

        Collection::make('blog')->save();
        $a = EntryFactory::collection('blog')->id('a')->create();
        $b = EntryFactory::collection('blog')->id('b')->create();
        $c = EntryFactory::collection('blog')->id('c')->data(['is_searchable' => false])->create();
        $d = EntryFactory::collection('blog')->id('d')->data(['is_searchable' => true])->create();
        $e = EntryFactory::collection('blog')->id('e')->create();

        $provider = $this->makeProvider(null, [
            'searchables' => 'content',
            'query_scope' => 'custom_entries_scope',
        ]);

        $this->assertEquals(
            ['entry::a', 'entry::b', 'entry::d', 'entry::e'],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains($a));
        $this->assertTrue($provider->contains($b));
        $this->assertFalse($provider->contains($c));
        $this->assertTrue($provider->contains($d));
        $this->assertTrue($provider->contains($e));
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new Entries)->setIndex($index)->setKeys($keys);
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
        return collect($keys === 'content' ? ['*'] : $keys)
            ->map(fn ($key) => str_replace('collection:', '', $key))
            ->all();
    }
}

class TestSearchableEntriesFilter
{
    public function handle($item)
    {
        return $item->get('is_searchable') !== false;
    }
}

class CustomEntriesScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('is_searchable', '!=', false);
    }
}
