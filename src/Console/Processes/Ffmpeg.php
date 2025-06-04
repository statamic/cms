<?php

namespace Statamic\Console\Processes;

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
        $this->run($this->buildCommand($path, $outputFilePath));

        if (! file_exists($outputFilePath)) {
            return null;
        }

        return $outputFilePath;
    }

    private function buildCommand(string $path, string $output)
    {
        return collect([
            escapeshellarg($this->ffmpegBinary()),
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
        return config('statamic.assets.ffmpeg.binary', 'ffmpeg');
    }
}
