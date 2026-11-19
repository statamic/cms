<?php

namespace Statamic\Assets;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class S3MetadataStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private const CHUNK_SIZE = 2 * 1024 * 1024;

    private StreamInterface $stream;
    private int $position = 0;
    private int $bufferStart = 0;
    private int $requestsCount = 0;
    private ?string $etag = null;
    private float $deadline;

    public function __construct(private AwsS3V3Adapter $disk, private string $path, private int $size)
    {
        $this->deadline = microtime(true) + 30;
        $this->stream = Utils::streamFor('');
    }

    public function read($length): string
    {
        if (! $this->isReadable()) {
            throw new RuntimeException('Cannot read a closed S3 stream.');
        }

        if ($length < 0) {
            throw new RuntimeException('Read length must not be negative.');
        }

        if ($length === 0 || $this->eof()) {
            return '';
        }

        if ($this->position < $this->bufferStart || $this->position >= $this->bufferStart + $this->stream->getSize()) {
            $this->readRange();
        }

        $this->stream->seek($this->position - $this->bufferStart);
        $data = $this->stream->read($length);
        $this->position += strlen($data);

        return $data;
    }

    private function readRange(): void
    {
        $timeout = $this->deadline - microtime(true);

        if (++$this->requestsCount > 128 || $timeout <= 0) {
            throw new RuntimeException('S3 metadata extraction exceeded its read limit.');
        }

        $start = intdiv($this->position, self::CHUNK_SIZE) * self::CHUNK_SIZE;
        $end = min($start + self::CHUNK_SIZE, $this->size) - 1;
        $config = $this->disk->getConfig();
        $options = $config['options'] ?? [];

        $params = array_merge($options, [
            'Bucket' => $config['bucket'],
            'Key' => $this->disk->path($this->path),
            'Range' => "bytes={$start}-{$end}",
            '@retries' => 0,
            '@http' => array_merge($options['@http'] ?? [], [
                'stream' => true,
                'timeout' => min(10, $timeout),
                'read_timeout' => min(10, $timeout),
            ]),
        ]);

        if ($this->etag !== null) {
            $params['IfMatch'] = $this->etag;
        }

        $result = $this->disk->getClient()->getObject($params);

        try {
            if (($result['@metadata']['statusCode'] ?? null) !== 206
                || ($result['ContentRange'] ?? null) !== "bytes {$start}-{$end}/{$this->size}") {
                throw new RuntimeException('S3 did not return the requested byte range.');
            }

            if ($this->etag !== null && $result['ETag'] !== $this->etag) {
                throw new RuntimeException('The S3 asset changed during metadata extraction.');
            }

            $data = '';
            $length = $end - $start + 1;

            while (! $result['Body']->eof() && strlen($data) <= $length) {
                if (microtime(true) >= $this->deadline) {
                    throw new RuntimeException('S3 metadata extraction exceeded its read limit.');
                }

                $chunk = $result['Body']->read(min(8192, $length - strlen($data) + 1));

                if ($chunk === '') {
                    break;
                }

                $data .= $chunk;
            }

            if (strlen($data) !== $length) {
                throw new RuntimeException('S3 returned an incomplete or oversized byte range.');
            }

            $this->etag = $result['ETag'];
            $this->bufferStart = $start;
            $this->stream->close();
            $this->stream = Utils::streamFor($data);
        } finally {
            $result['Body']->close();
        }
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (! $this->isReadable()) {
            throw new RuntimeException('Cannot seek a closed S3 stream.');
        }

        $position = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $this->size + $offset,
            default => throw new RuntimeException('Invalid seek mode.'),
        };

        if ($position < 0) {
            throw new RuntimeException('Cannot seek before the beginning of the S3 stream.');
        }

        $this->position = $position;
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= $this->size;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException('The S3 stream is read-only.');
    }

    public function detach(): mixed
    {
        $this->close();

        return null;
    }
}
