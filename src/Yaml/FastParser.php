<?php

namespace Statamic\Yaml;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Throwable;

/**
 * A faster parser for the block-style YAML that Statamic writes.
 *
 * Symfony's parser handles nested blocks recursively: for every nested block it collects
 * the block's lines and parses them again with a new parser, so each line is scanned once
 * per level of nesting. Content like Bard is deeply nested, which makes that expensive.
 *
 * This parser walks the lines once, keeping a stack of the open mappings and sequences.
 * Simple keys and values are read directly, and everything else is handed to Symfony's
 * own inline parser, so values are interpreted exactly as Symfony would interpret them.
 *
 * Anything outside the subset it understands (comments, anchors, tags, multi-line flow
 * collections or plain scalars, invalid YAML, etc.) makes it give up, and the whole
 * document is parsed by Symfony instead. Errors are therefore always Symfony's.
 *
 * @internal
 */
class FastParser
{
    private const WORD_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';

    private const PLAIN_CHARS = self::WORD_CHARS.'./';

    /**
     * Plain scalars that Symfony gives a special meaning to, by their lowercase form.
     */
    private const RESERVED = ['null' => null, '~' => null, 'true' => true, 'false' => false];

    public function __construct(private Parser $symfony = new Parser)
    {
    }

    public function parse(string $yaml, int $flags = 0): mixed
    {
        // Flags change how values are interpreted, which we leave to Symfony.
        if ($flags === 0) {
            try {
                return $this->parseBlocks($yaml);
            } catch (CannotFastParse) {
                //
            }
        }

        return $this->symfony->parse($yaml, $flags);
    }

    private function parseBlocks(string $yaml): array
    {
        if (str_contains($yaml, "\t") || str_contains($yaml, "\r") || str_starts_with($yaml, "\xEF\xBB\xBF") || ! preg_match('//u', $yaml)) {
            throw new CannotFastParse;
        }

        $lines = explode("\n", $yaml);

        // Front matter arrives with its opening "---" line, which Symfony ignores.
        if ($lines[0] === '---') {
            array_shift($lines);
        }

        $count = count($lines);

        Inline::initialize(0);

        $root = null;
        $stack = [];

        // When a line ends a key or sequence item with no value, its value is whatever
        // nested block follows. This holds that line's indentation until we know.
        $pending = null;

        $i = 0;

        while ($i < $count) {
            $line = $lines[$i];
            $trimmed = ltrim($line, ' ');

            if ($trimmed === '') {
                $i++;

                continue;
            }

            $indent = strlen($line) - strlen($trimmed);
            $c = $trimmed[0];

            if (str_contains('#&*!?%@`{[|>', $c)
                || ($c === '"' || $c === "'") && ! $this->startsWithQuotedKey($trimmed)
                || str_starts_with($trimmed, '---')
                || str_starts_with($trimmed, '...')
            ) {
                throw new CannotFastParse;
            }

            $isSequenceItem = $c === '-' && (! isset($trimmed[1]) || $trimmed[1] === ' ');

            if ($pending !== null) {
                [$pendingIndent, $pendingIsKey] = $pending;
                $pending = null;

                // A key's value may also be a sequence at the key's own indentation.
                $isUnindentedSequence = $pendingIsKey && $indent === $pendingIndent && $isSequenceItem;

                if ($indent > $pendingIndent || $isUnindentedSequence) {
                    $stack[] = new FastParserFrame($indent, $isSequenceItem, $isUnindentedSequence);
                } else {
                    $this->setLastValue($stack, null);
                }
            }

            if ($stack === []) {
                if ($indent !== 0) {
                    throw new CannotFastParse;
                }

                $stack[] = new FastParserFrame(0, $isSequenceItem);
            }

            // Close any collections this line has moved out of.
            while (true) {
                $top = end($stack);

                if ($indent > $top->indent) {
                    // Indented without a key or sequence item opening a block.
                    throw new CannotFastParse;
                }

                if ($indent === $top->indent && ! ($top->unindentedSequence && ! $isSequenceItem)) {
                    break;
                }

                $this->close($stack, $root);

                if ($stack === []) {
                    throw new CannotFastParse;
                }
            }

            $frame = end($stack);

            if ($isSequenceItem) {
                if (! $frame->isSequence) {
                    throw new CannotFastParse;
                }

                $rest = ltrim(substr($trimmed, 1), ' ');

                if ($rest === '') {
                    $frame->data[] = null;
                    $pending = [$indent, false];
                    $i++;

                    continue;
                }

                if ($rest[0] === '-' && (! isset($rest[1]) || $rest[1] === ' ')
                    || str_starts_with($rest, '---')
                    || str_starts_with($rest, '...')
                ) {
                    throw new CannotFastParse;
                }

                if ($this->isCompactMapping($rest)) {
                    // "- key: value" starts a mapping indented to the key. Re-read the
                    // line as the first key of that mapping.
                    $frame->data[] = null;
                    $childIndent = $indent + strlen($trimmed) - strlen($rest);
                    $stack[] = new FastParserFrame($childIndent, false, compact: true);
                    $lines[$i] = str_repeat(' ', $childIndent).$rest;

                    continue;
                }

                $frame->data[] = $this->scalar($rest, $lines, $i);
                $i++;

                continue;
            }

            if ($frame->isSequence) {
                throw new CannotFastParse;
            }

            [$key, $value] = $this->splitKey($trimmed);

            if (array_key_exists($key, $frame->data)) {
                throw new CannotFastParse; // Let Symfony report the duplicate key.
            }

            $frame->lastKey = $key;

            if ($value === '') {
                $frame->data[$key] = null;
                $pending = [$indent, true];
                $i++;

                continue;
            }

            if ($value[0] === '|') {
                // Inside "- key: value" items, Symfony keeps or drops a block scalar's final
                // newline depending on what follows the item. Statamic doesn't write that
                // form, so we leave it to Symfony.
                foreach ($stack as $open) {
                    if ($open->compact) {
                        throw new CannotFastParse;
                    }
                }

                [$frame->data[$key], $i] = $this->blockScalar($value, $lines, $i, $indent);

                continue;
            }

            $frame->data[$key] = $this->scalar($value, $lines, $i);
            $i++;
        }

        if ($pending !== null) {
            $this->setLastValue($stack, null);
        }

        while ($stack !== []) {
            $this->close($stack, $root);
        }

        if (! is_array($root)) {
            throw new CannotFastParse;
        }

        return $root;
    }

    /**
     * Close the innermost collection and set it as its parent's latest value.
     */
    private function close(array &$stack, ?array &$root): void
    {
        $frame = array_pop($stack);

        if ($stack === []) {
            $root = $frame->data;

            return;
        }

        $this->setLastValue($stack, $frame->data);
    }

    private function setLastValue(array $stack, mixed $value): void
    {
        $frame = end($stack);

        $frame->data[$frame->isSequence ? array_key_last($frame->data) : $frame->lastKey] = $value;
    }

    private function startsWithQuotedKey(string $line): bool
    {
        return (bool) preg_match('/^(?:'.Inline::REGEX_QUOTED_STRING.') *:(?: |$)/u', $line);
    }

    /**
     * Whether a sequence item's value is a mapping, using the same check as Symfony.
     */
    private function isCompactMapping(string $value): bool
    {
        return (bool) preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\{\[].*?) *\:(\s+(?P<value>.+?))?\s*$#u', $value);
    }

    /**
     * Split a "key: value" line, interpreting the key the same way Symfony does.
     */
    private function splitKey(string $line): array
    {
        // Most keys are plain words, which Symfony would return as they are.
        $pos = strpos($line, ':');

        if ($pos > 0
            && (! isset($line[$pos + 1]) || $line[$pos + 1] === ' ')
            && strspn($line, self::WORD_CHARS, 0, $pos) === $pos
            && ! ctype_digit($line[0]) && $line[0] !== '-'
            && ! array_key_exists(strtolower(substr($line, 0, $pos)), self::RESERVED)
        ) {
            return [substr($line, 0, $pos), trim(substr($line, $pos + 1), ' ')];
        }

        if (! preg_match('#^(?P<key>'.Inline::REGEX_QUOTED_STRING.'|[^ \'"\[\{!].*?) *\:(?: ++(?P<value>.+))?$#u', rtrim($line, ' '), $matches)) {
            throw new CannotFastParse;
        }

        if (str_contains($matches['key'], ' #') && ! in_array($matches['key'][0], ['"', "'"], true)) {
            throw new CannotFastParse;
        }

        $key = Inline::parseScalar($matches['key']);

        // Symfony rejects other key types, and "<<" is a merge key.
        if (! is_string($key) && ! is_int($key) || $key === '<<') {
            throw new CannotFastParse;
        }

        return [$key, $matches['value'] ?? ''];
    }

    /**
     * Interpret a value that fits on a single line.
     */
    private function scalar(string $value, array $lines, int $i): mixed
    {
        $value = rtrim($value, ' ');
        $c = $value[0];
        $quoted = $c === '"' || $c === "'";

        if (str_contains('&*!|>#?', $c)
            // Symfony checks for stray characters after a flow collection, which Inline doesn't.
            || $c === '{' && ! str_ends_with($value, '}')
            || $c === '[' && ! str_ends_with($value, ']')
            // Symfony rejects some plain values containing a colon.
            || ! $quoted && (str_contains($value, ': ') || str_ends_with($value, ':'))
            // A plain value that continues onto more indented lines.
            || ! $quoted && $c !== '{' && $c !== '[' && $this->nextLineIsMoreIndented($lines, $i)
        ) {
            throw new CannotFastParse;
        }

        // Shortcuts for the most common values, where Symfony's result is certain.
        $length = strlen($value);

        if ($quoted && $length > 1 && $value[$length - 1] === $c && strpos($value, $c, 1) === $length - 1 && ($c === "'" || ! str_contains($value, '\\'))) {
            return substr($value, 1, -1);
        }

        if (array_key_exists($lower = strtolower($value), self::RESERVED)) {
            return self::RESERVED[$lower];
        }

        if (ctype_alpha($c) && strspn($value, self::PLAIN_CHARS) === $length) {
            return $value;
        }

        if ($length < 19 && ($value === '0' || preg_match('/^-?[1-9][0-9]*$/', $value))) {
            return (int) $value;
        }

        try {
            $parsed = Inline::parse($value);
        } catch (ParseException) {
            throw new CannotFastParse; // e.g. a quoted string spanning multiple lines.
        }

        if ($parsed instanceof TaggedValue || is_object($parsed)) {
            throw new CannotFastParse;
        }

        return $parsed;
    }

    private function nextLineIsMoreIndented(array $lines, int $i): bool
    {
        $indent = strlen($lines[$i]) - strlen(ltrim($lines[$i], ' '));

        for ($j = $i + 1, $count = count($lines); $j < $count; $j++) {
            $trimmed = ltrim($lines[$j], ' ');

            if ($trimmed !== '') {
                return strlen($lines[$j]) - strlen($trimmed) > $indent;
            }
        }

        return false;
    }

    /**
     * Parse a literal block scalar by handing it to Symfony as a small document of its
     * own, so that chomping and indentation follow Symfony's rules exactly.
     *
     * @return array{mixed, int} The value, and the index of the line after the block.
     */
    private function blockScalar(string $header, array $lines, int $i, int $indent): array
    {
        $header = rtrim($header, ' ');

        if ($header !== '|' && $header !== '|-' && $header !== '|+') {
            throw new CannotFastParse; // Indentation indicators, comments, etc.
        }

        $block = ['k: '.$header];
        $count = count($lines);

        for ($j = $i + 1; $j < $count; $j++) {
            $line = $lines[$j];
            $trimmed = ltrim($line, ' ');

            if ($trimmed !== '' && strlen($line) - strlen($trimmed) <= $indent) {
                break;
            }

            // Keep any spaces beyond the key's indentation, even on otherwise blank lines.
            $block[] = strlen($line) > $indent ? substr($line, $indent) : '';
        }

        try {
            // The block's last line only ends with a newline if more lines followed it.
            $value = $this->symfony->parse(implode("\n", $block).($j < $count ? "\n" : ''))['k'];
        } catch (Throwable) {
            throw new CannotFastParse;
        }

        return [$value, $j];
    }
}
