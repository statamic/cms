<?php

namespace Statamic\View\Instrumentation;

/** @internal */
final class OutputContext
{
    private const DATA = 0;

    private const TAG = 1;

    private const QUOTED_VALUE = 2;

    private const UNQUOTED_VALUE = 3;

    private const COMMENT = 4;

    private const BOGUS_COMMENT = 5;

    private const CDATA = 6;

    private const RAW_TEXT = 7;

    private const PLAINTEXT = 8;

    private const SPACE = " \t\n\r\f";

    private int $state = self::DATA;

    private int $position = 0;

    private int $rewind = 0;

    private ?int $rewindState = null;

    private string $quote = '';

    private bool $expectsValue = false;

    private bool $closingTag = false;

    private string $tagName = '';

    private int $unsafeDepth = 0;

    public function length(): int
    {
        return $this->position;
    }

    public function isCommentSafe(): bool
    {
        return $this->state === self::DATA && $this->unsafeDepth === 0;
    }

    public function advance(string $html): void
    {
        $length = strlen($html);
        $index = $this->position;
        $this->rewind = 0;

        if ($this->rewindState !== null) {
            $this->state = $this->rewindState;
            $this->rewindState = null;
        }

        while ($index < $length) {
            switch ($this->state) {
                case self::DATA:
                    $next = strpos($html, '<', $index);
                    $index = $next === false ? $length : $this->openMarkup($html, $next, $length);
                    break;
                case self::TAG:
                    $index = $this->scanTag($html, $index, $length);
                    break;
                case self::QUOTED_VALUE:
                    $close = strpos($html, $this->quote, $index);

                    if ($close === false) {
                        $index = $length;
                    } else {
                        $index = $close + 1;
                        $this->state = self::TAG;
                    }
                    break;
                case self::UNQUOTED_VALUE:
                    $index += strcspn($html, self::SPACE.'>', $index, $length - $index);

                    if ($index < $length) {
                        $this->state = self::TAG;
                    }
                    break;
                case self::COMMENT:
                    $index = $this->scanTo($html, $index, $length, '/--!?>/', 3);
                    break;
                case self::BOGUS_COMMENT:
                    $index = $this->scanTo($html, $index, $length, '/>/', 0);
                    break;
                case self::CDATA:
                    $index = $this->scanTo($html, $index, $length, '/\]\]>/', 2);
                    break;
                case self::RAW_TEXT:
                    $index = $this->scanRawText($html, $index, $length);
                    break;
                default:
                    $index = $length;
                    break;
            }
        }

        $this->position = max($this->position, $length - $this->rewind);
    }

    private function openMarkup(string $html, int $index, int $length): int
    {
        $next = $html[$index + 1] ?? '';
        $available = $length - $index;

        if ($available < 9 && ($next === '' || $next === '!')) {
            $partial = substr($html, $index);

            if (str_starts_with('<![CDATA[', $partial) || str_starts_with('<!--', $partial)) {
                return $this->pending($index, $length, self::BOGUS_COMMENT);
            }
        }

        if ($next === '!') {
            if (substr_compare($html, '<!--', $index, 4) === 0) {
                if (($html[$index + 4] ?? '') === '>') {
                    return $index + 5;
                }

                if (substr_compare($html, '->', $index + 4, 2) === 0) {
                    return $index + 6;
                }

                $this->state = self::COMMENT;

                return $index + 4;
            }

            $this->state = substr_compare($html, '<![CDATA[', $index, 9) === 0 ? self::CDATA : self::BOGUS_COMMENT;

            return $index + 2;
        }

        if ($next === '?') {
            $this->state = self::BOGUS_COMMENT;

            return $index + 2;
        }

        $nameStart = $index + 1;
        $this->closingTag = $next === '/';

        if ($this->closingTag) {
            $nameStart++;

            if ($nameStart >= $length) {
                return $this->pending($index, $length, self::TAG);
            }

            if (! HtmlSpec::isAsciiAlpha($html[$nameStart])) {
                if ($html[$nameStart] === '>') {
                    return $nameStart + 1;
                }

                $this->state = self::BOGUS_COMMENT;

                return $nameStart;
            }
        } elseif (! HtmlSpec::isAsciiAlpha($next)) {
            return $nameStart;
        }

        $nameLength = strcspn($html, self::SPACE.'/>', $nameStart);

        if ($nameStart + $nameLength >= $length) {
            return $this->pending($index, $length, self::TAG);
        }

        $this->tagName = strtolower(substr($html, $nameStart, $nameLength));
        $this->expectsValue = false;
        $this->state = self::TAG;

        return $nameStart + $nameLength;
    }

    private function pending(int $index, int $length, int $state): int
    {
        $this->rewindState = $this->state;
        $this->state = $state;
        $this->rewind = $length - $index;

        return $length;
    }

    private function scanTag(string $html, int $index, int $length): int
    {
        while ($index < $length) {
            $char = $html[$index];

            if ($char === '>') {
                $this->endTag();

                return $index + 1;
            }

            if ($this->expectsValue) {
                if (strpos(self::SPACE, $char) !== false) {
                    $index++;

                    continue;
                }

                $this->expectsValue = false;

                if ($char === '"' || $char === "'") {
                    $this->quote = $char;
                    $this->state = self::QUOTED_VALUE;

                    return $index + 1;
                }

                $this->state = self::UNQUOTED_VALUE;

                return $index;
            }

            if ($char === '=') {
                $this->expectsValue = true;
            }

            $index++;
        }

        return $length;
    }

    private function endTag(): void
    {
        $name = $this->tagName;
        $this->state = self::DATA;

        if ($this->closingTag) {
            if ($this->isCommentUnsafeContainer($name)) {
                $this->unsafeDepth = max(0, $this->unsafeDepth - 1);
            }

            return;
        }

        if ($name === 'plaintext') {
            $this->state = self::PLAINTEXT;
        } elseif (isset(HtmlSpec::RAW_TEXT_ELEMENTS[$name])) {
            $this->state = self::RAW_TEXT;
        } elseif ($this->isCommentUnsafeContainer($name)) {
            $this->unsafeDepth++;
        }
    }

    private function isCommentUnsafeContainer(string $name): bool
    {
        return isset(HtmlSpec::COMMENT_UNSAFE_ELEMENTS[$name])
            && ! isset(HtmlSpec::RAW_TEXT_ELEMENTS[$name])
            && $name !== 'plaintext';
    }

    private function scanTo(string $html, int $index, int $length, string $terminator, int $overlap): int
    {
        if (! preg_match($terminator, $html, $match, PREG_OFFSET_CAPTURE, $index)) {
            $this->rewind = $overlap;

            return $length;
        }

        $this->state = self::DATA;

        return $match[0][1] + strlen($match[0][0]);
    }

    private function scanRawText(string $html, int $index, int $length): int
    {
        $closing = '</'.$this->tagName;
        $closingLength = strlen($closing);

        while (($end = stripos($html, $closing, $index)) !== false) {
            $after = $html[$end + $closingLength] ?? null;

            if ($after === null) {
                break;
            }

            if (strpos(self::SPACE.'/>', $after) !== false) {
                $this->closingTag = true;
                $this->expectsValue = false;
                $this->state = self::TAG;

                return $end + $closingLength;
            }

            $index = $end + 1;
        }

        $this->rewind = $closingLength;

        return $length;
    }
}
