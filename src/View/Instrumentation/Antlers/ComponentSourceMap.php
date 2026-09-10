<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;

/**
 * @internal
 */
class ComponentSourceMap
{
    private array $replacements = [];

    public function __construct(private string $source)
    {
    }

    public function replace(int $start, int $end, string $replacement): void
    {
        $this->replacements[$start] = [$end, mb_strlen($replacement)];
    }

    public function fingerprint(): string
    {
        return hash('xxh128', $this->source);
    }

    /**
     * @return array{0: array<int, array{offset: int, line: int}>, 1: array<int, array{offset: int, line: int}>}
     */
    public function positions(array $starts, array $ends): array
    {
        $segments = $this->replacementSegments();
        $mappedStarts = $this->mapEndpoints($starts, $segments, 'authoredStart');
        $mappedEnds = $this->mapEndpoints($ends, $segments, 'authoredEnd');
        $positions = $this->authoredPositions(array_merge(array_values($mappedStarts), array_values($mappedEnds)));

        return [
            array_map(fn ($offset) => $positions[$offset], $mappedStarts),
            array_map(fn ($offset) => $positions[$offset], $mappedEnds),
        ];
    }

    /** @return array<int, array{compiledStart: int, compiledEnd: int, authoredStart: int, authoredEnd: int}> */
    private function replacementSegments(): array
    {
        ksort($this->replacements, SORT_NUMERIC);
        $segments = [];
        $delta = 0;

        foreach ($this->replacements as $start => [$end, $length]) {
            $segments[] = [
                'compiledStart' => $start + $delta,
                'compiledEnd' => $start + $delta + $length,
                'authoredStart' => $start,
                'authoredEnd' => $end,
            ];
            $delta += $length - ($end - $start);
        }

        return $segments;
    }

    /**
     * @param  int[]  $offsets
     * @param  array<int, array{compiledStart: int, compiledEnd: int, authoredStart: int, authoredEnd: int}>  $segments
     * @param  'authoredStart'|'authoredEnd'  $boundary
     * @return array<int, int> Compiled character offset => authored normalized character offset.
     */
    private function mapEndpoints(array $offsets, array $segments, string $boundary): array
    {
        sort($offsets, SORT_NUMERIC);
        $mapped = [];
        $index = 0;
        $delta = 0;

        foreach ($offsets as $offset) {
            while (isset($segments[$index]) && $offset >= $segments[$index]['compiledEnd']) {
                $delta = $segments[$index]['compiledEnd'] - $segments[$index]['authoredEnd'];
                $index++;
            }

            $original = $offset - $delta;

            // An endpoint inside generated markup belongs to the corresponding
            // edge of the authored component tag, not to an interpolated offset.
            if (isset($segments[$index]) && $offset > $segments[$index]['compiledStart']) {
                $original = $segments[$index][$boundary];
            }

            $mapped[$offset] = $original;
        }

        return $mapped;
    }

    /** @return array<int, array{offset: int, line: int}> Keyed by authored normalized character offset. */
    private function authoredPositions(array $offsets): array
    {
        [$bytes, $characters] = CharacterOffsets::normalizedToBytesAndCharacters($this->source, $offsets);
        $orderedBytes = array_values(array_unique($bytes));
        sort($orderedBytes, SORT_NUMERIC);
        $positions = [];
        $line = 1;
        $previous = 0;

        foreach ($orderedBytes as $byte) {
            $line += preg_match_all('/\r\n|\r|\n/', substr($this->source, $previous, $byte - $previous));
            $positions[$byte] = ['offset' => $characters[$byte], 'line' => $line];
            $previous = $byte;
        }

        return array_map(fn ($byte) => $positions[$byte], $bytes);
    }
}
