<?php

namespace Tests\Imaging;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Imaging\Attributes;
use Tests\Fakes\CountingFilesystemAdapter;
use Tests\Fakes\ImageFixtures;
use Tests\TestCase;

class RemoteAttributesTest extends TestCase
{
    private const SIZE = 2 * 1024 * 1024;

    private CountingFilesystemAdapter $adapter;
    private FilesystemAdapter $disk;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = new CountingFilesystemAdapter;
        $this->disk = new FilesystemAdapter(new Filesystem($this->adapter), $this->adapter, []);
    }

    #[Test]
    #[DataProvider('images')]
    public function it_only_reads_the_start_of_an_image_on_a_remote_disk(string $extension, \Closure $contents, array $expected)
    {
        $this->adapter->seed("image.{$extension}", $contents());

        $attributes = (new Attributes)->from($this->disk, "image.{$extension}");

        $this->assertSame($expected, $attributes);
        $this->assertSame(1, $this->adapter->objectReads);
        $this->assertLessThan(100 * 1024, $this->adapter->objectBytes);
    }

    public static function images(): array
    {
        return [
            'jpg' => ['jpg', fn () => ImageFixtures::jpeg(30, 60, self::SIZE), ['width' => 30, 'height' => 60]],
            'png' => ['png', fn () => ImageFixtures::png(30, 60, self::SIZE), ['width' => 30, 'height' => 60]],
            'gif' => ['gif', fn () => ImageFixtures::gif(30, 60, self::SIZE), ['width' => 30, 'height' => 60]],
            'webp' => ['webp', fn () => ImageFixtures::webp(30, 60, self::SIZE), ['width' => 30, 'height' => 60]],
            'rotated jpg' => ['jpg', fn () => ImageFixtures::jpeg(30, 60, self::SIZE, orientation: 6), ['width' => 60, 'height' => 30]],
            'flipped jpg' => ['jpg', fn () => ImageFixtures::jpeg(30, 60, self::SIZE, orientation: 3), ['width' => 30, 'height' => 60]],
        ];
    }

    #[Test]
    public function it_reads_further_when_metadata_comes_before_the_dimensions()
    {
        $this->adapter->seed('image.jpg', ImageFixtures::jpeg(30, 60, self::SIZE, orientation: 6, headerPadding: 300 * 1024));

        $attributes = (new Attributes)->from($this->disk, 'image.jpg');

        $this->assertSame(['width' => 60, 'height' => 30], $attributes);
        $this->assertSame(1, $this->adapter->objectReads);
        $this->assertLessThan(600 * 1024, $this->adapter->objectBytes);
    }

    #[Test]
    public function it_falls_back_to_copying_the_file_when_the_dimensions_are_too_far_in()
    {
        $size = 4 * 1024 * 1024;

        $this->adapter->seed('image.jpg', ImageFixtures::jpeg(30, 60, $size, headerPadding: 3 * 1024 * 1024));

        $attributes = (new Attributes)->from($this->disk, 'image.jpg');

        $this->assertSame(['width' => 30, 'height' => 60], $attributes);
        $this->assertSame(2, $this->adapter->objectReads);
        $this->assertGreaterThanOrEqual($size, $this->adapter->objectBytes);
    }

    #[Test]
    #[DataProvider('unreadableImages')]
    public function it_has_no_dimensions_for_a_small_file_that_is_not_an_image(int $size)
    {
        $this->adapter->seed('image.jpg', ImageFixtures::junk($size));

        $attributes = (new Attributes)->from($this->disk, 'image.jpg');

        $this->assertSame(['width' => 0, 'height' => 0], $attributes);
        $this->assertSame(1, $this->adapter->objectReads);
    }

    public static function unreadableImages(): array
    {
        return ['empty' => [0], 'junk' => [10 * 1024]];
    }

    #[Test]
    public function it_still_copies_svgs()
    {
        $this->adapter->seed('image.svg', '<svg width="30" height="60" xmlns="http://www.w3.org/2000/svg"></svg>');

        $attributes = (new Attributes)->from($this->disk, 'image.svg');

        $this->assertSame(['width' => 30.0, 'height' => 60.0], $attributes);
    }

    #[Test]
    public function it_requests_a_range_from_s3()
    {
        $image = ImageFixtures::jpeg(30, 60, self::SIZE, orientation: 6);
        $ranges = [];

        $disk = $this->s3Disk($image, function (array $params) use ($image, &$ranges) {
            $this->assertSame('assets', $params['Bucket']);
            $this->assertSame('uploads/image.jpg', $params['Key']);

            preg_match('/^bytes=(\d+)-(\d+)$/', $ranges[] = $params['Range'], $range);
            [$start, $end] = [(int) $range[1], (int) $range[2]];

            return [
                'Body' => Utils::streamFor(substr($image, $start, $end - $start + 1)),
                'ETag' => '"original"',
                'ContentRange' => "bytes {$start}-{$end}/".strlen($image),
                '@metadata' => ['statusCode' => 206],
            ];
        });

        $attributes = (new Attributes)->from($disk, 'image.jpg');

        $this->assertSame(['width' => 60, 'height' => 30], $attributes);
        $this->assertSame(['bytes=0-262143'], $ranges);
    }

    #[Test]
    public function it_falls_back_to_copying_the_file_when_s3_ignores_the_range()
    {
        $image = ImageFixtures::jpeg(30, 60, 300 * 1024);

        $disk = $this->s3Disk($image, fn () => [
            'Body' => Utils::streamFor($image),
            'ETag' => '"original"',
            '@metadata' => ['statusCode' => 200],
        ]);

        $driver = new Filesystem($adapter = new CountingFilesystemAdapter);
        $adapter->seed('image.jpg', $image);
        $disk->shouldReceive('getDriver')->andReturn($driver);

        $attributes = (new Attributes)->from($disk, 'image.jpg');

        $this->assertSame(['width' => 30, 'height' => 60], $attributes);
        $this->assertSame(1, $adapter->objectReads);
    }

    private function s3Disk(string $contents, callable $getObject)
    {
        $client = Mockery::mock();
        $client->shouldReceive('getObject')->andReturnUsing($getObject);

        $disk = Mockery::mock(AwsS3V3Adapter::class);
        $disk->shouldReceive('getAdapter')->andReturn(new CountingFilesystemAdapter);
        $disk->shouldReceive('size')->with('image.jpg')->andReturn(strlen($contents));
        $disk->shouldReceive('path')->with('image.jpg')->andReturn('uploads/image.jpg');
        $disk->shouldReceive('getClient')->andReturn($client);
        $disk->shouldReceive('getConfig')->andReturn(['bucket' => 'assets']);

        return $disk;
    }
}
