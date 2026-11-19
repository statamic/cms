<?php

namespace Tests\Fakes;

use League\Flysystem\Config;
use League\Flysystem\DecoratedAdapter;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use RuntimeException;

/**
 * A non-local Flysystem adapter that tallies what a remote disk would be asked to do.
 *
 * It wraps an in-memory adapter, so code under test takes the same path it would
 * for S3 and friends, while the bytes actually pulled can be asserted on.
 */
class CountingFilesystemAdapter extends DecoratedAdapter
{
    public int $objectReads = 0;
    public int $objectBytes = 0;
    public int $metaReads = 0;
    public int $lists = 0;
    public int $heads = 0;
    public int $writes = 0;

    private ?int $objectReadsAllowed = null;

    public function __construct()
    {
        parent::__construct(new InMemoryFilesystemAdapter);
    }

    public function reset(): void
    {
        $this->objectReads = $this->objectBytes = $this->metaReads = 0;
        $this->lists = $this->heads = $this->writes = 0;
        $this->objectReadsAllowed = null;
    }

    /**
     * Throw once this many more objects have been read, to simulate a worker being killed.
     */
    public function failAfterObjectReads(?int $reads): void
    {
        $this->objectReadsAllowed = $reads;
    }

    public function counts(): array
    {
        return [
            'object_reads' => $this->objectReads,
            'object_bytes' => $this->objectBytes,
            'meta_reads' => $this->metaReads,
            'lists' => $this->lists,
            'heads' => $this->heads,
            'writes' => $this->writes,
        ];
    }

    public function read(string $path): string
    {
        if ($this->isMeta($path)) {
            $this->metaReads++;

            return parent::read($path);
        }

        $this->startObjectRead();

        $contents = parent::read($path);
        $this->objectBytes += strlen($contents);

        return $contents;
    }

    public function readStream(string $path)
    {
        if ($this->isMeta($path)) {
            $this->metaReads++;

            return parent::readStream($path);
        }

        $this->startObjectRead();

        return CountingStream::wrap(parent::readStream($path), function (int $bytes) {
            $this->objectBytes += $bytes;
        });
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $this->lists++;

        return parent::listContents($path, $deep);
    }

    public function fileExists(string $path): bool
    {
        $this->heads++;

        return parent::fileExists($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        $this->heads++;

        return parent::fileSize($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        $this->heads++;

        return parent::lastModified($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        $this->heads++;

        return parent::mimeType($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->writes++;

        parent::write($path, $contents, $config);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->writes++;

        parent::writeStream($path, $contents, $config);
    }

    /**
     * Put a file on the disk without it counting as a write.
     */
    public function seed(string $path, string $contents): void
    {
        parent::write($path, $contents, new Config);
    }

    private function startObjectRead(): void
    {
        if ($this->objectReadsAllowed !== null && $this->objectReadsAllowed-- <= 0) {
            throw new RuntimeException('Simulated worker timeout.');
        }

        $this->objectReads++;
    }

    private function isMeta(string $path): bool
    {
        return str_starts_with($path, '.meta/') || str_contains($path, '/.meta/');
    }
}
