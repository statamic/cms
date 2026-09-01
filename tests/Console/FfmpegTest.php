<?php

namespace Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Ffmpeg;
use Tests\TestCase;

class FfmpegTest extends TestCase
{
    public function tearDown(): void
    {
        Ffmpeg::clearBinaryCache();

        parent::tearDown();
    }

    #[Test]
    public function it_builds_a_thumbnail_command_that_only_writes_errors_to_stderr()
    {
        $command = $this->buildCommand('/usr/bin/ffmpeg', '/path/to/video.mov', '/path/to/thumb.jpg');

        $this->assertStringContainsString('-hide_banner', $command);
        $this->assertStringContainsString('-loglevel error', $command);
        $this->assertStringContainsString('-frames:v 1', $command);
        $this->assertStringContainsString('-update 1', $command);
        $this->assertStringNotContainsString('-vframes', $command);
    }

    #[Test]
    public function it_ignores_a_configured_binary_that_is_not_executable()
    {
        Ffmpeg::clearBinaryCache();
        config(['statamic.assets.ffmpeg.binary' => storage_path('missing-ffmpeg-binary')]);

        $this->assertNull((new Ffmpeg)->ffmpegBinary());
        $this->assertFalse((new Ffmpeg)->available());
    }

    #[Test]
    public function it_memoizes_binary_resolution_across_instances()
    {
        Ffmpeg::clearBinaryCache();
        config(['statamic.assets.ffmpeg.binary' => PHP_BINARY]);

        $resolved = (new Ffmpeg)->ffmpegBinary();

        config(['statamic.assets.ffmpeg.binary' => storage_path('missing-ffmpeg-binary')]);

        $this->assertSame($resolved, (new Ffmpeg)->ffmpegBinary());

        Ffmpeg::clearBinaryCache();

        $this->assertNull((new Ffmpeg)->ffmpegBinary());
    }

    #[Test]
    public function it_treats_ffmpeg_as_unavailable_when_proc_open_is_disabled()
    {
        Ffmpeg::clearBinaryCache();
        config(['statamic.assets.ffmpeg.binary' => null]);

        $ffmpeg = new class extends Ffmpeg
        {
            protected function procOpenAvailable(): bool
            {
                return false;
            }

            public function run($command, $cacheKey = null)
            {
                throw new \LogicException('The Process class relies on proc_open, which is not available on your PHP installation.');
            }
        };

        $this->assertNull($ffmpeg->ffmpegBinary());
        $this->assertFalse($ffmpeg->available());
    }

    #[Test]
    public function it_treats_ffmpeg_as_unavailable_when_proc_open_is_disabled_even_with_a_configured_binary()
    {
        Ffmpeg::clearBinaryCache();
        config(['statamic.assets.ffmpeg.binary' => PHP_BINARY]);

        $ffmpeg = new class extends Ffmpeg
        {
            protected function procOpenAvailable(): bool
            {
                return false;
            }

            public function run($command, $cacheKey = null)
            {
                throw new \LogicException('The Process class relies on proc_open, which is not available on your PHP installation.');
            }
        };

        $this->assertNull($ffmpeg->ffmpegBinary());
        $this->assertFalse($ffmpeg->available());
    }

    #[Test]
    public function it_ignores_a_path_discovered_binary_that_is_not_executable()
    {
        Ffmpeg::clearBinaryCache();
        config(['statamic.assets.ffmpeg.binary' => null]);

        $path = storage_path('non-executable-ffmpeg');
        file_put_contents($path, '');
        chmod($path, 0644);

        $ffmpeg = new class($path) extends Ffmpeg
        {
            public function __construct(private string $discoveredPath)
            {
                parent::__construct();
            }

            public function run($command, $cacheKey = null)
            {
                return $this->discoveredPath;
            }
        };

        $this->assertNull($ffmpeg->ffmpegBinary());
        $this->assertFalse($ffmpeg->available());

        @unlink($path);
    }

    private function buildCommand(...$arguments)
    {
        $method = (new \ReflectionClass(Ffmpeg::class))->getMethod('buildCommand');

        return $method->invoke(new Ffmpeg, ...$arguments);
    }
}
