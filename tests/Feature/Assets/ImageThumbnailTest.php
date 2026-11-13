<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetRepository;
use Statamic\Contracts\Assets\AssetRepository as AssetRepositoryContract;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ImageThumbnailTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $tempDir;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->tempDir = __DIR__.'/tmp',
        ]]);

        Glide::cacheStore()->flush();
        File::delete(storage_path('statamic/glide'));
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    #[Test]
    public function it_returns_thumbnail()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $container
            ->makeAsset('one.png')
            ->upload(UploadedFile::fake()->image('one.png'));

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/thumbnails/'.base64_encode('test::one.png'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_404s_when_the_asset_doesnt_exist()
    {
        $container = AssetContainer::make('test')->disk('test')->save();

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/thumbnails/'.base64_encode('test::unknown.png'))
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_without_permission_to_view_asset()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $container
            ->makeAsset('one.png')
            ->upload(UploadedFile::fake()->image('one.png'));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/thumbnails/'.base64_encode('test::one.png'))
            ->assertForbidden();
    }

    #[Test]
    public function it_drops_a_missing_file_from_the_browser_after_the_thumbnail_read_fails()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $asset = $container
            ->makeAsset('one.png')
            ->upload(UploadedFile::fake()->image('one.png', 30, 60));
        $asset->data(['alt' => 'Event poster'])->save();
        $container
            ->makeAsset('keep.png')
            ->upload(UploadedFile::fake()->image('keep.png', 30, 60));

        $cached = Cache::get($asset->metaCacheKey());

        $this->assertIsArray($cached);
        $this->assertArrayHasKey('size', $cached);
        $this->assertArrayHasKey('mime_type', $cached);

        Storage::disk('test')->delete('one.png');

        $repository = $this->partialMock(AssetRepository::class);
        $repository->shouldNotReceive('delete');
        $this->instance(AssetRepositoryContract::class, $repository);

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/assets/browse/folders/test/')
            ->assertSuccessful()
            ->assertJsonFragment(['path' => 'one.png'])
            ->assertJsonFragment(['path' => 'keep.png']);

        $this
            ->actingAs($user)
            ->getJson('/cp/thumbnails/'.base64_encode('test::one.png'))
            ->assertNotFound();

        Blink::flush();

        $this
            ->actingAs($user)
            ->getJson('/cp/assets/browse/folders/test/')
            ->assertSuccessful()
            ->assertJsonMissing(['path' => 'one.png'])
            ->assertJsonFragment(['path' => 'keep.png']);

        $this->assertSame(
            'Event poster',
            YAML::parse(Storage::disk('test')->get('.meta/one.png.yaml'))['data']['alt']
        );
    }
}
