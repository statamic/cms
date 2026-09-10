<?php

namespace Statamic\View\Instrumentation;

/** @internal */
class HtmlSpec
{
    /**
     * @param  string  $source
     * @return bool
     */
    public static function hasExplicitDocumentStructure($source)
    {
        return preg_match('/<(?:!doctype[\s>]|html[\s>\/]|head[\s>\/]|body[\s>\/])/i', $source) === 1;
    }

    const VOID_ELEMENTS = [
        'area' => true, 'base' => true, 'br' => true, 'col' => true,
        'embed' => true, 'hr' => true, 'img' => true, 'input' => true,
        'link' => true, 'meta' => true, 'source' => true, 'track' => true,
        'wbr' => true,
        'basefont' => true, 'bgsound' => true, 'frame' => true,
        'keygen' => true, 'param' => true,
    ];

    const RAW_TEXT_ELEMENTS = [
        'script' => true, 'style' => true, 'textarea' => true,
        'title' => true, 'iframe' => true, 'noembed' => true,
        'noframes' => true, 'noscript' => true, 'xmp' => true,
    ];

    const COMMENT_UNSAFE_ELEMENTS = self::RAW_TEXT_ELEMENTS + [
        'pre' => true, 'listing' => true, 'plaintext' => true,
    ];

    /**
     * @param  string  $char
     * @return bool
     */
    public static function isHtmlSpace($char)
    {
        return $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r" || $char === "\f";
    }

    public static function isTagNameDelimiter($char)
    {
        return $char === null || self::isHtmlSpace($char) || $char === '>' || $char === '/';
    }

    public static function isAttributeNameDelimiter($char)
    {
        return self::isTagNameDelimiter($char) || $char === '=';
    }

    /**
     * @param  mixed  $name
     * @return bool
     */
    public static function isValidName($name)
    {
        if (! is_string($name)) {
            return false;
        }

        if ($name === '') {
            return false;
        }

        if (! mb_check_encoding($name, 'UTF-8')) {
            return false;
        }

        return ! preg_match('/[\x00-\x20\x7F"\'<>\/=]/', $name);
    }

    /**
     * @param  string|null  $char
     * @return bool
     */
    public static function isAsciiAlpha($char)
    {
        if ($char === null) {
            return false;
        }

        if ($char >= 'A' && $char <= 'Z') {
            return true;
        }

        return $char >= 'a' && $char <= 'z';
    }

    /**
     * @param  mixed  $value
     * @param  array<int, array{0: string, 1: string}>  $dynamicDelimiters
     * @return string[]
     */
    public static function classList($value, array $dynamicDelimiters = [['{{', '}}']])
    {
        $value = (string) $value;
        $classes = [];
        $buffer = '';
        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            foreach ($dynamicDelimiters as [$opening, $closing]) {
                $openingLength = strlen($opening);

                if ($openingLength === 0 || substr($value, $index, $openingLength) !== $opening) {
                    continue;
                }

                $end = strpos($value, $closing, $index + $openingLength);

                if ($end === false) {
                    $stop = $length;
                } else {
                    $stop = $end + strlen($closing);
                }

                $buffer .= substr($value, $index, $stop - $index);
                $index = $stop - 1;

                continue 2;
            }

            $char = $value[$index];

            if (self::isHtmlSpace($char)) {
                if ($buffer !== '') {
                    $classes[] = $buffer;
                    $buffer = '';
                }

                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $classes[] = $buffer;
        }

        return $classes;
    }

    const P_CLOSING_ELEMENTS = [
        'address' => true, 'article' => true, 'aside' => true,
        'blockquote' => true, 'center' => true, 'details' => true,
        'dialog' => true, 'dir' => true, 'div' => true, 'dl' => true,
        'fieldset' => true, 'figcaption' => true, 'figure' => true,
        'footer' => true, 'form' => true, 'h1' => true,
        'h2' => true, 'h3' => true, 'h4' => true, 'h5' => true,
        'h6' => true, 'header' => true, 'hgroup' => true, 'hr' => true,
        'listing' => true, 'main' => true, 'menu' => true, 'nav' => true,
        'ol' => true, 'p' => true, 'plaintext' => true, 'pre' => true,
        'search' => true, 'section' => true, 'summary' => true,
        'table' => true, 'ul' => true, 'xmp' => true, 'li' => true,
        'dt' => true, 'dd' => true,
    ];

    const IMPLIED_CLOSE_TARGETS = [
        'li' => ['li' => true],
        'dt' => ['dt' => true, 'dd' => true],
        'dd' => ['dt' => true, 'dd' => true],
        'option' => ['option' => true],
        'optgroup' => ['option' => true, 'optgroup' => true],
        'tr' => ['tr' => true],
        'td' => ['td' => true, 'th' => true],
        'th' => ['td' => true, 'th' => true],
        'thead' => ['thead' => true, 'tbody' => true, 'tfoot' => true],
        'tbody' => ['thead' => true, 'tbody' => true, 'tfoot' => true],
        'tfoot' => ['thead' => true, 'tbody' => true, 'tfoot' => true],
        'caption' => ['caption' => true],
        'colgroup' => ['colgroup' => true],
        'rb' => ['rb' => true, 'rp' => true, 'rt' => true, 'rtc' => true],
        'rp' => ['rb' => true, 'rp' => true, 'rt' => true, 'rtc' => true],
        'rt' => ['rb' => true, 'rp' => true, 'rt' => true, 'rtc' => true],
        'rtc' => ['rb' => true, 'rp' => true, 'rt' => true, 'rtc' => true],
    ];

    const SCOPE_BOUNDARIES = [
        'applet' => true, 'caption' => true, 'html' => true,
        'table' => true, 'td' => true, 'th' => true, 'marquee' => true,
        'object' => true, 'template' => true,
    ];

    const LIST_ITEM_SCOPE_BOUNDARIES = [
        'applet' => true, 'caption' => true, 'html' => true,
        'table' => true, 'td' => true, 'th' => true, 'marquee' => true,
        'object' => true, 'template' => true, 'ol' => true, 'ul' => true,
    ];

    const BUTTON_SCOPE_BOUNDARIES = [
        'applet' => true, 'caption' => true, 'html' => true,
        'table' => true, 'td' => true, 'th' => true, 'marquee' => true,
        'object' => true, 'template' => true, 'button' => true,
    ];

    const TABLE_SCOPE_BOUNDARIES = [
        'html' => true, 'table' => true, 'template' => true,
    ];

    const TABLE_SCOPED_STARTS = [
        'thead' => true, 'tbody' => true, 'tfoot' => true,
        'caption' => true, 'colgroup' => true, 'tr' => true, 'td' => true,
        'th' => true,
    ];

    const TABLE_STRUCTURE_STARTS = [
        'caption' => true, 'col' => true, 'colgroup' => true,
        'tbody' => true, 'td' => true, 'tfoot' => true, 'th' => true,
        'thead' => true, 'tr' => true,
    ];

    const TABLE_SECTIONS = [
        'thead' => true, 'tbody' => true, 'tfoot' => true,
    ];

    const TABLE_STRUCTURAL_ELEMENTS = [
        'caption' => true, 'colgroup' => true, 'thead' => true,
        'tbody' => true, 'tfoot' => true,
    ];

    const ALLOWED_IN_TABLE = [
        'caption' => true, 'colgroup' => true, 'thead' => true,
        'tbody' => true, 'tfoot' => true, 'style' => true, 'script' => true,
        'template' => true,
    ];

    const SELECT_TABLE_ELEMENTS = [
        'caption' => true, 'table' => true, 'tbody' => true,
        'tfoot' => true, 'thead' => true, 'tr' => true, 'td' => true,
        'th' => true,
    ];

    const HEADING_ELEMENTS = [
        'h1' => true, 'h2' => true, 'h3' => true, 'h4' => true,
        'h5' => true, 'h6' => true,
    ];

    const SCOPED_END_TAGS = [
        'address' => true, 'article' => true, 'aside' => true,
        'blockquote' => true, 'button' => true, 'center' => true,
        'details' => true, 'dialog' => true, 'dir' => true, 'div' => true,
        'dl' => true, 'fieldset' => true, 'figcaption' => true,
        'figure' => true, 'footer' => true, 'header' => true,
        'hgroup' => true, 'listing' => true, 'main' => true, 'menu' => true,
        'nav' => true, 'ol' => true, 'pre' => true, 'search' => true,
        'section' => true, 'summary' => true, 'ul' => true,
        'applet' => true, 'marquee' => true, 'object' => true,
    ];

    const FORMATTING_ELEMENTS = [
        'a' => true, 'b' => true, 'big' => true, 'code' => true,
        'em' => true, 'font' => true, 'i' => true, 'nobr' => true,
        's' => true, 'small' => true, 'strike' => true, 'strong' => true,
        'tt' => true, 'u' => true,
    ];

    const FORMATTING_MARKER_ELEMENTS = [
        'applet' => true, 'caption' => true, 'marquee' => true,
        'object' => true, 'td' => true, 'th' => true, 'template' => true,
    ];

    const FORMATTING_BREAK_STARTS = [
        'address' => true, 'applet' => true, 'article' => true,
        'aside' => true, 'blockquote' => true, 'button' => true,
        'base' => true, 'basefont' => true, 'bgsound' => true,
        'caption' => true, 'center' => true, 'col' => true,
        'colgroup' => true, 'dd' => true, 'details' => true, 'dir' => true,
        'div' => true, 'dl' => true, 'dt' => true, 'fieldset' => true,
        'figcaption' => true, 'figure' => true, 'footer' => true,
        'form' => true, 'frame' => true, 'h1' => true, 'h2' => true, 'h3' => true,
        'h4' => true, 'h5' => true, 'h6' => true, 'header' => true,
        'hgroup' => true, 'hr' => true, 'li' => true, 'link' => true,
        'listing' => true, 'main' => true, 'marquee' => true, 'menu' => true,
        'meta' => true, 'nav' => true, 'object' => true, 'ol' => true,
        'p' => true, 'param' => true, 'plaintext' => true,
        'pre' => true, 'search' => true, 'section' => true,
        'select' => true, 'source' => true, 'table' => true, 'tbody' => true,
        'td' => true, 'tfoot' => true, 'th' => true, 'thead' => true,
        'template' => true, 'tr' => true, 'track' => true, 'ul' => true,
    ];

    const SVG_HTML_INTEGRATION_POINTS = [
        'foreignobject' => true, 'desc' => true, 'title' => true,
    ];

    const SVG_TAG_NAMES = [
        'altglyph' => 'altGlyph', 'altglyphdef' => 'altGlyphDef',
        'altglyphitem' => 'altGlyphItem', 'animatecolor' => 'animateColor',
        'animatemotion' => 'animateMotion', 'animatetransform' => 'animateTransform',
        'clippath' => 'clipPath', 'feblend' => 'feBlend',
        'fecolormatrix' => 'feColorMatrix', 'fecomponenttransfer' => 'feComponentTransfer',
        'fecomposite' => 'feComposite', 'feconvolvematrix' => 'feConvolveMatrix',
        'fediffuselighting' => 'feDiffuseLighting', 'fedisplacementmap' => 'feDisplacementMap',
        'fedistantlight' => 'feDistantLight', 'fedropshadow' => 'feDropShadow',
        'feflood' => 'feFlood', 'fefunca' => 'feFuncA', 'fefuncb' => 'feFuncB',
        'fefuncg' => 'feFuncG', 'fefuncr' => 'feFuncR',
        'fegaussianblur' => 'feGaussianBlur', 'feimage' => 'feImage',
        'femerge' => 'feMerge', 'femergenode' => 'feMergeNode',
        'femorphology' => 'feMorphology', 'feoffset' => 'feOffset',
        'fepointlight' => 'fePointLight', 'fespecularlighting' => 'feSpecularLighting',
        'fespotlight' => 'feSpotLight', 'fetile' => 'feTile',
        'feturbulence' => 'feTurbulence', 'foreignobject' => 'foreignObject',
        'glyphref' => 'glyphRef', 'lineargradient' => 'linearGradient',
        'radialgradient' => 'radialGradient', 'textpath' => 'textPath',
    ];

    const MATHML_TEXT_INTEGRATION_POINTS = [
        'mi' => true, 'mo' => true, 'mn' => true, 'ms' => true,
        'mtext' => true,
    ];

    const FOREIGN_BREAKOUT_ELEMENTS = [
        'b' => true, 'big' => true, 'blockquote' => true, 'body' => true,
        'br' => true, 'center' => true, 'code' => true, 'dd' => true,
        'div' => true, 'dl' => true, 'dt' => true, 'em' => true,
        'embed' => true, 'h1' => true, 'h2' => true, 'h3' => true,
        'h4' => true, 'h5' => true, 'h6' => true, 'head' => true,
        'hr' => true, 'i' => true, 'img' => true, 'li' => true,
        'listing' => true, 'menu' => true, 'meta' => true, 'nobr' => true,
        'ol' => true, 'p' => true, 'pre' => true, 'ruby' => true,
        's' => true, 'small' => true, 'span' => true, 'strong' => true,
        'strike' => true, 'sub' => true, 'sup' => true, 'table' => true,
        'tt' => true, 'u' => true, 'ul' => true, 'var' => true,
    ];
}
