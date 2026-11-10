<?php

namespace Statamic\Yaml;

/**
 * A mapping or sequence that the fast parser is still building.
 *
 * @internal
 */
class FastParserFrame
{
    public array $data = [];

    public int|string|null $lastKey = null;

    public function __construct(
        public int $indent,
        public bool $isSequence,
        public bool $unindentedSequence = false,
        public bool $compact = false,
    ) {
    }
}
