<?php

namespace Statamic\Support;

use Rhukster\DomSanitizer\DOMSanitizer;
use Stringy\StaticStringy;

class Svg
{
    public static function withClasses(string $svg, ?string $classes = null): string
    {
        $attrs = " class=\"{$classes}\"";

        $svg = StaticStringy::collapseWhitespace($svg);

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }

    public static function sanitize(string $svg, ?DOMSanitizer $sanitizer = null): string
    {
        $sanitizer = $sanitizer ?? new DOMSanitizer(DOMSanitizer::SVG);

        $svg = static::sanitizeStyleTags($svg);

        return $sanitizer->sanitize($svg, [
            'remove-xml-tags' => ! Str::startsWith($svg, '<?xml'),
        ]);
    }

    public static function sanitizeCss(string $css): string
    {
        // Decode all CSS escape sequences in a single pass to prevent bypass.
        // Hex escapes: \69mport -> import. Non-hex escapes: \i -> i, \@ -> @.
        $css = preg_replace_callback(
            '/\\\\(?:([0-9a-fA-F]{1,6})\s?|(.))/s',
            fn ($m) => ($m[1] !== '') ? mb_chr(hexdec($m[1]), 'UTF-8') : $m[2],
            $css
        );

        // Normalize Unicode whitespace and invisible characters to ASCII spaces
        // so they can't be used to sneak past the regex patterns below
        $css = preg_replace('/[\p{Z}\x{200B}\x{FEFF}]+/u', ' ', $css);

        // Remove @import rules entirely
        $css = preg_replace('/@import\s+[^;]+;?/i', '', $css);

        // Neutralize url() references to external resources (http, https, protocol-relative)
        $css = preg_replace('/url\s*\(\s*["\']?\s*(?:https?:|\/\/)[^)]*\)/i', 'url()', $css);

        return $css;
    }

    private static function sanitizeStyleTags(string $svg): string
    {
        return preg_replace_callback(
            '/<style([^>]*)>(.*?)<\/style>/si',
            fn ($matches) => '<style'.$matches[1].'>'.static::sanitizeCss($matches[2]).'</style>',
            $svg
        );
    }
}
