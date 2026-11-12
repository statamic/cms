<?php

namespace Tests\Feature\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Glide;
use Statamic\Facades\User;
use Statamic\Imaging\GlideCachePathResolver;
use Statamic\Imaging\HybridUrlBuilder;
use Statamic\Imaging\ImageGenerator;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImageCacheExplorerTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config([
            'statamic.assets.image_manipulation.cache' => 'image-cache-explorer',
            'cache.stores.glide' => ['driver' => 'array'],
        ]);
        Storage::fake('image-cache-explorer');
        Storage::fake('image-originals');

        foreach (['blog' => 'Editorial', 'blogger' => 'Profile photos', 'empty' => 'Empty'] as $handle => $title) {
            AssetContainer::make($handle)->title($title)->disk('image-originals')->save();
        }

        $this->actingAs(tap(User::make()->makeSuper())->save());
    }

    #[Test]
    public function it_lists_counts_and_sizes_per_container_including_empty_containers()
    {
        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blog/photo.jpg/one.jpg', '123');
        $disk->put('containers/blog/nested/photo.jpg/two.webp', '1234567');
        $disk->put('containers/blogger/profile.jpg/one.jpg', '12345678901');
        $disk->put('paths/photo.jpg/one.jpg', 'unrelated');
        $disk->put('http/example.com/photo.jpg/one.jpg', 'unrelated');

        $this->getJson(cp_route('utilities.cache.image-containers'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.handle', 'blogger')
            ->assertJsonPath('data.0.count', 1)
            ->assertJsonPath('data.0.size', 11)
            ->assertJsonPath('data.1.title', 'Editorial')
            ->assertJsonPath('data.1.count', 2)
            ->assertJsonPath('data.1.size', 10)
            ->assertJsonPath('data.1.size_formatted', '10 B')
            ->assertJsonPath('data.2.handle', 'empty')
            ->assertJsonPath('data.2.count', 0)
            ->assertJsonPath('data.2.size', 0);
    }

    #[Test]
    public function it_searches_titles_and_handles_and_sorts_before_paginating()
    {
        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blog/photo.jpg/one.jpg', str_repeat('a', 20));
        $disk->put('containers/blogger/photo.jpg/one.jpg', 'ab');

        $this->getJson(cp_route('utilities.cache.image-containers', ['perPage' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.handle', 'blog')
            ->assertJsonPath('meta.last_page', 3);

        $this->getJson(cp_route('utilities.cache.image-containers', ['perPage' => 1, 'page' => 2]))
            ->assertOk()
            ->assertJsonPath('data.0.handle', 'blogger')
            ->assertJsonPath('meta.from', 2);

        $this->getJson(cp_route('utilities.cache.image-containers', ['sort' => 'size', 'order' => 'asc']))
            ->assertOk()
            ->assertJsonPath('data.0.handle', 'empty')
            ->assertJsonPath('data.1.handle', 'blogger')
            ->assertJsonPath('data.2.handle', 'blog');

        $this->getJson(cp_route('utilities.cache.image-containers', ['search' => 'EDITORIAL']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.container_count', 3)
            ->assertJsonPath('meta.single_container', null)
            ->assertJsonPath('data.0.handle', 'blog');

        $this->getJson(cp_route('utilities.cache.image-containers', ['search' => 'BLOG']))
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    #[Test]
    public function it_honors_saved_and_requested_columns()
    {
        User::current()->setPreference('cache.image_containers.columns', ['handle', 'size'])->save();

        $columns = collect($this->getJson(cp_route('utilities.cache.image-containers'))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['handle', 'size'], $columns->where('visible', true)->pluck('field')->values()->all());

        $columns = collect($this->getJson(cp_route('utilities.cache.image-containers', ['columns' => 'count,title,actions']))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['count', 'title', 'actions'], $columns->where('visible', true)->pluck('field')->values()->all());
        $this->assertSame(0, $columns->keyBy('field')['title']['defaultOrder']);
        $this->assertFalse($columns->keyBy('field')['handle']['defaultVisibility']);

        User::current()->setPreference('cache.image_assets.columns', ['path', 'size'])->save();

        $columns = collect($this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))
            ->assertOk()->json('meta.columns'));

        $this->assertSame(['path', 'size'], $columns->where('visible', true)->pluck('field')->values()->all());
    }

    #[Test]
    public function it_identifies_a_single_container_even_when_search_hides_it()
    {
        AssetContainer::find('blogger')->delete();
        AssetContainer::find('empty')->delete();

        $this->getJson(cp_route('utilities.cache.image-containers', ['search' => 'no matches']))
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.container_count', 1)
            ->assertJsonPath('meta.single_container.id', 'blog');
    }

    #[Test]
    public function it_groups_cached_variations_by_asset_including_nested_paths_and_missing_originals()
    {
        $asset = $this->createAsset('blog', 'nested/my photo.jpg');
        $generator = app(ImageGenerator::class);
        $generator->generateByAsset($asset, ['w' => 10]);
        $generator->generateByAsset($asset, ['w' => 20, 'fm' => 'webp']);

        $disk = Storage::disk('image-cache-explorer');
        $size = collect($disk->allFiles('containers/blog'))->sum(fn ($path) => $disk->size($path));
        $disk->put('containers/blog/deleted.jpg/hash/deleted.jpg', 'orphan');
        $disk->put('containers/blogger/other.jpg/hash/other.jpg', 'other');

        $response = $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        $assets = collect($response->json('data'))->keyBy('path');
        $this->assertSame('blog::nested/my photo.jpg', $assets['nested/my photo.jpg']['id']);
        $this->assertSame(2, $assets['nested/my photo.jpg']['count']);
        $this->assertSame($size, $assets['nested/my photo.jpg']['size']);
        $this->assertSame(1, $assets['deleted.jpg']['count']);
        $this->assertSame(6, $assets['deleted.jpg']['size']);
    }

    #[Test]
    public function it_searches_and_sorts_cached_assets_before_paginating()
    {
        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blog/nested/large.jpg/hash/large.jpg', str_repeat('a', 20));
        $disk->put('containers/blog/small.jpg/hash/small.jpg', 'ab');

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog', 'perPage' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.path', 'nested/large.jpg')
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog', 'perPage' => 1, 'page' => 2]))
            ->assertOk()
            ->assertJsonPath('data.0.path', 'small.jpg');

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog', 'sort' => 'size', 'order' => 'asc']))
            ->assertOk()
            ->assertJsonPath('data.0.path', 'small.jpg');

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog', 'search' => 'NESTED']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.path', 'nested/large.jpg');
    }

    #[Test]
    public function it_previews_existing_variants_without_generating_more_cached_images()
    {
        $asset = $this->createAsset('blog', 'nested/my photo.jpg');
        $generator = app(ImageGenerator::class);
        $generator->generateByAsset($asset, ['w' => 10]);
        $generator->generateByAsset($asset, ['w' => 20, 'fm' => 'webp']);

        $disk = Storage::disk('image-cache-explorer');
        $files = $disk->allFiles();
        $manifest = Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($asset));

        $variants = collect($this->getJson(cp_route('utilities.cache.image-variants', [
            'container' => 'blog',
            'asset' => $asset->id(),
        ]))->assertOk()->assertJsonCount(2, 'data')->json('data'));

        $this->assertEqualsCanonicalizing(['JPG', 'WEBP'], $variants->pluck('format')->all());

        foreach ($variants as $variant) {
            $this->assertSame($disk->size($variant['id']), $variant['size']);
            $this->assertNotNull($variant['generated_at']);
        }

        $assetData = $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))
            ->assertOk()->json('data.0');

        $smallest = $variants->sortBy('size')->first();
        $this->assertSame($smallest['preview_url'], $assetData['thumbnail']);
        $this->assertArrayNotHasKey('thumbnail_size', $assetData);

        $response = $this->get($assetData['thumbnail'])->assertOk();
        $this->assertSame($disk->get($smallest['id']), $response->streamedContent());
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertSame($files, $disk->allFiles());
        $this->assertSame($manifest, Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($asset)));
    }

    #[Test]
    public function it_sorts_and_paginates_variants_by_size_and_generation_date()
    {
        $disk = Storage::disk('image-cache-explorer');
        $large = 'containers/blog/photo.jpg/large/photo.jpg';
        $small = 'containers/blog/photo.jpg/small/photo.webp';
        $disk->put($large, '1234567890');
        $disk->put($small, 'ab');
        touch($disk->path($large), now()->subHour()->getTimestamp());
        touch($disk->path($small), now()->getTimestamp());
        clearstatcache();

        $input = ['container' => 'blog', 'asset' => 'blog::photo.jpg', 'perPage' => 1];
        $url = 'utilities.cache.image-variants';

        $this->getJson(cp_route($url, $input))
            ->assertOk()
            ->assertJsonPath('data.0.id', $large)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson(cp_route($url, $input + ['page' => 2]))
            ->assertOk()
            ->assertJsonPath('data.0.id', $small);

        $this->getJson(cp_route($url, $input + ['sort' => 'generated_at', 'order' => 'desc']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $small);

        $this->getJson(cp_route($url, $input + ['sort' => 'size', 'order' => 'asc']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $small);

        $this->getJson(cp_route($url, $input + ['search' => 'WEBP']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $small);
    }

    #[Test]
    public function it_clears_one_variation_and_its_path_cache_while_preserving_other_variations_and_hybrid_recipes()
    {
        $asset = $this->createAsset('blog', 'nested/photo.jpg');
        $otherAsset = $this->createAsset('blog', 'other.jpg');
        $generator = app(ImageGenerator::class);
        $path = $generator->generateByAsset($asset, ['w' => 10]);
        $otherPath = $generator->generateByAsset($asset, ['w' => 20]);
        $otherAssetPath = $generator->generateByAsset($otherAsset, ['w' => 10]);

        $builder = new HybridUrlBuilder(app(GlideCachePathResolver::class), ['route' => '/img']);
        $builder->build($asset, ['w' => 10]);
        $builder->build($asset, ['w' => 20]);

        $store = Glide::cacheStore();
        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);
        $aliasKey = 'asset::'.$asset->id().'::alias';
        $store->forever($aliasKey, $path);
        $store->forever($manifestKey, [...$store->get($manifestKey), $aliasKey]);
        $manifest = $store->get($manifestKey);
        $hybridRecipe = $store->get('hybrid::'.$path);

        $disk = Storage::disk('image-cache-explorer');
        $size = $disk->size($path);

        $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
            'container' => 'blog',
            'asset' => $asset->id(),
            'variant' => $path,
        ])
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('size', $size)
            ->assertJsonPath('remaining', 1)
            ->assertJsonPath('remaining_size', $disk->size($otherPath));

        $disk->assertMissing($path);
        $disk->assertExists([$otherPath, $otherAssetPath]);
        Storage::disk('image-originals')->assertExists(['nested/photo.jpg', 'other.jpg']);

        $clearedKey = ImageGenerator::manipulationCacheKey('asset', $asset, ['w' => 10]);
        $otherKey = ImageGenerator::manipulationCacheKey('asset', $asset, ['w' => 20]);
        $this->assertNull($store->get($clearedKey));
        $this->assertNull($store->get($aliasKey));
        $this->assertSame($otherPath, $store->get($otherKey));
        $this->assertSame($hybridRecipe, $store->get('hybrid::'.$path));
        $this->assertSame(array_values(array_diff($manifest, [$clearedKey, $aliasKey])), $store->get($manifestKey));

        $this->getJson(cp_route('utilities.cache.image-variants', ['container' => 'blog', 'asset' => $asset->id()]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $otherPath);

        $generator->generateByAsset($asset, ['w' => 10]);
        $disk->assertExists($path);
    }

    #[Test]
    public function it_clears_the_last_variation_and_rejects_variations_outside_the_selected_asset()
    {
        $asset = $this->createAsset('blog', 'photo.jpg');
        $otherAsset = $this->createAsset('blogger', 'other.jpg');
        $generator = app(ImageGenerator::class);
        $path = $generator->generateByAsset($asset, ['w' => 10]);
        $otherPath = $generator->generateByAsset($otherAsset, ['w' => 10]);

        foreach ([$otherPath, 'containers/blog/photo.jpg/../../other.jpg', 'missing', null] as $variant) {
            $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
                'container' => 'blog',
                'asset' => $asset->id(),
                'variant' => $variant,
            ])->assertUnprocessable()->assertJsonValidationErrors('variant');
        }

        $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
            'container' => 'blog',
            'asset' => $asset->id(),
            'variant' => $path,
        ])->assertOk()->assertJsonPath('remaining', 0)->assertJsonPath('remaining_size', 0);

        Storage::disk('image-cache-explorer')->assertExists($otherPath);
        $this->assertNull(Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($asset)));
        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))
            ->assertOk()->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_bulk_clears_only_selected_containers_and_assets()
    {
        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blog/first.jpg/hash/first.jpg', 'first');
        $disk->put('containers/blog/second.jpg/hash/second.jpg', 'second');
        $disk->put('containers/blog/keep.jpg/hash/keep.jpg', 'keep');
        $disk->put('containers/blogger/keep.jpg/hash/keep.jpg', 'other');

        $this->postJson(cp_route('utilities.cache.clear-image-asset'), [
            'container' => 'blog',
            'assets' => ['blog::first.jpg', 'blog::second.jpg'],
        ])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('size', 11);

        $disk->assertExists(['containers/blog/keep.jpg/hash/keep.jpg', 'containers/blogger/keep.jpg/hash/keep.jpg']);
        $disk->assertMissing(['containers/blog/first.jpg/hash/first.jpg', 'containers/blog/second.jpg/hash/second.jpg']);

        $this->postJson(cp_route('utilities.cache.clear-image-container'), ['containers' => ['blog', 'empty']])
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('size', 4);

        $disk->assertExists('containers/blogger/keep.jpg/hash/keep.jpg');
        $this->assertSame([], $disk->allFiles('containers/blog'));
    }

    #[Test]
    public function it_bulk_clears_selected_variations_and_updates_their_shared_manifest()
    {
        $asset = $this->createAsset('blog', 'photo.jpg');
        $generator = app(ImageGenerator::class);
        $first = $generator->generateByAsset($asset, ['w' => 10]);
        $second = $generator->generateByAsset($asset, ['w' => 20]);
        $keep = $generator->generateByAsset($asset, ['w' => 30]);
        $disk = Storage::disk('image-cache-explorer');
        $size = $disk->size($first) + $disk->size($second);

        $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
            'container' => 'blog',
            'asset' => $asset->id(),
            'variants' => [$first, $second],
        ])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('size', $size)
            ->assertJsonPath('remaining', 1);

        $disk->assertMissing([$first, $second]);
        $disk->assertExists($keep);
        $this->assertCount(1, Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($asset)));
        $generator->generateByAsset($asset, ['w' => 10]);
        $disk->assertExists($first);
    }

    #[Test]
    public function it_validates_every_bulk_selection_before_clearing_any_files()
    {
        $disk = Storage::disk('image-cache-explorer');
        $path = 'containers/blog/photo.jpg/hash/photo.jpg';
        $disk->put($path, 'cached');

        foreach ([
            ['containers' => ['blog', 'missing']],
            ['containers' => []],
            ['containers' => ['blog', 'blog']],
            ['container' => 'blog', 'containers' => ['blog']],
        ] as $input) {
            $this->postJson(cp_route('utilities.cache.clear-image-container'), $input)->assertUnprocessable();
            $disk->assertExists($path);
        }

        $this->postJson(cp_route('utilities.cache.clear-image-asset'), [
            'container' => 'blog',
            'assets' => ['blog::photo.jpg', 'blogger::other.jpg'],
        ])->assertUnprocessable();

        $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
            'container' => 'blog',
            'asset' => 'blog::photo.jpg',
            'variants' => [$path, 'containers/blogger/other.jpg/hash/other.jpg'],
        ])->assertUnprocessable();

        $disk->assertExists($path);
    }

    #[Test]
    public function it_always_shows_variation_actions_with_the_column_customizer_disabled()
    {
        Storage::disk('image-cache-explorer')->put('containers/blog/photo.jpg/hash/photo.jpg', 'cached');
        User::current()->setPreference('cache.image_variants.columns', ['preview', 'size'])->save();

        $columns = collect($this->getJson(cp_route('utilities.cache.image-variants', [
            'container' => 'blog',
            'asset' => 'blog::photo.jpg',
            'columns' => 'preview,size',
        ]))->assertOk()->json('meta.columns'))->keyBy('field');

        $this->assertTrue($columns['actions']['visible']);
        $this->assertFalse($columns['actions']['sortable']);
    }

    #[Test]
    public function it_restricts_previews_to_images_in_the_selected_container()
    {
        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blogger/photo.jpg/hash/photo.jpg', UploadedFile::fake()->image('photo.jpg')->get());
        $disk->put('containers/blog/not-an-image.txt', 'not an image');
        $disk->put('containers/blog/script.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        foreach ([
            'containers/blogger/photo.jpg/hash/photo.jpg',
            'containers/blog/../blogger/photo.jpg/hash/photo.jpg',
            'containers/blog/./photo.jpg',
            'containers/blog/missing.jpg',
            'containers/blog/..\\blogger/photo.jpg',
            'paths/photo.jpg',
        ] as $path) {
            $this->get(cp_route('utilities.cache.image-preview', ['container' => 'blog', 'path' => $path]))
                ->assertNotFound();
        }

        foreach (['containers/blog/not-an-image.txt', 'containers/blog/script.svg'] as $path) {
            $this->get(cp_route('utilities.cache.image-preview', ['container' => 'blog', 'path' => $path]))
                ->assertStatus(415);
        }
    }

    #[Test]
    public function it_requires_asset_view_permission_to_preview_cached_images()
    {
        $path = 'containers/blog/photo.jpg/hash/photo.jpg';
        Storage::disk('image-cache-explorer')->put($path, UploadedFile::fake()->image('photo.jpg')->get());

        $this->setTestRoles([
            'cache_manager' => ['access cp', 'access cache utility'],
            'asset_viewer' => ['access cp', 'access cache utility', 'view blog assets'],
        ]);
        $user = User::make()->assignRole('cache_manager')->save();
        $this->actingAs($user);
        $url = cp_route('utilities.cache.image-preview', ['container' => 'blog', 'path' => $path]);

        $this->getJson($url)->assertForbidden();

        $this->actingAs(User::make()->assignRole('asset_viewer')->save());
        $this->getJson($url)->assertOk();
    }

    #[Test]
    public function it_clears_an_individual_asset_cache_and_updates_totals()
    {
        $asset = $this->createAsset('blog', 'nested/photo.jpg');
        $otherAsset = $this->createAsset('blog', 'nested/photo.jpg-extra.jpg');
        $generator = app(ImageGenerator::class);
        $generator->generateByAsset($asset, ['w' => 10]);
        $generator->generateByAsset($asset, ['w' => 20]);
        $generator->generateByAsset($otherAsset, ['w' => 10]);

        (new HybridUrlBuilder(app(GlideCachePathResolver::class), ['route' => '/img']))->build($asset, ['w' => 10]);

        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);
        $keys = Glide::cacheStore()->get($manifestKey);
        $hybridKeys = collect($keys)->filter(fn ($key) => str_starts_with($key, 'hybrid::'))->values()->all();
        $disk = Storage::disk('image-cache-explorer');
        $size = collect($disk->allFiles('containers/blog/'.$asset->path()))->sum(fn ($path) => $disk->size($path));
        $otherFiles = $disk->allFiles('containers/blog/'.$otherAsset->path());

        $this->postJson(cp_route('utilities.cache.clear-image-asset'), ['container' => 'blog', 'asset' => $asset->id()])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('size', $size)
            ->assertJsonPath('message', fn ($message) => str_contains($message, 'for nested/photo.jpg in '));

        $this->assertSame([], $disk->allFiles('containers/blog/'.$asset->path()));
        $this->assertSame($otherFiles, $disk->allFiles('containers/blog/'.$otherAsset->path()));
        Storage::disk('image-originals')->assertExists($asset->path());
        $this->assertSame($hybridKeys, Glide::cacheStore()->get($manifestKey));

        foreach (array_diff($keys, $hybridKeys) as $key) {
            $this->assertNull(Glide::cacheStore()->get($key));
        }

        $this->assertNotEmpty(Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($otherAsset)));

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.path', $otherAsset->path());

        $this->getJson(cp_route('utilities.cache.image-containers', ['search' => 'Editorial']))
            ->assertOk()
            ->assertJsonPath('data.0.count', 1);

        $generator->generateByAsset($asset, ['w' => 10]);
        $this->assertCount(1, $disk->allFiles('containers/blog/'.$asset->path()));
    }

    #[Test]
    #[DataProvider('hybridCacheClearActions')]
    public function it_regenerates_existing_hybrid_urls_after_clearing_an_asset_or_container(string $action)
    {
        $disk = Storage::disk('image-cache-explorer');
        config([
            'statamic.assets.image_manipulation.cache' => 'hybrid',
            'statamic.assets.image_manipulation.cache_path' => $disk->path(''),
        ]);

        $asset = $this->createAsset('blog', 'nested/photo.jpg');
        $builder = new HybridUrlBuilder(app(GlideCachePathResolver::class), ['route' => '/img']);
        $url = $builder->build($asset, ['w' => 10]);
        $uncachedUrl = $builder->build($asset, ['w' => 20]);
        $path = app(ImageGenerator::class)->generateByAsset($asset, ['w' => 10]);
        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);
        $recipes = collect(Glide::cacheStore()->get($manifestKey))
            ->filter(fn ($key) => str_starts_with($key, 'hybrid::'))
            ->mapWithKeys(fn ($key) => [$key => Glide::cacheStore()->get($key)]);

        $this->get($url)->assertOk();

        $input = ['container' => 'blog'];

        if ($action === 'clear-image-asset') {
            $input['asset'] = $asset->id();
        }

        $this->postJson(cp_route('utilities.cache.'.$action), $input)
            ->assertOk()
            ->assertJsonPath('count', 1);

        $disk->assertMissing($path);
        $this->assertSame($recipes->keys()->all(), Glide::cacheStore()->get($manifestKey));
        $this->assertNull(Glide::cacheStore()->get(ImageGenerator::manipulationCacheKey('asset', $asset, ['w' => 10])));

        foreach ($recipes as $key => $recipe) {
            $this->assertSame($recipe, Glide::cacheStore()->get($key));
        }

        $this->get($url)->assertOk();
        $this->get($uncachedUrl)->assertOk();
        $disk->assertExists($path);
        $this->assertCount(2, $disk->allFiles('containers/blog/'.$asset->path()));

        Glide::clearAsset($asset);

        $this->assertSame([], $disk->allFiles('containers/blog/'.$asset->path()));
        $this->assertNull(Glide::cacheStore()->get($manifestKey));

        foreach ($recipes->keys() as $key) {
            $this->assertNull(Glide::cacheStore()->get($key));
        }
    }

    public static function hybridCacheClearActions(): array
    {
        return [
            'asset' => ['clear-image-asset'],
            'container' => ['clear-image-container'],
        ];
    }

    #[Test]
    public function it_can_clear_cached_files_for_a_missing_original_asset()
    {
        $disk = Storage::disk('image-cache-explorer');
        $path = 'containers/blog/deleted.jpg/hash/deleted.jpg';
        $disk->put($path, 'orphan');

        $this->postJson(cp_route('utilities.cache.clear-image-asset'), ['container' => 'blog', 'asset' => 'blog::deleted.jpg'])
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('size', 6);

        $disk->assertMissing($path);
    }

    #[Test]
    public function it_clears_only_the_selected_container_and_its_cache_metadata()
    {
        $asset = $this->createAsset('blog', 'nested/photo.jpg');
        $otherAsset = $this->createAsset('blogger', 'profile.jpg');
        $generator = app(ImageGenerator::class);
        $params = ['w' => 10];
        $generator->generateByAsset($asset, $params);
        $generator->generateByAsset($otherAsset, $params);

        (new HybridUrlBuilder(app(GlideCachePathResolver::class), ['route' => '/img']))->build($asset, $params);

        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);
        $keys = Glide::cacheStore()->get($manifestKey);
        $this->assertCount(2, $keys);
        $hybridKeys = collect($keys)->filter(fn ($key) => str_starts_with($key, 'hybrid::'))->values()->all();

        $disk = Storage::disk('image-cache-explorer');
        $disk->put('containers/blog/deleted.jpg/old.jpg', 'orphan');
        $disk->put('paths/photo.jpg/one.jpg', 'unrelated');
        $size = collect($disk->allFiles('containers/blog'))->sum(fn ($path) => $disk->size($path));
        $otherFiles = $disk->allFiles('containers/blogger');

        Glide::cacheStore()->forever('unrelated', 'keep');

        $this->postJson(cp_route('utilities.cache.clear-image-container'), ['container' => 'blog'])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('size', $size)
            ->assertJsonPath('message', fn ($message) => str_starts_with($message, 'Cleared 2 cached images (')
                && str_contains($message, 'from Editorial in '));

        $this->assertSame([], $disk->allFiles('containers/blog'));
        $this->assertSame($otherFiles, $disk->allFiles('containers/blogger'));
        $disk->assertExists('paths/photo.jpg/one.jpg');
        Storage::disk('image-originals')->assertExists(['nested/photo.jpg', 'profile.jpg']);
        $this->assertSame($hybridKeys, Glide::cacheStore()->get($manifestKey));

        foreach (array_diff($keys, $hybridKeys) as $key) {
            $this->assertNull(Glide::cacheStore()->get($key));
        }

        $this->assertNotEmpty(Glide::cacheStore()->get(ImageGenerator::assetCacheManifestKey($otherAsset)));
        $this->assertSame('keep', Glide::cacheStore()->get('unrelated'));

        $this->getJson(cp_route('utilities.cache.image-containers', ['search' => 'Editorial']))
            ->assertOk()
            ->assertJsonPath('data.0.count', 0)
            ->assertJsonPath('data.0.size', 0);

        $generator->generateByAsset($asset, $params);
        $this->assertCount(1, $disk->allFiles('containers/blog'));
    }

    #[Test]
    public function it_handles_clearing_an_empty_container()
    {
        $this->postJson(cp_route('utilities.cache.clear-image-container'), ['container' => 'empty'])
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('size', 0);
    }

    #[Test]
    public function it_requires_cache_utility_permission()
    {
        $this->setTestRoles(['cp_user' => ['access cp']]);
        $this->actingAs(User::make()->assignRole('cp_user')->save());

        $this->getJson(cp_route('utilities.cache.image-containers'))->assertForbidden();
        $this->postJson(cp_route('utilities.cache.clear-image-container'), ['container' => 'blog'])->assertForbidden();
        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'blog']))->assertForbidden();
        $this->postJson(cp_route('utilities.cache.clear-image-asset'), ['container' => 'blog', 'asset' => 'blog::photo.jpg'])->assertForbidden();
        $this->getJson(cp_route('utilities.cache.image-variants', ['container' => 'blog', 'asset' => 'blog::photo.jpg']))->assertForbidden();
        $this->getJson(cp_route('utilities.cache.image-preview', ['container' => 'blog', 'path' => 'containers/blog/photo.jpg/hash/photo.jpg']))->assertForbidden();
        $this->postJson(cp_route('utilities.cache.clear-image-variant'), [
            'container' => 'blog',
            'asset' => 'blog::photo.jpg',
            'variant' => 'containers/blog/photo.jpg/hash/photo.jpg',
        ])->assertForbidden();
    }

    #[Test]
    public function it_validates_listing_parameters_and_container_handles()
    {
        foreach ([['page' => 0], ['perPage' => 101], ['sort' => 'actions'], ['order' => 'invalid'], ['search' => ['invalid']]] as $input) {
            $this->getJson(cp_route('utilities.cache.image-containers', $input))->assertUnprocessable();
        }

        foreach ([[], ['container' => 'missing'], ['container' => '../blog'], ['container' => ['blog']]] as $input) {
            $this->postJson(cp_route('utilities.cache.clear-image-container'), $input)
                ->assertUnprocessable()->assertJsonValidationErrors('container');
        }

        foreach (['blogger::photo.jpg', 'blog::../blogger/photo.jpg', 'blog::missing.jpg', null, ['blog::photo.jpg']] as $asset) {
            $this->postJson(cp_route('utilities.cache.clear-image-asset'), ['container' => 'blog', 'asset' => $asset])
                ->assertUnprocessable()->assertJsonValidationErrors('asset');
        }

        $this->getJson(cp_route('utilities.cache.image-assets', ['container' => 'missing']))
            ->assertUnprocessable()->assertJsonValidationErrors('container');
    }

    #[Test]
    public function it_passes_the_explorer_endpoints_to_the_cache_page()
    {
        $this->get(cp_route('utilities.cache'))->assertInertia(fn (AssertableInertia $page) => $page
            ->where('imageContainersUrl', cp_route('utilities.cache.image-containers'))
            ->where('clearImageContainerUrl', cp_route('utilities.cache.clear-image-container'))
            ->where('imageAssetsUrl', cp_route('utilities.cache.image-assets'))
            ->where('clearImageAssetUrl', cp_route('utilities.cache.clear-image-asset'))
            ->where('imageVariantsUrl', cp_route('utilities.cache.image-variants'))
            ->where('clearImageVariantUrl', cp_route('utilities.cache.clear-image-variant'))
            ->missing('images')
        );
    }

    private function createAsset(string $container, string $path)
    {
        Storage::disk('image-originals')->put($path, UploadedFile::fake()->image(basename($path), 30, 60)->get());

        return tap(AssetContainer::find($container)->makeAsset($path))->save();
    }
}
