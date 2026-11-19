<?php

namespace Tests\Fakes;

use Closure;

/**
 * A stream wrapper that reports how many bytes were actually consumed from a stream,
 * rather than how many were available.
 */
class CountingStream
{
    private const PROTOCOL = 'statamic-counting';

    /** @var resource */
    public $context;

    private $stream;
    private Closure $onRead;

    /**
     * @param  resource  $stream
     * @return resource
     */
    public static function wrap($stream, Closure $onRead)
    {
        if (! in_array(self::PROTOCOL, stream_get_wrappers())) {
            stream_wrapper_register(self::PROTOCOL, self::class);
        }

        return fopen(self::PROTOCOL.'://stream', 'r', false, stream_context_create([
            self::PROTOCOL => ['stream' => $stream, 'onRead' => $onRead],
        ]));
    }

    public function stream_open(): bool
    {
        $options = stream_context_get_options($this->context)[self::PROTOCOL];

        $this->stream = $options['stream'];
        $this->onRead = $options['onRead'];

        return true;
    }

    public function stream_read(int $count): string
    {
        $data = (string) fread($this->stream, $count);

        ($this->onRead)(strlen($data));

        return $data;
    }

    public function stream_eof(): bool
    {
        return feof($this->stream);
    }

    public function stream_tell(): int
    {
        return ftell($this->stream);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return fseek($this->stream, $offset, $whence) === 0;
    }

    public function stream_stat(): array
    {
        return fstat($this->stream) ?: [];
    }

    public function stream_close(): void
    {
        fclose($this->stream);
    }
}
