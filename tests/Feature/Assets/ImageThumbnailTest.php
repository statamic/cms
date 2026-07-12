<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
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
}
