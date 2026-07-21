<?php

namespace Tests\Forms;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;

/**
 * A minimal Flysystem adapter that stores file contents purely in memory,
 * simulating a "cloud" disk where path() cannot return a real local path.
 */
class InMemoryFlysystemAdapter implements FilesystemAdapter
{
    private array $files = [];

    public function fileExists(string $path): bool
    {
        return isset($this->files[$path]);
    }

    public function directoryExists(string $path): bool
    {
        return false;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->files[$path] = $contents;
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->files[$path] = stream_get_contents($contents);
    }

    public function read(string $path): string
    {
        if (! $this->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        return $this->files[$path];
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        unset($this->files[$path]);
    }

    public function deleteDirectory(string $path): void
    {
        //
    }

    public function createDirectory(string $path, Config $config): void
    {
        //
    }

    public function setVisibility(string $path, string $visibility): void
    {
        //
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path, mimeType: 'image/jpeg');
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path, lastModified: time());
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path, strlen($this->files[$path] ?? ''));
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return [];
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->files[$destination] = $this->files[$source] ?? '';
        unset($this->files[$source]);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->files[$destination] = $this->files[$source] ?? '';
    }
}
