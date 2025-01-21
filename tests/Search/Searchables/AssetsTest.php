<?php

namespace Tests\Search\Searchables;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Search\Searchables\Assets;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('assetsProvider')]
    public function it_gets_assets($locale, $config, $expected)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        Storage::fake('images');
        Storage::fake('documents');
        AssetContainer::make('images')->disk('images')->save();
        AssetContainer::make('documents')->disk('documents')->save();

        Asset::make()->container('images')->path('a.jpg')->save();
        Storage::disk('images')->put('a.jpg', '');
        Asset::make()->container('images')->path('b.jpg')->save();
        Storage::disk('images')->put('b.jpg', '');
        Asset::make()->container('documents')->path('y.txt')->save();
        Storage::disk('documents')->put('y.txt', '');
        Asset::make()->container('documents')->path('z.txt')->save();
        Storage::disk('documents')->put('z.txt', '');

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected assets.
        $this->assertEquals($expected, $provider->provide()->map->filename()->all());

        // Check if the assets are contained by the provider or not.
        foreach (Asset::all() as $asset) {
            $this->assertEquals(
                $shouldBeIn = in_array($asset->filename(), $expected),
                $provider->contains($asset),
                "Asset {$asset->filename()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function assetsProvider()
    {
        return [
            'all' => [
                null,
                ['searchables' => 'all'],
                ['a', 'b', 'y', 'z'],
            ],
            'all containers' => [
                null,
                ['searchables' => ['assets:*']],
                ['a', 'b', 'y', 'z'],
            ],
            'images' => [
                null,
                ['searchables' => ['assets:images']],
                ['a', 'b'],
            ],
            'documents' => [
                null,
                ['searchables' => ['assets:documents']],
                ['y', 'z'],
            ],

            'all, english' => [
                'en',
                ['searchables' => 'all'],
                ['a', 'b', 'y', 'z'],
            ],
            'all containers, english' => [
                'en',
                ['searchables' => ['assets:*']],
                ['a', 'b', 'y', 'z'],
            ],
            'images, english' => [
                'en',
                ['searchables' => ['assets:images']],
                ['a', 'b'],
            ],
            'documents, english' => [
                'en',
                ['searchables' => ['assets:documents']],
                ['y', 'z'],
            ],

            'all, french' => [
                'fr',
                ['searchables' => 'all'],
                ['a', 'b', 'y', 'z'],
            ],
            'all containers, french' => [
                'fr',
                ['searchables' => ['assets:*']],
                ['a', 'b', 'y', 'z'],
            ],
            'images, french' => [
                'fr',
                ['searchables' => ['assets:images']],
                ['a', 'b'],
            ],
            'documents, french' => [
                'fr',
                ['searchables' => ['assets:documents']],
                ['y', 'z'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        Storage::fake('images');
        AssetContainer::make('images')->disk('images')->save();

        Storage::disk('images')->put('a.jpg', '');
        Storage::disk('images')->put('b.jpg', '');
        Storage::disk('images')->put('c.jpg', '');
        Storage::disk('images')->put('d.jpg', '');
        $a = tap(Asset::make()->container('images')->path('a.jpg'))->save();
        $b = tap(Asset::make()->container('images')->path('b.jpg')->set('is_searchable', false))->save();
        $c = tap(Asset::make()->container('images')->path('c.jpg')->set('is_searchable', true))->save();
        $d = tap(Asset::make()->container('images')->path('d.jpg'))->save();

        $provider = $this->makeProvider(null, [
            'searchables' => 'all',
            'filter' => $filter,
        ]);

        $this->assertEquals(['a', 'c', 'd'], $provider->provide()->map->filename()->all());

        $this->assertTrue($provider->contains($a));
        $this->assertFalse($provider->contains($b));
        $this->assertTrue($provider->contains($c));
        $this->assertTrue($provider->contains($d));
    }

    public static function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableAssetsFilter::class],
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

        return (new Assets)->setIndex($index)->setKeys($keys);
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
            ->map(fn ($key) => str_replace('assets:', '', $key))
            ->all();
    }
}

class TestSearchableAssetsFilter
{
    public function handle($item)
    {
        return $item->get('is_searchable') !== false;
    }
}
