<?php

namespace Tests\Assets;

use Tests\TestCase;
use Statamic\Facades\Asset;
use Illuminate\Support\Carbon;
use Statamic\Facades\AssetContainer;
use Illuminate\Http\UploadedFile;
use Statamic\Assets\AssetRepository;
use Illuminate\Support\Facades\Storage;

class AssetRepositoryTest extends TestCase
{
    /** @test */
    function it_saves_the_meta_file_to_disk()
    {
        $disk = Storage::fake('test');

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('foo', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->getAdapter()->getPathPrefix() . 'foo/image.jpg';
        touch($realFilePath, $timestamp = Carbon::now()->subMinutes(3)->timestamp);

        $container = AssetContainer::make('test')->disk('test');
        $asset = Asset::make()->container($container)->path('foo/image.jpg');
        $disk->assertMissing('foo/.meta/image.jpg.yaml');

        (new AssetRepository)->save($asset);

        $disk->assertExists($path = 'foo/.meta/image.jpg.yaml');
        $contents = <<<EOT
data: {  }
size: 723
last_modified: $timestamp
width: 30
height: 60

EOT;
        $this->assertEquals($contents, $disk->get($path));
    }
}
