<?php

namespace Statamic\Yaml;

use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Throwable;

/**
 * A faster parser for the block-style YAML that Statamic writes.
 *
 * Symfony's parser handles nested blocks recursively: for every nested block it collects
 * the block's lines and parses them again with a new parser, so each line is scanned once
 * per level of nesting. Content like Bard is deeply nested, which makes that expensive.
 *
 * This parser walks the lines once, descending into each nested block as it meets it.
 * Plain keys and the most common values are read directly. Any other key or value is
 * handed to Symfony's inline parser, so it is read exactly as Symfony would read it.
 *
 * Anything outside the subset it understands (comments, anchors, tags, multi-line flow
 * collections or plain scalars, invalid YAML, etc.) makes it give up, and the whole
 * document is parsed by Symfony instead. Errors are therefore always Symfony's.
 *
 * @internal
 */
class FastParser
{
    private const WORD = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';

    private const HEX = '0123456789abcdef-';

    /**
     * First characters of a plain value that Symfony always reads as a string,
     * apart from null, true and false.
     */
    private const PLAIN = [
        'a' => 1, 'b' => 1, 'c' => 1, 'd' => 1, 'e' => 1, 'f' => 1, 'g' => 1, 'h' => 1, 'i' => 1, 'j' => 1, 'k' => 1, 'l' => 1, 'm' => 1,
        'n' => 1, 'o' => 1, 'p' => 1, 'q' => 1, 'r' => 1, 's' => 1, 't' => 1, 'u' => 1, 'v' => 1, 'w' => 1, 'x' => 1, 'y' => 1, 'z' => 1,
        'A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 1, 'F' => 1, 'G' => 1, 'H' => 1, 'I' => 1, 'J' => 1, 'K' => 1, 'L' => 1, 'M' => 1,
        'N' => 1, 'O' => 1, 'P' => 1, 'Q' => 1, 'R' => 1, 'S' => 1, 'T' => 1, 'U' => 1, 'V' => 1, 'W' => 1, 'X' => 1, 'Y' => 1, 'Z' => 1,
        '_' => 1, '/' => 1, '(' => 1, '$' => 1, '=' => 1, ';' => 1, '^' => 1, '<' => 1,
    ];

    private const RESERVED = ['null' => null, 'true' => true, 'false' => false];

    /**
     * Deeper nesting is left to Symfony, which applies its own limit (128 levels by default).
     */
    private const MAX_DEPTH = 64;

    /**
     * Parsed keys are remembered for the life of the process. This caps how many, so
     * long-running processes (Octane, queue workers) don't grow without limit.
     */
    private const MAX_CACHED_KEYS = 2000;

    /** Raw key text => parsed key, for keys already proven safe. */
    private static array $keys = [];

    private static ?Parser $symfony = null;

    private array $lines = [];

    private int $count = 0;

    /** The current line. */
    private int $p = 0;

    /** Lines Symfony strips from the top (the "---" marker and blank lines). */
    private int $lead = 0;

    /** How many "- key: value" mappings are open. */
    private int $compact = 0;

    /** How many mappings and sequences are open. */
    private int $depth = 0;

    /** Whether the document has tabs, which are only understood inside literal blocks. */
    private bool $tabs = false;

    public function parse(string $yaml, int $flags = 0): mixed
    {
        // Flags change how values are interpreted, which we leave to Symfony.
        if ($flags === 0) {
            try {
                return (new self)->parseBlocks($yaml);
            } catch (CannotFastParse) {
                //
            }
        }

        return self::symfony()->parse($yaml, $flags);
    }

    private static function symfony(): Parser
    {
        // Symfony's parser resets its state on every parse, so one can be reused.
        return self::$symfony ??= new Parser;
    }

    private function parseBlocks(string $yaml): array
    {
        if ($yaml === '' || strpbrk($yaml, "\r\0\x0B") !== false || str_starts_with($yaml, "\xEF\xBB\xBF") || ! preg_match('//u', $yaml)) {
            $this->fail();
        }

        // In a document starting with "---", which all front matter does, Symfony strips a
        // trailing "..." as an end of document marker, even from a value like "Read more...".
        if (str_ends_with(rtrim($yaml, " \t\n"), '...')) {
            $this->fail();
        }

        $this->lead = 0;
        $this->compact = 0;
        $this->depth = 0;

        // Front matter arrives with its opening "---" line, which Symfony ignores.
        if (str_starts_with($yaml, "---\n")) {
            $yaml = substr($yaml, 4);
            $this->lead = 1;
        }

        $this->tabs = str_contains($yaml, "\t");
        $this->lines = $lines = explode("\n", $yaml);
        $this->count = $count = count($lines);

        Inline::initialize(0);

        $p = 0;

        while ($p < $count && $lines[$p] === '') {
            $p++;
        }

        $this->p = $p;
        $this->lead += $p;

        if ($p >= $count || $lines[$p][0] === ' ') {
            $this->fail();
        }

        $result = $this->isSequenceItem($lines[$p], 0)
            ? $this->parseSequence(0)
            : $this->parseMapping(0, null);

        // Anything left over (such as a less indented line) is for Symfony to explain.
        if ($this->p < $count) {
            $this->fail();
        }

        return $result;
    }

    private function fail(): never
    {
        // A new exception each time, so no document is kept alive by a stored stack trace.
        throw new CannotFastParse;
    }

    private function enter(): void
    {
        // Indentation doesn't measure depth: a key's value can be a sequence at the key's
        // own indentation, and "- key: value" adds a mapping inside a sequence item.
        if (++$this->depth > self::MAX_DEPTH) {
            $this->fail();
        }
    }

    private function isSequenceItem(string $line, int $indent): bool
    {
        return $line[$indent] === '-' && (! isset($line[$indent + 1]) || $line[$indent + 1] === ' ');
    }

    /**
     * Whether a dash at the start of the string is followed by whitespace other than a space.
     * Symfony treats a form feed or Unicode whitespace there as a separator too, so the
     * dash would start a sequence item.
     */
    private function dashWithOtherWhitespace(string $s): bool
    {
        return isset($s[1]) && $s[0] === '-'
            && ($s[1] === "\f" || $s[1] >= "\x80" && preg_match('/^-\s/u', $s));
    }

    /**
     * @param  string|null  $line  The first line, when it isn't the current line as written ("- key: value").
     */
    private function parseMapping(int $indent, ?string $line): array
    {
        $this->enter();

        $data = [];
        $keys = 0;
        $lines = $this->lines;
        $count = $this->count;
        $p = $this->p;
        $tabs = $this->tabs;

        while (true) {
            if ($line === null) {
                if ($p >= $count) {
                    break;
                }

                $line = $lines[$p];
            }

            $i = strspn($line, ' ');

            if ($i !== $indent || ! isset($line[$i])) {
                if (! isset($line[$i])) {
                    if ($i) {
                        $this->fail(); // Whitespace-only lines can change how Symfony reads what follows.
                    }

                    $p++;
                    $line = null;

                    continue;
                }

                if ($i < $indent) {
                    break;
                }

                $this->fail();
            }

            if ($tabs && str_contains($line, "\t")) {
                $this->fail();
            }

            $pos = strpos($line, ':', $i);

            if ($pos !== false && (! isset($line[$pos + 1]) || $line[$pos + 1] === ' ')
                && ($key = self::$keys[$raw = substr($line, $i, $pos - $i)] ?? $this->key($raw)) !== null
            ) {
                $value = isset($line[$pos + 2]) ? trim(substr($line, $pos + 2), ' ') : '';
            } else {
                [$key, $value] = $this->splitKey(substr($line, $i));
            }

            $p++;
            $line = null;

            if ($value === '') {
                // The value is the nested block that follows, if there is one.
                $this->p = $p;
                $data[$key] = $this->child($indent, true);
                $p = $this->p;
            } elseif (isset(self::PLAIN[$c = $value[0]])) {
                $data[$key] = strpbrk($value, ':#') !== false
                    ? $this->inline($value, $c)
                    : $this->plain($value);
            } elseif ($c === "'" && $value[-1] === "'" && isset($value[1]) && strpos($value, "'", 1) === strlen($value) - 1) {
                $data[$key] = substr($value, 1, -1);
            } elseif ($c === '|') {
                $this->p = $p;
                $data[$key] = $this->literal($value, $indent);
                $p = $this->p;
            } else {
                $data[$key] = $this->scalar($value, $c);
            }

            if (count($data) !== ++$keys) {
                $this->fail(); // A duplicate key: let Symfony report it.
            }
        }

        $this->p = $p;
        $this->depth--;

        return $data;
    }

    private function parseSequence(int $indent): array
    {
        $this->enter();

        $data = [];
        $lines = $this->lines;
        $count = $this->count;
        $p = $this->p;
        $tabs = $this->tabs;

        while ($p < $count) {
            $line = $lines[$p];
            $i = strspn($line, ' ');

            if ($i !== $indent || ! isset($line[$i])) {
                if (! isset($line[$i])) {
                    if ($i) {
                        $this->fail();
                    }

                    $p++;

                    continue;
                }

                if ($i < $indent) {
                    break;
                }

                $this->fail();
            }

            if ($line[$i] !== '-') {
                break;
            }

            if (! isset($line[$i + 1])) {
                // A dash on its own: the item is the block that follows.
                $this->p = $p + 1;
                $data[] = $this->child($indent, false);
                $p = $this->p;

                continue;
            }

            if ($line[$i + 1] !== ' ' || $tabs && str_contains($line, "\t")) {
                $this->fail();
            }

            $rest = trim(substr($line, $i + 2), ' ');

            if ($rest === '') {
                $this->p = $p + 1;
                $data[] = $this->child($indent, false);
                $p = $this->p;

                continue;
            }

            $c = $rest[0];

            if ($c === '-' && (! isset($rest[1]) || $rest[1] === ' ' || str_starts_with($rest, '---') || $this->dashWithOtherWhitespace($rest)) || $c === '.' && str_starts_with($rest, '...')) {
                $this->fail(); // "- - x", or document markers.
            }

            if (($c === "\f" || $c >= "\x80") && preg_match('/^\s/u', $rest)) {
                $this->fail(); // Symfony strips other whitespace after the dash too.
            }

            if ((str_contains($rest, ': ') || $rest[-1] === ':')
                && preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\{\[].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $rest)
            ) {
                // "- key: value" starts a mapping indented to the key, with this line as its first.
                $offset = strlen(rtrim($line, ' ')) - strlen($rest);
                $this->p = $p;
                $this->compact++;
                $data[] = $this->parseMapping($offset, str_repeat(' ', $offset).$rest);
                $this->compact--;
                $p = $this->p;

                continue;
            }

            $p++;
            $data[] = $this->scalar($rest, $c);
        }

        $this->p = $p;
        $this->depth--;

        return $data;
    }

    /**
     * The nested block that follows a key or sequence item with no value of its own.
     */
    private function child(int $indent, bool $allowUnindentedSequence): ?array
    {
        $p = $this->p;

        if ($p >= $this->count) {
            return null;
        }

        $line = $this->lines[$p];

        if ($line === '') {
            // Symfony reads an empty value followed by a blank line differently depending on
            // what comes after it (an unindented list is an error), unless only blank lines follow.
            while ($p < $this->count && $this->lines[$p] === '') {
                $p++;
            }

            if ($p < $this->count) {
                $this->fail();
            }

            $this->p = $p;

            return null;
        }

        $i = strspn($line, ' ');

        if (! isset($line[$i])) {
            $this->fail();
        }

        if ($i > $indent) {
            return $this->isSequenceItem($line, $i)
                ? $this->parseSequence($i)
                : $this->parseMapping($i, null);
        }

        // A key's value may also be a sequence at the key's own indentation.
        if ($allowUnindentedSequence && $i === $indent && $this->isSequenceItem($line, $i)) {
            return $this->parseSequence($indent);
        }

        return null;
    }

    /**
     * A plain key not seen before, or null when the line needs the full key pattern.
     */
    private function key(string $raw): string|int|null
    {
        if ($raw === '') {
            return null;
        }

        $c = $raw[0];

        // Plain words are returned as they are, unless they could be a number,
        // a sequence item or a reserved word.
        if (strspn($raw, self::WORD) === strlen($raw)) {
            if ($c !== '-' && ($c < '0' || $c > '9') && ! array_key_exists(strtolower($raw), self::RESERVED)) {
                return $this->rememberKey($raw, $raw);
            }

            if ($c === '-' && ($raw === '-' || str_starts_with($raw, '---'))) {
                return null;
            }
        }

        if (str_contains(' \'"[{!#&*?%@`|>~.', $c) || str_contains($raw, ' ') || str_starts_with($raw, '---') || $this->dashWithOtherWhitespace($raw)) {
            return null;
        }

        try {
            $key = Inline::parseScalar($raw);
        } catch (Throwable) {
            $this->fail();
        }

        // Symfony rejects other key types, and "<<" is a merge key.
        if (! is_string($key) && ! is_int($key) || $raw === '<<') {
            $this->fail();
        }

        return $this->rememberKey($raw, $key);
    }

    private function rememberKey(string $raw, string|int $key): string|int
    {
        if (count(self::$keys) >= self::MAX_CACHED_KEYS) {
            self::$keys = [];
        }

        return self::$keys[$raw] = $key;
    }

    /**
     * Split a "key: value" line the way Symfony does, for keys the fast path doesn't cover.
     */
    private function splitKey(string $line): array
    {
        $c = $line[0];

        if (str_contains('#&*!?%@`{[|>', $c)
            || $c === '-' && (! isset($line[1]) || $line[1] === ' ' || $this->dashWithOtherWhitespace($line))
            || str_starts_with($line, '---')
            || str_starts_with($line, '...')
        ) {
            $this->fail();
        }

        if (($c === '"' || $c === "'") && ! preg_match('/^(?:'.Inline::REGEX_QUOTED_STRING.') *:(?: |$)/u', $line)) {
            $this->fail();
        }

        if (! preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\[\{!].*?) *\:(?: ++(?P<value>.+))?$#u', rtrim($line, ' '), $matches)) {
            $this->fail();
        }

        if (str_contains($matches['key'], ' #') && $c !== '"' && $c !== "'") {
            $this->fail();
        }

        try {
            $key = Inline::parseScalar($matches['key']);
        } catch (Throwable) {
            $this->fail();
        }

        if (! is_string($key) && ! is_int($key) || $key === '<<') {
            $this->fail();
        }

        return [$key, $matches['value'] ?? ''];
    }

    /**
     * A plain value with no colon or hash, which Symfony reads as a string unless it's reserved.
     */
    private function plain(string $value): mixed
    {
        return isset($value[5]) || ! array_key_exists($lower = strtolower($value), self::RESERVED)
            ? $value
            : self::RESERVED[$lower];
    }

    private function scalar(string $value, string $c): mixed
    {
        $length = strlen($value);

        if ($c === "'") {
            if ($length > 1 && $value[$length - 1] === "'") {
                $inner = substr($value, 1, -1);

                if (! str_contains($inner, "'")) {
                    return $inner;
                }

                if (! str_contains(str_replace("''", '', $inner), "'")) {
                    return str_replace("''", "'", $inner);
                }
            }
        } elseif ($c >= '0' && $c <= '9') {
            if ($length < 19 && ctype_digit($value) && ($c !== '0' || $length === 1)) {
                return (int) $value;
            }

            if ($length === 36 && $value[8] === '-' && strspn($value, self::HEX) === 36) {
                return $value; // A UUID.
            }
        } elseif ($c === '"') {
            if ($length > 1 && $value[$length - 1] === '"' && strpos($value, '"', 1) === $length - 1 && ! str_contains($value, '\\')) {
                return substr($value, 1, -1);
            }
        } elseif (isset(self::PLAIN[$c]) || $c >= "\x80") {
            if (strpbrk($value, ':#') === false) {
                return $this->plain($value);
            }
        } elseif ($value === '[]' || $value === '{}') {
            return [];
        }

        return $this->inline($value, $c);
    }

    /**
     * Anything the shortcuts don't cover is read by Symfony's inline parser.
     */
    private function inline(string $value, string $c): mixed
    {
        if (str_contains('&*!|>#?', $c)
            // Symfony reads flow collections with a stricter lexer than Inline, so only the simplest are taken.
            || $c === '{' && ! preg_match('/^\{ *\}$/', $value)
            || $c === '[' && ! preg_match('/^\[ *(?:[A-Za-z0-9_.\/-]+(?: *, *[A-Za-z0-9_.\/-]+)*)? *\]$/', $value)
            // Symfony rejects some plain values containing a colon.
            || $c !== '"' && $c !== "'" && (str_contains($value, ': ') || str_ends_with($value, ':'))
        ) {
            $this->fail();
        }

        try {
            $parsed = Inline::parse($value);
        } catch (Throwable) {
            $this->fail(); // e.g. a quoted string spanning multiple lines.
        }

        if (is_object($parsed)) {
            $this->fail();
        }

        return $parsed;
    }

    /**
     * A literal block scalar. "|" and "|-" blocks are read directly; anything awkward
     * is handed to Symfony as a small document of its own.
     */
    private function literal(string $header, int $indent): string
    {
        if ($this->compact) {
            // Inside "- key: value" items, Symfony keeps or drops a block's final newline
            // depending on what follows the item.
            $this->fail();
        }

        if ($header !== '|' && $header !== '|-') {
            return $this->literalBySymfony($header, $indent);
        }

        $lines = $this->lines;
        $count = $this->count;
        $block = [];
        $blockIndent = null;
        $j = $this->p;

        while ($j < $count) {
            $line = $lines[$j];
            $i = strspn($line, ' ');

            if (! isset($line[$i])) {
                if ($line !== '') {
                    return $this->literalBySymfony($header, $indent); // Whitespace-only lines.
                }

                $block[] = '';
                $j++;

                continue;
            }

            if ($i <= $indent) {
                break;
            }

            if ($blockIndent === null) {
                $blockIndent = $i;
            } elseif ($i < $blockIndent) {
                return $this->literalBySymfony($header, $indent);
            }

            $block[] = substr($line, $blockIndent);
            $j++;
        }

        if ($blockIndent === null) {
            $this->fail(); // An empty block holds different things depending on what follows.
        }

        if ($block[0] === '') {
            return $this->literalBySymfony($header, $indent);
        }

        while (end($block) === '') {
            array_pop($block);
        }

        if ($header === '|-') {
            $this->p = $j;

            return implode("\n", $block);
        }

        if ($j >= $count) {
            return $this->literalBySymfony($header, $indent);
        }

        if ($count - $j <= $this->lead) {
            // Symfony miscounts the end of the document by the lines it stripped from the
            // top, and drops this block's final newline. Leave the document to it.
            $this->fail();
        }

        $this->p = $j;

        return implode("\n", $block)."\n";
    }

    private function literalBySymfony(string $header, int $indent): string
    {
        // Indentation indicators are written when a block's first line starts with spaces.
        if (! preg_match('/^\|[1-9]?[-+]?$/', $header)) {
            $this->fail(); // Comments, folded blocks, etc.
        }

        $lines = $this->lines;
        $count = $this->count;
        $block = ['k: '.$header];

        for ($j = $this->p; $j < $count; $j++) {
            $line = $lines[$j];
            $i = strspn($line, ' ');

            if (isset($line[$i]) && $i <= $indent) {
                break;
            }

            // Keep any spaces beyond the key's indentation, even on otherwise blank lines.
            $block[] = strlen($line) > $indent ? substr($line, $indent) : '';
        }

        if ($j < $count && $count - $j <= $this->lead && $header !== '|-') {
            $this->fail(); // See literal().
        }

        try {
            // The block's last line only ends with a newline if more lines followed it.
            $value = self::symfony()->parse(implode("\n", $block).($j < $count ? "\n" : ''))['k'];
        } catch (Throwable) {
            $this->fail();
        }

        if (! is_string($value) || trim($value, "\n") === '') {
            $this->fail();
        }

        $this->p = $j;

        return $value;
    }
}
