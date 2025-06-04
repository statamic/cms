<?php

namespace Statamic\Console\Processes;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class Ffmpeg extends Process
{
    protected string $startTimestamp = '00:00:00';

    public function startTimestamp(string $startTimestamp): static
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    public function extractThumbnail(string $path, string $outputFilePath)
    {
        $ffmpegBinary = $this->ffmpegBinary();

        if (! $ffmpegBinary) {
            return null;
        }

        $output = $this->run($this->buildCommand($ffmpegBinary, $path, $outputFilePath));

        if (! file_exists($outputFilePath)) {
            return null;
        }

        return $outputFilePath;
    }

    private function buildCommand(string $ffmpegBinary, string $path, string $output)
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
        ])
            ->join(' ');
    }

    public function ffmpegBinary()
    {
        if ($binary = config('statamic.assets.ffmpeg.binary')) {
            return $binary;
        }

        $output = $this->run($this->isWindows() ? 'where ffmpeg2' : 'which ffmpeg');

        if (str($output)->lower()->contains([
            'could not find files for the given',
        ])) {
            return null;
        }

        return str(StringUtilities::normalizeLineEndings(trim($output)))
            ->explode("\n")
            ->first();
    }
}
