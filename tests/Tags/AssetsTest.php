<?php

namespace Tests\Tags;

use Illuminate\Http\UploadedFile;
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

        Storage::fake('test', ['url' => '/assets']);

        Storage::disk('test')->put('a.jpg', UploadedFile::fake()->image('a.jpg')->getContent());
        Storage::disk('test')->put('b.jpg', UploadedFile::fake()->image('b.jpg')->getContent());
        Storage::disk('test')->put('c.mp4', UploadedFile::fake()->create('c.mp4')->getContent());
        Storage::disk('test')->put('d.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        Storage::disk('test')->put('nested/private/e.jpg', UploadedFile::fake()->image('e.jpg')->getContent());
        Storage::disk('test')->put('nested/public/f.jpg', UploadedFile::fake()->image('f.jpg')->getContent());

        tap(AssetContainer::make('test')->disk('test'))->save();

        Asset::find('test::a.jpg')->data(['title' => 'Alpha'])->save();
        Asset::find('test::b.jpg')->data(['title' => 'Beta'])->save();
        Asset::find('test::c.mp4')->data(['title' => 'Gamma'])->save();
        Asset::find('test::d.svg')->data(['title' => 'Delta'])->save();
        Asset::find('test::nested/private/e.jpg')->data(['title' => 'Epsilon'])->save();
        Asset::find('test::nested/public/f.jpg')->data(['title' => 'Zeta'])->save();
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

        $this->assertSame(['a', 'b', 'e', 'f'], $this->getFilenames([
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

        $this->assertSame(['a', 'b', 'e', 'f'], $this->getFilenames([
            'query_scope' => AssetsTagJpgScope::handle(),
            'sort' => 'filename:asc',
        ]));
    }

    #[Test]
    public function it_filters_assets_by_type()
    {
        $this->assertSame(['a', 'b', 'e', 'f'], $this->getFilenames([
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
    public function it_keeps_legacy_filtering_params_working()
    {
        $this->assertSame(['f'], $this->getFilenames([
            'folder' => 'nested',
            'recursive' => true,
            'sort' => 'filename:asc',
            'offset' => 1,
            'limit' => 1,
        ]));

        $this->assertSame(['a', 'b', 'c', 'd', 'f'], $this->getFilenames([
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
