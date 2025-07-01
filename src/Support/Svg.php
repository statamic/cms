<?php

namespace Statamic\Support;

use Statamic\Facades\File;
use Stringy\StaticStringy;

class Svg
{
    public static function withClasses(string $svg, string $classes): string
    {
        $attrs = " class=\"{$classes}\"";

        $svg = StaticStringy::collapseWhitespace($svg);

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }
}
