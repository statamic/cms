<?php

namespace Tests\Assets;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\DecoratedAdapter;
use League\Flysystem\Filesystem;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Assets\ExtractInfo;
use Statamic\Assets\S3MetadataStream;
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

    #[Test]
    #[DataProvider('nonS3Disks')]
    public function it_still_extracts_metadata_from_large_assets_on_other_disks(bool $local): void
    {
        $filesize = 104857601;
        $disk = Storage::fake('media');
        $stream = fopen($disk->path('a.txt'), 'w');
        ftruncate($stream, $filesize);
        fclose($stream);

        if (! $local) {
            $adapter = new class($disk->getAdapter()) extends DecoratedAdapter
            {
            };
            $disk = new FilesystemAdapter(new Filesystem($adapter), $adapter);
        }

        $asset = (new Asset)
            ->container($this->container->disk($disk))
            ->path('a.txt');

        $extracted = (new ExtractInfo)->fromAsset($asset);

        $this->assertArraySubset(['filesize' => $filesize, 'filename' => 'a.txt', 'encoding' => 'UTF-8'], $extracted);
    }

    public static function nonS3Disks(): array
    {
        return ['local' => [true], 'other remote' => [false]];
    }

    /** @see https://github.com/statamic/cms/issues/8626 */
    #[Test]
    #[DataProvider('metadataLocations')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function it_extracts_metadata_without_downloading_the_entire_s3_asset(bool $metadataAtEnd, string $memoryLimit): void
    {
        $size = 10 * 1024 ** 3;
        $downloadedBytesCount = 0;
        $video = file_get_contents(__DIR__.'/__fixtures__/video.mp4');
        $paddingSize = $size - strlen($video);
        $metadataOffset = strpos($video, 'moov') - 4;
        $metadata = $metadataAtEnd ? substr($video, $metadataOffset) : '';

        $segments = [
            0 => ($metadataAtEnd ? substr($video, 0, $metadataOffset) : $video).pack('Na4J', 1, 'free', $paddingSize),
            $size - strlen($metadata) => $metadata,
        ];

        $asset = $this->s3Asset($size, function (array $params) use ($segments, $size, &$downloadedBytesCount) {
            $this->assertSame('private-assets', $params['Bucket']);
            $this->assertSame('uploads/nested/video.mp4', $params['Key']);
            $this->assertSame('requester', $params['RequestPayer']);
            $this->assertTrue($params['@http']['stream']);

            if ($downloadedBytesCount) {
                $this->assertSame('"original"', $params['IfMatch']);
            }

            preg_match('/^bytes=(\d+)-(\d+)$/', $params['Range'], $range);
            [$start, $end] = [(int) $range[1], (int) $range[2]];
            $downloadedBytesCount += $end - $start + 1;
            $this->assertLessThan(512 * 1024 * 1024, $downloadedBytesCount);

            $body = str_repeat("\0", $end - $start + 1);

            foreach ($segments as $offset => $data) {
                $from = max($start, $offset);
                $to = min($end + 1, $offset + strlen($data));

                if ($from < $to) {
                    $body = substr_replace($body, substr($data, $from - $offset, $to - $from), $from - $start, $to - $from);
                }
            }

            return [
                'Body' => Utils::streamFor($body),
                'ETag' => '"original"',
                'ContentRange' => "bytes {$start}-{$end}/{$size}",
                '@metadata' => ['statusCode' => 206],
            ];
        });

        $originalMemoryLimit = ini_set('memory_limit', $memoryLimit);

        try {
            $extracted = (new ExtractInfo)->fromAsset($asset);
            $this->assertSame($memoryLimit, ini_get('memory_limit'));
        } finally {
            ini_set('memory_limit', $originalMemoryLimit);
        }

        $this->assertEmpty($extracted['error'] ?? []);
        $this->assertEquals(3, $extracted['playtime_seconds']);
        $this->assertEquals(320, $extracted['video']['resolution_x']);
        $this->assertEquals(180, $extracted['video']['resolution_y']);
    }

    private function s3Asset(int $size, callable $getObject): Asset
    {
        $client = Mockery::mock();
        $client->shouldReceive('getObject')->andReturnUsing($getObject);

        $disk = Mockery::mock(AwsS3V3Adapter::class);
        $disk->shouldReceive('size')->with('nested/video.mp4')->andReturn($size);
        $disk->shouldReceive('path')->with('nested/video.mp4')->andReturn('uploads/nested/video.mp4');
        $disk->shouldReceive('getClient')->andReturn($client);
        $disk->shouldReceive('getConfig')->andReturn([
            'bucket' => 'private-assets',
            'options' => ['RequestPayer' => 'requester'],
        ]);

        return (new Asset)
            ->container($this->container->disk($disk))
            ->path('nested/video.mp4');
    }

    public static function metadataLocations(): array
    {
        return [
            'beginning, low memory' => [false, '128M'],
            'end, low memory' => [true, '128M'],
            'beginning, high memory' => [false, '1G'],
            'end, high memory' => [true, '1G'],
            'beginning, unlimited memory' => [false, '-1'],
            'end, unlimited memory' => [true, '-1'],
        ];
    }

    #[Test]
    #[DataProvider('invalidRangeResponses')]
    public function it_rejects_invalid_s3_range_responses(int $status, string $range, string $body): void
    {
        $stream = Utils::streamFor($body);
        $asset = $this->s3Asset(100, fn () => [
            'Body' => $stream,
            'ETag' => '"original"',
            'ContentRange' => $range,
            '@metadata' => ['statusCode' => $status],
        ]);

        $extracted = (new ExtractInfo)->fromAsset($asset);

        $this->assertNotEmpty($extracted['error']);
        $this->assertArrayNotHasKey('playtime_seconds', $extracted);
        $this->assertFalse($stream->isReadable());
    }

    public static function invalidRangeResponses(): array
    {
        return [
            'ignored range' => [200, '', str_repeat('x', 100)],
            'wrong range' => [206, 'bytes 1-100/100', str_repeat('x', 100)],
            'truncated body' => [206, 'bytes 0-99/100', 'short'],
            'oversized body' => [206, 'bytes 0-99/100', str_repeat('x', 101)],
        ];
    }

    #[Test]
    public function it_rejects_an_s3_asset_that_changes_during_extraction(): void
    {
        $size = 10 * 1024 ** 3;
        $asset = $this->s3Asset($size, function (array $params) use ($size) {
            preg_match('/^bytes=(\d+)-(\d+)$/', $params['Range'], $range);
            [$start, $end] = [(int) $range[1], (int) $range[2]];

            return [
                'Body' => Utils::streamFor(str_repeat("\0", $end - $start + 1)),
                'ETag' => isset($params['IfMatch']) ? '"changed"' : '"original"',
                'ContentRange' => "bytes {$start}-{$end}/{$size}",
                '@metadata' => ['statusCode' => 206],
            ];
        });

        $extracted = (new ExtractInfo)->fromAsset($asset);

        $this->assertSame(['Caught exception: The S3 asset changed during metadata extraction.'], $extracted['error']);
    }

    #[Test]
    public function it_bounds_how_much_data_can_be_read_from_s3(): void
    {
        $size = 10 * 1024 ** 3;
        $downloadedBytesCount = 0;
        $asset = $this->s3Asset($size, function (array $params) use ($size, &$downloadedBytesCount) {
            preg_match('/^bytes=(\d+)-(\d+)$/', $params['Range'], $range);
            [$start, $end] = [(int) $range[1], (int) $range[2]];
            $downloadedBytesCount += $end - $start + 1;

            return [
                'Body' => Utils::streamFor(str_repeat("\0", $end - $start + 1)),
                'ETag' => '"original"',
                'ContentRange' => "bytes {$start}-{$end}/{$size}",
                '@metadata' => ['statusCode' => 206],
            ];
        });

        $stream = new S3MetadataStream($asset->disk()->filesystem(), $asset->path(), $size);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('S3 metadata extraction exceeded its read limit.');

        try {
            for ($i = 0; $i < 512; $i++) {
                $stream->read(1024 * 1024);
            }
        } finally {
            $stream->close();
            $this->assertLessThanOrEqual(256 * 1024 * 1024, $downloadedBytesCount);
        }
    }
}
