<?php

namespace Statamic\Tags;

class Spaceless extends Tags
{
    private const PROTECTED_ELEMENTS = 'script|style|pre|textarea';

    private const VOID_ELEMENTS = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';

    public function index(): string
    {
        $html = (string) $this->parse();

        $protected = [];

        $html = $this->protectSignificantWhitespace($html, $protected);

        return strtr($this->collapse($html), $protected);
    }

    /**
     * Replace comments and whitespace-significant elements with
     * placeholder tokens so whitespace collapsing never touches them.
     */
    private function protectSignificantWhitespace(string $html, array &$protected): string
    {
        return $this->protect(
            $html,
            '/<!--.*?-->|<('.self::PROTECTED_ELEMENTS.')\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>.*?<\/\1>/is',
            $protected
        );
    }

    private function protect(string $html, string $pattern, array &$protected): string
    {
        return preg_replace_callback($pattern, function ($matches) use (&$protected) {
            $key = "\x02spaceless:".count($protected)."\x02";
            $protected[$key] = $matches[0];

            return $key;
        }, $html);
    }

    /**
     * Tokenize into tags, whitespace runs, and prose runs, then decide
     * per whitespace token whether to remove it or collapse it to a
     * single space.
     */
    private function collapse(string $html): string
    {
        $tokens = $this->tokenize($html);

        return $this->reduceWhitespace($tokens);
    }

    private function tokenize(string $html): array
    {
        // A single HTML tag, aware of quoted attribute values so a literal '>'
        // inside an attribute, isn't mistaken for the tag's closing bracket.
        $tag = '<\/?[a-zA-Z][^<>"\']*(?:"[^"]*"|\'[^\']*\'|[^<>"\'])*>';

        preg_match_all('/'.$tag.'/u', $html, $tagMatches, PREG_OFFSET_CAPTURE);

        $tokens = [];
        $cursor = 0;

        foreach ($tagMatches[0] as [$tagString, $offset]) {
            $this->pushTextTokens(substr($html, $cursor, $offset - $cursor), $tokens);

            $type = match (true) {
                str_starts_with($tagString, '</') => 'closing',
                $this->hasNoClosingTag($tagString) => 'void',
                default => 'opening',
            };

            $tokens[] = ['type' => $type, 'value' => $tagString];
            $cursor = $offset + strlen($tagString);
        }

        $this->pushTextTokens(substr($html, $cursor), $tokens);

        return $tokens;
    }

    private function pushTextTokens(string $text, array &$tokens): void
    {
        if ($text === '') {
            return;
        }

        preg_match_all('/(\s+)|(\S+)/u', $text, $pieces, PREG_SET_ORDER);

        foreach ($pieces as $piece) {
            $tokens[] = $piece[1] !== ''
                ? ['type' => 'ws', 'value' => $piece[1]]
                : ['type' => 'prose', 'value' => $piece[2]];
        }
    }

    /**
     * Self-closed syntax or a known void element name — either way, it has
     * no separate closing tag and no interior content of its own to trim.
     */
    private function hasNoClosingTag(string $tagString): bool
    {
        // Must be this explicit lookahead, not \b — \b doesn't handle hyphens
        // correctly and would wrongly treat <link-preview> as void.
        $pattern = '/^<('.self::VOID_ELEMENTS.')(?=[\s\/>])/i';

        return str_ends_with($tagString, '/>')
            || preg_match($pattern, $tagString) === 1;
    }

    private function reduceWhitespace(array $tokens): string
    {
        $html = '';

        foreach ($tokens as $i => $token) {
            if ($token['type'] !== 'ws') {
                $html .= $token['value'];

                continue;
            }

            $html .= $this->shouldKeepWhitespace($tokens, $i) ? ' ' : '';
        }

        return trim($html);
    }

    private function shouldKeepWhitespace(array $tokens, int $i): bool
    {
        $hasNewline = str_contains($tokens[$i]['value'], "\n");
        $left = $tokens[$i - 1] ?? null;
        $right = $tokens[$i + 1] ?? null;

        if ($left && $right && $left['type'] === 'opening' && $right['type'] === 'closing') {
            // Whitespace that is a tag's entire content: padding on both
            // sides at once, only kept if the tag is glued to content on
            // both outside edges (e.g. `is<strong> </strong>important`).
            return ! $hasNewline
                && $this->externallyGlued($tokens, $i - 1, -1)
                && $this->externallyGlued($tokens, $i + 1, 1);
        }

        if ($left && $left['type'] === 'opening') {
            // Whitespace right inside an opening tag: a container's own
            // padding, only kept if the tag itself is glued to content
            // outside it (e.g. `is<strong> important`).
            return ! $hasNewline && $this->externallyGlued($tokens, $i - 1, -1);
        }

        if ($right && $right['type'] === 'closing') {
            return ! $hasNewline && $this->externallyGlued($tokens, $i + 1, 1);
        }

        if ($left && $right && $left['type'] === 'prose' && $right['type'] === 'prose') {
            // Mid-sentence whitespace always separates words, even across a line break.
            return true;
        }

        // A peer gap (e.g. between two tags, or beside a void tag):
        // keep a real space, but a line break is formatter noise.
        return ! $hasNewline;
    }

    /**
     * Whether the tag at the given index is, ignoring any further tags
     * in between, glued (no whitespace) to real content in the given direction.
     */
    private function externallyGlued(array $tokens, int $tagIndex, int $direction): bool
    {
        $i = $tagIndex + $direction;

        while (isset($tokens[$i]) && $this->isTagToken($tokens[$i]['type'])) {
            $i += $direction;
        }

        return isset($tokens[$i]) && $tokens[$i]['type'] === 'prose';
    }

    private function isTagToken(string $type): bool
    {
        return in_array($type, ['opening', 'closing', 'void'], true);
    }
}
