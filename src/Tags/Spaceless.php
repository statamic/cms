<?php

namespace Statamic\Tags;

class Spaceless extends Tags
{
    private const PROTECTED_ELEMENTS = 'script|style|pre|textarea';

    private const VOID_ELEMENTS = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';

    public function index(): string
    {
        $html = (string) $this->parse();

        // Protect whitespace-sensitive elements and comments from any changes.
        $protected = [];

        $html = preg_replace_callback(
            '/<!--.*?-->|<('.self::PROTECTED_ELEMENTS.')\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>.*?<\/\1>/is',
            function ($matches) use (&$protected) {
                $key = "\x02spaceless:".count($protected)."\x02";
                $protected[$key] = $matches[0];

                return $key;
            },
            $html
        );

        return strtr($this->collapse($html), $protected);
    }

    /**
     * Tokenize into tags, whitespace runs, and prose runs, then decide
     * per whitespace token whether to remove it or collapse it to a
     * single space.
     */
    private function collapse(string $html): string
    {
        // A single HTML tag, aware of quoted attribute values so a literal '>'
        // inside an attribute, isn't mistaken for the tag's closing bracket.
        $tag = '<\/?[a-zA-Z][^<>"\']*(?:"[^"]*"|\'[^\']*\'|[^<>"\'])*>';

        preg_match_all('/'.$tag.'/u', $html, $tagMatches, PREG_OFFSET_CAPTURE);

        $tokens = [];
        $cursor = 0;

        $pushText = function (string $text) use (&$tokens) {
            if ($text === '') {
                return;
            }

            preg_match_all('/(\s+)|(\S+)/u', $text, $pieces, PREG_SET_ORDER);

            foreach ($pieces as $piece) {
                $tokens[] = $piece[1] !== ''
                    ? ['type' => 'ws', 'value' => $piece[1]]
                    : ['type' => 'prose', 'value' => $piece[2]];
            }
        };

        foreach ($tagMatches[0] as [$tagString, $offset]) {
            $pushText(substr($html, $cursor, $offset - $cursor));

            $type = match (true) {
                str_starts_with($tagString, '</') => 'closing',
                str_ends_with($tagString, '/>') => 'void',
                (bool) preg_match('/^<('.self::VOID_ELEMENTS.')\b/i', $tagString) => 'void',
                default => 'opening',
            };

            $tokens[] = ['type' => $type, 'value' => $tagString];
            $cursor = $offset + strlen($tagString);
        }
        $pushText(substr($html, $cursor));

        $isTag = fn ($type) => in_array($type, ['opening', 'closing', 'void'], true);

        // Whether the tag at $tagIndex is, ignoring any further tags in
        // between, glued (no whitespace) to real content on the given side.
        $externallyGlued = function (int $tagIndex, int $direction) use ($tokens, $isTag) {
            $i = $tagIndex + $direction;

            while (isset($tokens[$i]) && $isTag($tokens[$i]['type'])) {
                $i += $direction;
            }

            return isset($tokens[$i]) && $tokens[$i]['type'] === 'prose';
        };

        $html = '';

        foreach ($tokens as $i => $token) {
            if ($token['type'] !== 'ws') {
                $html .= $token['value'];

                continue;
            }

            $hasNewline = str_contains($token['value'], "\n");
            $left = $tokens[$i - 1] ?? null;
            $right = $tokens[$i + 1] ?? null;

            if ($left && $left['type'] === 'opening') {
                // Whitespace right inside an opening tag: a container's own
                // padding, only kept if the tag itself is glued to content
                // outside it (e.g. `is<strong> important`).
                $keep = ! $hasNewline && $externallyGlued($i - 1, -1);
            } elseif ($right && $right['type'] === 'closing') {
                $keep = ! $hasNewline && $externallyGlued($i + 1, 1);
            } elseif ($left && $right && $left['type'] === 'prose' && $right['type'] === 'prose') {
                // Mid-sentence whitespace always separates words, even across a line break.
                $keep = true;
            } else {
                // A peer gap (e.g. between two tags, or beside a void tag):
                // keep a real space, but a line break is formatter noise.
                $keep = ! $hasNewline;
            }

            $html .= $keep ? ' ' : '';
        }

        return trim($html);
    }
}
