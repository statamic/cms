<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Ffmpeg;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Assets\AssetsFieldtypeAsset;
use Statamic\Statamic;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class VideoThumbnailTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $tempDir;

    public function setUp(): void
    {
        parent::setUp();

        Ffmpeg::clearBinaryCache();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->tempDir = __DIR__.'/tmp',
        ]]);
    }

    public function tearDown(): void
    {
        Ffmpeg::clearBinaryCache();

        app('files')->deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    #[Test]
    public function it_omits_thumbnail_url_from_asset_payload_when_ffmpeg_is_unavailable()
    {
        $this->withoutFfmpeg();
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $asset = $this->createVideoAsset();

        $payload = (new AssetsFieldtypeAsset($asset))->resolve()['data'];

        $this->assertNull($payload['thumbnail']);
    }

    #[Test]
    public function it_returns_a_video_filetype_icon_when_video_thumbnail_cannot_be_generated()
    {
        $this->withoutFfmpeg();

        $asset = $this->createVideoAsset();

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get('/cp/thumbnails/'.base64_encode($asset->id()).'/small')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee(Statamic::svg('filetypes/video'), false);
    }

    #[Test]
    public function it_returns_a_video_filetype_icon_when_video_thumbnails_are_disabled()
    {
        config(['statamic.assets.video_thumbnails' => false]);
        Ffmpeg::clearBinaryCache();

        $asset = $this->createVideoAsset();

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get('/cp/thumbnails/'.base64_encode($asset->id()).'/small')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee(Statamic::svg('filetypes/video'), false);
    }

    private function createVideoAsset()
    {
        $container = AssetContainer::make('test')->disk('test')->save();

        return $container
            ->makeAsset('clip.mp4')
            ->upload(UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'));
    }

    private function withoutFfmpeg()
    {
        config(['statamic.assets.ffmpeg.binary' => null]);

        $this->mock(Ffmpeg::class, function ($mock) {
            $mock->shouldReceive('available')->andReturn(false);
            $mock->shouldReceive('ffmpegBinary')->andReturn(null);
            $mock->shouldReceive('extractThumbnail')->andReturn(null);
        });

        Ffmpeg::clearBinaryCache();
    }
}
