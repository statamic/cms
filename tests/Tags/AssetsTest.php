<?php

namespace Tests\Tags;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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

        Storage::fake('test', ['url' => '/assets']);

        Storage::disk('test')->put('a.jpg', UploadedFile::fake()->image('a.jpg')->getContent());
        Storage::disk('test')->put('b.jpg', UploadedFile::fake()->image('b.jpg')->getContent());
        Storage::disk('test')->put('c.mp4', UploadedFile::fake()->create('c.mp4')->getContent());
        Storage::disk('test')->put('d.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        Storage::disk('test')->put('e.mp3', UploadedFile::fake()->create('e.mp3')->getContent());
        Storage::disk('test')->put('nested/private/f.jpg', UploadedFile::fake()->image('f.jpg')->getContent());
        Storage::disk('test')->put('nested/public/g.jpg', UploadedFile::fake()->image('g.jpg')->getContent());

        tap(AssetContainer::make('test')->disk('test'))->save();

        Asset::find('test::a.jpg')->data(['title' => 'Alpha'])->save();
        Asset::find('test::b.jpg')->data(['title' => 'Beta'])->save();
        Asset::find('test::c.mp4')->data(['title' => 'Gamma'])->save();
        Asset::find('test::d.svg')->data(['title' => 'Delta'])->save();
        Asset::find('test::e.mp3')->data(['title' => 'Epsilon'])->save();
        Asset::find('test::nested/private/f.jpg')->data(['title' => 'Zeta'])->save();
        Asset::find('test::nested/public/g.jpg')->data(['title' => 'Eta'])->save();
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

        $this->assertSame(['a', 'b', 'f', 'g'], $this->getFilenames([
            'extension:is' => 'jpg',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_filters_assets_by_custom_field_conditions()
    {
        Asset::find('test::b.jpg')->data([
            'title' => 'Beta',
            'alt' => 'Bob Ross',
        ])->save();

        $this->assertSame(['b'], $this->getFilenames([
            'alt:contains' => 'Bob',
        ]));
    }

    #[Test]
    public function it_supports_query_scopes()
    {
        app('statamic.scopes')[AssetsTagJpgScope::handle()] = AssetsTagJpgScope::class;

        $this->assertSame(['a', 'b', 'f', 'g'], $this->getFilenames([
            'query_scope' => AssetsTagJpgScope::handle(),
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_filters_assets_by_type()
    {
        $this->assertSame(['e'], $this->getFilenames([
            'type' => 'audio',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame(['a', 'b', 'f', 'g'], $this->getFilenames([
            'type' => 'image',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame(['d'], $this->getFilenames([
            'type' => 'svg',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame(['c'], $this->getFilenames([
            'type' => 'video',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame([], $this->getFilenames([
            'type' => 'invalid',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_gets_assets_from_a_collection()
    {
        $this->createCollectionWithAssetFields();

        tap(Entry::make()->collection('articles')->data([
            'hero' => 'a.jpg',
            'avatar' => 'b.jpg',
        ]))->save();

        tap(Entry::make()->collection('articles')->data([
            'hero' => 'c.mp4',
        ]))->save();

        $this->assertSame(['a', 'b', 'c'], $this->getFilenames([
            'collection' => 'articles',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_gets_unique_assets_from_a_collection()
    {
        $this->createCollectionWithAssetFields();

        tap(Entry::make()->collection('articles')->data(['hero' => 'a.jpg']))->save();
        tap(Entry::make()->collection('articles')->data(['hero' => 'a.jpg']))->save();

        $this->assertSame(['a'], $this->getFilenames([
            'collection' => 'articles',
        ]));
    }

    #[Test]
    public function it_gets_assets_from_a_collection_filtered_by_type()
    {
        $this->createCollectionWithAssetFields();

        tap(Entry::make()->collection('articles')->data([
            'hero' => 'a.jpg',
            'avatar' => 'c.mp4',
        ]))->save();

        $this->assertSame(['a'], $this->getFilenames([
            'collection' => 'articles',
            'type' => 'image',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_gets_assets_from_a_collection_filtered_by_fields()
    {
        $this->createCollectionWithAssetFields();

        tap(Entry::make()->collection('articles')->data([
            'hero' => 'a.jpg',
            'avatar' => 'b.jpg',
        ]))->save();

        $this->assertSame(['a'], $this->getFilenames([
            'collection' => 'articles',
            'fields' => 'hero',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_filters_by_folder_non_recursively()
    {
        $this->assertSame(['f'], $this->getFilenames([
            'folder' => 'nested/private',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_returns_root_assets_when_folder_is_slash_without_recursive()
    {
        $this->assertSame(['a', 'b', 'c', 'd', 'e'], $this->getFilenames([
            'folder' => '/',
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_keeps_legacy_filtering_params_working()
    {
        $this->assertSame(['g'], $this->getFilenames([
            'folder' => 'nested',
            'recursive' => true,
            'sort' => 'filename:asc',
            'offset' => 1,
            'limit' => 1,
        ]));

        $this->assertSame(['a', 'b', 'c', 'd', 'e', 'g'], $this->getFilenames([
            'not_in' => '/?nested/private',
            'sort' => 'filename:asc',
        ]));

        $this->assertSame(['c', 'd'], $this->getFilenames([
            'not_in' => '/?nested/private',
            'sort' => 'filename:asc',
            'offset' => 2,
            'limit' => 2,
        ]));
    }

    #[Test]
    public function it_returns_no_results_when_query_matches_nothing()
    {
        $results = $this->runTag(['title:is' => 'nonexistent']);

        $this->assertIsArray($results);
        $this->assertTrue($results['no_results']);
        $this->assertEquals(0, $results['total_results']);
    }

    private function createCollectionWithAssetFields()
    {
        tap(Collection::make('articles'))->save();

        $blueprint = tap(Blueprint::make('article')->setContents([
            'fields' => [
                ['handle' => 'hero', 'field' => ['type' => 'assets', 'container' => 'test', 'max_files' => 1]],
                ['handle' => 'avatar', 'field' => ['type' => 'assets', 'container' => 'test', 'max_files' => 1]],
            ],
        ]))->save();

        Blueprint::shouldReceive('in')->with('collections/articles')->andReturn(collect([$blueprint]));
    }

    private function runTag(array $params = [])
    {
        $tag = new Assets;
        $tag->setContext([]);
        $tag->setParameters(isset($params['collection'])
            ? $params
            : array_merge(['container' => 'test'], $params));

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
