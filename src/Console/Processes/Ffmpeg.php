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

    public function extractThumbnail(string $path, string $output)
    {
        $this->run($this->buildCommand($path, $output));

        if (! file_exists($output)) {
            return null;
        }

        return $output;
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
