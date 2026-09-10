<?php

namespace Statamic\View\Instrumentation;

/** @internal */
final class TemplateRegion
{
    const TYPE_SINGLE = 'single';

    const TYPE_PAIR = 'pair';

    public function __construct(
        public readonly string $engine,
        public readonly string $type,
        public readonly string $expression,
        public readonly int $line,
        public readonly int $start,
        public readonly int $end,
        public readonly int $byteStart,
        public readonly int $byteEnd,
        private readonly HtmlContext $context,
        private readonly mixed $raw,
        public readonly bool $commentSafe = false,
    ) {
    }

    public function context(): HtmlContext
    {
        return $this->context;
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function isPair(): bool
    {
        return $this->type !== self::TYPE_SINGLE;
    }
}
