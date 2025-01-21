<?php

namespace Tests\Assets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Assets\ExtractInfo;
use Tests\TestCase;

class ExtractInfoTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        $this->container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test');
    }

    #[Test]
    public function it_can_extract_basic_id3_info_from_text_asset()
    {
        $asset = (new Asset)->container($this->container)->path('a.txt');

        $extracted = (new ExtractInfo)->fromAsset($asset);

        $expected = [
            'filesize' => $this->isRunningWindows() ? 3 : 2,
            'filename' => 'a.txt',
            'encoding' => 'UTF-8',
        ];

        $this->assertArraySubset($expected, $extracted);
    }
}
