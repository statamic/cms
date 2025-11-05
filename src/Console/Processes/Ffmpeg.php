<?php

namespace Statamic\Console\Processes;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class Ffmpeg extends Process
{
    protected string $startTimestamp = '00:00:00';

    public function startTimestamp(string $startTimestamp): self
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    public function extractThumbnail(string $path, string $outputFilePath): ?string
    {
        $ffmpegBinary = $this->ffmpegBinary();

        if (! $ffmpegBinary) {
            return null;
        }

        $this->run($this->buildCommand($ffmpegBinary, $path, $outputFilePath));

        if (! file_exists($outputFilePath)) {
            return null;
        }

        return $outputFilePath;
    }

    private function buildCommand(string $ffmpegBinary, string $path, string $output): string
    {
        return collect([
            escapeshellarg($ffmpegBinary),
            '-y',
            '-ss',
            escapeshellarg($this->startTimestamp),
            '-i',
            escapeshellarg($path),
            '-vframes 1',
            escapeshellarg($output),
        ])->join(' ');
    }

    public function ffmpegBinary(): ?string
    {
        if ($binary = config('statamic.assets.ffmpeg.binary')) {
            return $binary;
        }

        $output = $this->run($this->isWindows() ? 'where ffmpeg' : 'which ffmpeg');

        // Laravel Herd doesn't inherit the user's PATH, so we need to check the Homebrew path manually
        if ($this->isMac() && ! $output) {
            $output = $this->run('command -v /opt/homebrew/bin/ffmpeg');
        }

        if (str($output)->lower()->contains('could not find files for the given')) {
            return null;
        }

        return str(StringUtilities::normalizeLineEndings(trim($output)))
            ->explode("\n")
            ->first();
    }
}
