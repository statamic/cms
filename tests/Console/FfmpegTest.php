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

    private function buildCommand(...$arguments)
    {
        $method = (new \ReflectionClass(Ffmpeg::class))->getMethod('buildCommand');

        return $method->invoke(new Ffmpeg, ...$arguments);
    }
}
