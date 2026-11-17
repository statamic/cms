<?php

namespace Statamic\View\Instrumentation\Concerns;

use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\MisplacedMarkerRemover;

/** @internal */
trait RemovesMisplacedMarkers
{
    /** @param  HtmlInstrumentation[]  $instrumenters */
    public static function removeMarkers(string $html, array $instrumenters): string
    {
        $markers = static::markerPattern($html, $instrumenters);

        if ($markers === null) {
            return $html;
        }

        return preg_replace('/'.$markers.'/', '', $html) ?? $html;
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    public static function trimAroundMarkers(string $html, array $instrumenters): string
    {
        $markers = static::markerPattern($html, $instrumenters);

        if ($markers === null) {
            return trim($html);
        }

        $head = '';
        $tail = '';

        while (true) {
            $html = ltrim($html);

            if (! str_starts_with($html, '<!--') || ! preg_match('/\G(?:'.$markers.')/', $html, $match)) {
                break;
            }

            $head .= $match[0];
            $html = substr($html, strlen($match[0]));
        }

        while (true) {
            $html = rtrim($html);

            if (! str_ends_with($html, '-->') || ($start = strrpos($html, '<!--')) === false) {
                break;
            }

            if (! preg_match('/\G(?:'.$markers.')\z/', $html, $match, 0, $start)) {
                break;
            }

            $tail = $match[0].$tail;
            $html = substr($html, 0, $start);
        }

        return $head.$html.$tail;
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    public static function containsMarkers(string $html, array $instrumenters): bool
    {
        foreach ($instrumenters as $instrumenter) {
            if ($instrumenter->strippableComments()
                && str_contains($html, '<!-- '.$instrumenter->commentPrefix.':')) {
                return true;
            }

            foreach (array_keys($instrumenter->attributeLayers) as $name) {
                if (str_contains($html, ' '.$name.'="')) {
                    return true;
                }
            }

            foreach (array_keys($instrumenter->markerPatterns()) as $needle) {
                if (str_contains($html, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     * @return array{0: string, 1: array<int, array{0: int, 1: string}>}
     */
    public static function extractMarkers(string $html, array $instrumenters): array
    {
        $markers = static::markerPattern($html, $instrumenters);

        if ($markers === null || ! preg_match_all('/'.$markers.'/', $html, $matches, PREG_OFFSET_CAPTURE)) {
            return [$html, []];
        }

        $clean = '';
        $removed = [];
        $copied = 0;

        foreach ($matches[0] as [$marker, $offset]) {
            $clean .= substr($html, $copied, $offset - $copied);
            $removed[] = [strlen($clean), $marker];
            $copied = $offset + strlen($marker);
        }

        return [$clean.substr($html, $copied), $removed];
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    public static function removeMisplacedMarkers(string $html, array $instrumenters): string
    {
        $markers = static::markerPattern($html, $instrumenters);

        if ($markers === null) {
            return $html;
        }

        return MisplacedMarkerRemover::remove($html, $markers, $instrumenters);
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     * @return array{0: string[], 1: string[]}
     */
    public static function markerVocabulary(array $instrumenters): array
    {
        $commentPrefixes = [];
        $attributeNames = [];

        foreach ($instrumenters as $instrumenter) {
            if ($instrumenter->strippableComments()) {
                $commentPrefixes[] = $instrumenter->commentPrefix;
            }

            array_push($attributeNames, ...array_keys($instrumenter->attributeLayers));
        }

        return [array_values(array_unique($commentPrefixes)), array_values(array_unique($attributeNames))];
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    protected static function markerPattern(string $html, array $instrumenters): ?string
    {
        $patterns = [];
        $found = false;

        foreach ($instrumenters as $instrumenter) {
            if ($instrumenter->strippableComments()) {
                $patterns[] = MisplacedMarkerRemover::commentPattern($instrumenter->commentPrefix);
                $found = $found || str_contains($html, '<!-- '.$instrumenter->commentPrefix.':');
            }

            foreach (array_keys($instrumenter->attributeLayers) as $name) {
                $patterns[] = MisplacedMarkerRemover::attributePattern($name);
                $found = $found || str_contains($html, ' '.$name.'="');
            }

            foreach ($instrumenter->markerPatterns() as $needle => $pattern) {
                $patterns[] = $pattern;
                $found = $found || str_contains($html, $needle);
            }
        }

        if (! $found) {
            return null;
        }

        return implode('|', array_unique($patterns));
    }

    protected function strippableComments(): bool
    {
        return $this->commentsEnabled && $this->commentFactory === null;
    }

    /** @return array<string, string> */
    protected function markerPatterns(): array
    {
        return [];
    }
}
