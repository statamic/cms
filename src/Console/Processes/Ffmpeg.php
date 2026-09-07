<?php

namespace Statamic\Console\Processes;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class Ffmpeg extends Process
{
    protected string $startTimestamp = '00:00:00';

    private static bool $binaryResolved = false;

    private static ?string $resolvedBinary = null;

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
            '-hide_banner',
            '-loglevel error',
            '-y',
            '-ss',
            escapeshellarg($this->startTimestamp),
            '-i',
            escapeshellarg($path),
            '-frames:v 1',
            '-update 1',
            escapeshellarg($output),
        ])->join(' ');
    }

    public function available(): bool
    {
        return filled($this->ffmpegBinary());
    }

    public function ffmpegBinary(): ?string
    {
        if (static::$binaryResolved) {
            return static::$resolvedBinary;
        }

        static::$binaryResolved = true;

        return static::$resolvedBinary = $this->resolveFfmpegBinary();
    }

    private function resolveFfmpegBinary(): ?string
    {
        if (! $this->procOpenAvailable()) {
            return null;
        }

        if ($binary = config('statamic.assets.ffmpeg.binary')) {
            return is_executable($binary) ? $binary : null;
        }

        $output = $this->run($this->isWindows() ? 'where ffmpeg' : 'which ffmpeg');

        // Laravel Herd doesn't inherit the user's PATH, so we need to check the Homebrew path manually
        if ($this->isMac() && ! $output) {
            $output = $this->run('command -v /opt/homebrew/bin/ffmpeg');
        }

        if (str($output)->lower()->contains('could not find files for the given')) {
            return null;
        }

        $resolved = str(StringUtilities::normalizeLineEndings(trim($output)))
            ->explode("\n")
            ->first();

        if (! filled($resolved) || ! is_executable($resolved)) {
            return null;
        }

        return $resolved;
    }

    protected function procOpenAvailable(): bool
    {
        return function_exists('proc_open');
    }

    public static function clearBinaryCache(): void
    {
        static::$binaryResolved = false;
        static::$resolvedBinary = null;
    }
}
