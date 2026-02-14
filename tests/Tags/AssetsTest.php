<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Query\Scopes\Scope;
use Statamic\Tags\Assets;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/'],
            'es' => ['name' => 'Spanish', 'locale' => 'es_ES', 'url' => 'http://localhost/es/'],
        ]);

        Storage::fake('test', ['url' => '/assets']);

        Storage::disk('test')->put('a.jpg', '');
        Storage::disk('test')->put('b.jpg', '');
        Storage::disk('test')->put('c.mp4', '');
        Storage::disk('test')->put('nested/private/d.jpg', '');
        Storage::disk('test')->put('nested/public/e.jpg', '');

        tap(AssetContainer::make('test')->disk('test'))->save();

        Asset::find('test::a.jpg')->data(['title' => 'Alpha'])->save();
        Asset::find('test::b.jpg')->data(['title' => 'Beta'])->save();
        Asset::find('test::c.mp4')->data(['title' => 'Gamma'])->save();
        Asset::find('test::nested/private/d.jpg')->data(['title' => 'Delta'])->save();
        Asset::find('test::nested/public/e.jpg')->data(['title' => 'Epsilon'])->save();
    }

    #[Test]
    public function it_filters_assets_by_conditions()
    {
        $this->assertSame(['a'], $this->getFilenames([
            'title:is' => 'Alpha',
        ]));

        $this->assertSame(['b'], $this->getFilenames([
            'filename:starts_with' => 'b',
        ]));

        $this->assertSame(['a', 'b', 'd', 'e'], $this->getFilenames([
            'extension:is' => 'jpg',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_filters_assets_by_localized_field_conditions()
    {
        Asset::find('test::b.jpg')->data([
            'en' => ['alt' => 'Bob Ross'],
        ])->save();

        $this->assertSame(['b'], $this->getFilenames([
            'alt:contains' => 'Bob',
        ]));
    }

    #[Test]
    public function it_supports_query_scopes()
    {
        app('statamic.scopes')[AssetsTagJpgScope::handle()] = AssetsTagJpgScope::class;

        $this->assertSame(['a', 'b', 'd', 'e'], $this->getFilenames([
            'query_scope' => AssetsTagJpgScope::handle(),
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_supports_paginating_assets()
    {
        $results = $this->runTag([
            'sort' => 'filename:asc',
            'paginate' => 2,
        ]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('results', $results);
        $this->assertArrayHasKey('paginate', $results);
        $this->assertCount(2, $results['results']);
        $this->assertSame(['a', 'b'], collect($results['results'])->map->filename()->all());
        $this->assertSame(5, $results['paginate']['total_items']);
    }

    #[Test]
    public function it_keeps_legacy_filtering_params_working()
    {
        $this->assertSame(['e'], $this->getFilenames([
            'folder' => 'nested',
            'recursive' => true,
            'sort' => 'filename:asc',
            'offset' => 1,
            'limit' => 1,
        ]));

        $this->assertSame(['a', 'b', 'd', 'e'], $this->getFilenames([
            'type' => 'image',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame(['a', 'b', 'c', 'e'], $this->getFilenames([
            'not_in' => '/?nested/private',
            'sort' => 'filename:asc',
        ]));
    }

    private function runTag(array $params = [])
    {
        $tag = new Assets;
        $tag->setContext([]);
        $tag->setParameters(array_merge(['container' => 'test'], $params));

        return $tag->index();
    }

    private function getFilenames(array $params = []): array
    {
        $results = $this->runTag($params);

        if (is_array($results) && isset($results['results'])) {
            $results = $results['results'];
        }

        if (is_array($results) && ($results['no_results'] ?? false)) {
            return [];
        }

        return collect($results)->map->filename()->values()->all();
    }
}

class AssetsTagJpgScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('extension', 'jpg');
    }
}
