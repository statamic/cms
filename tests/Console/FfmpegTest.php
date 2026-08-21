<?php

namespace Tests\Console;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Ffmpeg;
use Tests\TestCase;

class FfmpegTest extends TestCase
{
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

    private function buildCommand(...$arguments)
    {
        $method = (new \ReflectionClass(Ffmpeg::class))->getMethod('buildCommand');

        return $method->invoke(new Ffmpeg, ...$arguments);
    }
}
