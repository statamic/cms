<?php

namespace Tests\View\Instrumentation\Concerns;

use Statamic\View\Instrumentation\HtmlInstrumentation;

trait DecodesMarkers
{
    protected function markerExpressions(string $html, string $prefix = 'antlers'): array
    {
        return array_column($this->decodedMarkers($html, $prefix), 'expression');
    }

    protected function decodedMarkers(string $html, string $prefix = 'antlers'): array
    {
        preg_match_all('/<!-- '.preg_quote($prefix, '/').':start (.*?) -->/', $html, $matches);

        return array_map([HtmlInstrumentation::class, 'decode'], $matches[1]);
    }
}
