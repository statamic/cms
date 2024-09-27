<?php

namespace Tests\Search\Searchables;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
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

        EntryFactory::collection('blog')->slug('alfa')->create();
        EntryFactory::collection('blog')->slug('bravo')->published(false)->create();
        EntryFactory::collection('blog')->slug('charlie')->create();
        EntryFactory::collection('blog')->slug('delta')->locale('fr')->create();
        EntryFactory::collection('blog')->slug('echo')->locale('fr')->published(false)->create();
        EntryFactory::collection('blog')->slug('foxtrot')->locale('fr')->create();

        EntryFactory::collection('pages')->slug('xray')->create();
        EntryFactory::collection('pages')->slug('yankee')->published(false)->create();
        EntryFactory::collection('pages')->slug('zulu')->create();

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected entries.
        $this->assertEquals($expected, $provider->provide()->map->slug()->all());

        // Check if the entries are contained by the provider or not.
        foreach (Entry::all() as $entry) {
            $this->assertEquals(
                $shouldBeIn = in_array($entry->slug(), $expected),
                $provider->contains($entry),
                "Entry {$entry->slug()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function entriesProvider()
    {
        return [
            'all' => [
                null,
                ['searchables' => 'all'],
                ['alfa', 'charlie', 'delta', 'foxtrot', 'xray', 'zulu'],
            ],
            'all collections' => [
                null,
                ['searchables' => ['collection:*']],
                ['alfa', 'charlie', 'delta', 'foxtrot', 'xray', 'zulu'],
            ],
            'blog' => [
                null,
                ['searchables' => ['collection:blog']],
                ['alfa', 'charlie', 'delta', 'foxtrot'],
            ],
            'pages' => [
                null,
                ['searchables' => ['collection:pages']],
                ['xray', 'zulu'],
            ],

            'all, english' => [
                'en',
                ['searchables' => 'all'],
                ['alfa', 'charlie', 'xray', 'zulu'],
            ],
            'all collections, english' => [
                'en',
                ['searchables' => ['collection:*']],
                ['alfa', 'charlie', 'xray', 'zulu'],
            ],
            'blog, english' => [
                'en',
                ['searchables' => ['collection:blog']],
                ['alfa', 'charlie'],
            ],
            'pages, english' => [
                'en',
                ['searchables' => ['collection:pages']],
                ['xray', 'zulu'],
            ],

            'all, french' => [
                'fr',
                ['searchables' => 'all'],
                ['delta', 'foxtrot'],
            ],
            'all collections, french' => [
                'fr',
                ['searchables' => ['collection:*']],
                ['delta', 'foxtrot'],
            ],
            'blog, french' => [
                'fr',
                ['searchables' => ['collection:blog']],
                ['delta', 'foxtrot'],
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
        $a = EntryFactory::collection('blog')->slug('a')->create();
        $b = EntryFactory::collection('blog')->slug('b')->published(false)->create();
        $c = EntryFactory::collection('blog')->slug('c')->data(['is_searchable' => false])->create();
        $d = EntryFactory::collection('blog')->slug('d')->data(['is_searchable' => true])->create();
        $e = EntryFactory::collection('blog')->slug('e')->create();

        $provider = $this->makeProvider(null, [
            'searchables' => 'all',
            'filter' => $filter,
        ]);

        $this->assertEquals(['a', 'b', 'd', 'e'], $provider->provide()->map->slug()->all());

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
        return collect($keys === 'all' ? ['*'] : $keys)
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
