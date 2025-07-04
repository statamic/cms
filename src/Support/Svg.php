<?php

namespace Statamic\Support;

use Stringy\StaticStringy;

class Svg
{
    public static function withClasses(string $svg, ?string $classes = null): string
    {
        $attrs = " class=\"{$classes}\"";

        $svg = StaticStringy::collapseWhitespace($svg);

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }
}
