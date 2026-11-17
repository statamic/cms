<?php

namespace Statamic\View\Instrumentation;

/** @internal */
final class TagBody
{
    /**
     * @param  array<int, array{0: int, 1: string}>  $markers
     * @param  HtmlInstrumentation[]  $instrumenters
     */
    private function __construct(
        public readonly string $instrumented,
        public readonly string $clean,
        public readonly bool $composesOutput,
        private readonly array $markers,
        private readonly array $instrumenters,
    ) {
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     */
    public static function from(string $instrumented, array $instrumenters, bool $composesOutput = false): self
    {
        [$clean, $markers] = HtmlInstrumentation::extractMarkers($instrumented, $instrumenters);

        return new self($instrumented, $clean, $composesOutput, $markers, $instrumenters);
    }

    public function align(string $text): ?string
    {
        if ($text === '' || $this->clean === '') {
            return null;
        }

        if ($text === $this->clean) {
            return $this->instrumented;
        }

        if (HtmlInstrumentation::containsMarkers($text, $this->instrumenters)) {
            return $text;
        }

        if (($at = strpos($text, $this->clean)) !== false) {
            return strrpos($text, $this->clean) === $at ? $this->insert($text, $at, 0, strlen($this->clean)) : $text;
        }

        if (($at = strpos($this->clean, $text)) !== false) {
            return strrpos($this->clean, $text) === $at ? $this->insert($text, -$at, $at, $at + strlen($text)) : $text;
        }

        return null;
    }

    private function insert(string $text, int $shift, int $from, int $to): string
    {
        $result = '';
        $copied = 0;

        foreach ($this->markersWithin($from, $to) as [$offset, $marker]) {
            $target = $offset + $shift;
            $result .= substr($text, $copied, $target - $copied).$marker;
            $copied = $target;
        }

        return $result.substr($text, $copied);
    }

    /** @return array<int, array{0: int, 1: string}> */
    private function markersWithin(int $from, int $to): array
    {
        $kept = [];
        $open = [];

        foreach ($this->markers as $index => [$offset, $marker]) {
            if ($offset < $from || $offset > $to) {
                continue;
            }

            if (! str_starts_with($marker, '<!--')) {
                if ($offset > $from && $offset < $to) {
                    $kept[$index] = true;
                }

                continue;
            }

            if (str_contains($marker, ':start ')) {
                if ($offset < $to) {
                    $open[] = $index;
                }

                continue;
            }

            if ($offset > $from && $open !== []) {
                $kept[array_pop($open)] = true;
                $kept[$index] = true;
            }
        }

        return array_values(array_intersect_key($this->markers, $kept));
    }
}
