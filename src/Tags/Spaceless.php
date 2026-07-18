<?php

namespace Statamic\Tags;

class Spaceless extends Tags
{
    private const PROTECTED_ELEMENTS = 'script|style|pre|textarea';

    public function index(): string
    {
        $html = (string) $this->parse();

        // Protect elements whose whitespace is significant so their contents are never touched.
        $protected = [];

        $html = preg_replace_callback(
            '/<('.self::PROTECTED_ELEMENTS.')\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>.*?<\/\1>/is',
            function ($matches) use (&$protected) {
                $key = "\x02spaceless:".count($protected)."\x02";
                $protected[$key] = $matches[0];

                return $key;
            },
            $html
        );

        // A single HTML tag, aware of quoted attribute values so a literal '>'
        // inside an attribute, isn't mistaken for the tag's closing bracket.
        $tag = '<\/?[a-zA-Z][^<>"\']*(?:"[^"]*"|\'[^\']*\'|[^<>"\'])*>';

        // Collapse whitespace runs into a single space, but only within text
        // nodes — tags (and their attribute values) are left untouched.
        $html = preg_replace_callback(
            '/('.$tag.')|([^<]+)/u',
            fn ($matches) => $matches[1] !== '' ? $matches[1] : preg_replace('/\s+/', ' ', $matches[2]),
            $html
        );

        // Remove whitespace that sits directly between two tags.
        $html = preg_replace('/('.$tag.')\s+(?='.$tag.')/u', '$1', $html);

        // Trim after an opening tag / before a closing tag, but only when
        // a boundary (whitespace, tag, or string edge) exists on the other side.
        $opening = '<(?!\/)[a-zA-Z][^<>"\']*(?:"[^"]*"|\'[^\']*\'|[^<>"\'])*(?<!\/)>';
        $closing = '<\/[a-zA-Z][^<>"\']*(?:"[^"]*"|\'[^\']*\'|[^<>"\'])*>';
        $html = preg_replace('/(?:^|(?<=[\s>]))('.$opening.')\s+/u', '$1', $html);
        $html = preg_replace('/\s+('.$closing.')(?=$|[\s<])/u', '$1', $html);

        $html = trim($html);

        // Restore the protected elements exactly as they were.
        return strtr($html, $protected);
    }
}
