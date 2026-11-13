<?php

namespace Tests\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\ReplaceAsset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Asset;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReplaceAssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->container = tap(
            (new AssetContainer)->handle('test_container')->disk('test')
        )->save();
    }

    private function createAsset($filename, $yaml = null)
    {
        $file = UploadedFile::fake()->image($filename, 30, 60);
        Storage::disk('test')->putFileAs('path/to', $file, $filename);

        if ($yaml) {
            Storage::disk('test')->put('path/to/.meta/'.$filename.'.yaml', $yaml);
        }
    }

    private function runAction(array $values)
    {
        return (new ReplaceAsset)
            ->context(['container' => 'test_container'])
            ->run(collect([Asset::find('test_container::path/to/original.jpg')]), $values);
    }

    #[Test]
    public function it_copies_data_from_the_original_asset()
    {
        $this->createAsset('original.jpg', "data:\n  alt: 'Original alt'\n  copyright: 'Someone'\n  focus: 25-75-1");
        $this->createAsset('new.jpg', "data:\n  alt: 'New alt'\n  caption: 'New caption'");

        $this->runAction([
            'new_asset' => 'path/to/new.jpg',
            'copy_data' => true,
            'delete_original' => true,
        ]);

        $this->assertNull($this->container->asset('path/to/original.jpg'));
        $this->assertEquals([
            'alt' => 'Original alt',
            'caption' => 'New caption',
            'copyright' => 'Someone',
            'focus' => '25-75-1',
        ], $this->container->asset('path/to/new.jpg')->data()->sortKeys()->all());
    }

    #[Test]
    public function it_does_not_copy_data_when_disabled()
    {
        $this->createAsset('original.jpg', "data:\n  alt: 'Original alt'");
        $this->createAsset('new.jpg');

        $this->runAction([
            'new_asset' => 'path/to/new.jpg',
            'copy_data' => false,
            'delete_original' => false,
        ]);

        $this->assertEquals(['alt' => 'Original alt'], $this->container->asset('path/to/original.jpg')->data()->all());
        $this->assertEquals([], $this->container->asset('path/to/new.jpg')->data()->all());
    }
}
