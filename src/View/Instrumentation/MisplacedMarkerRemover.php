<?php

namespace Statamic\View\Instrumentation;

/** @internal */
final class MisplacedMarkerRemover
{
    private const SPACE = " \t\n\r\f";

    private const IN_TAG = 0;

    private const BEFORE_VALUE = 1;

    private const UNQUOTED_VALUE = 2;

    /** @var array<string, array{0: string, 1: string[], 2: string, 3: string, 4: string, 5: string, 6: string}> */
    private static array $compiled = [];

    private readonly int $length;

    private ?string $safeMarkup;

    /** @var string[] */
    private readonly array $literals;

    private readonly string $markerAt;

    private readonly string $anyMarker;

    private readonly string $commentEndOrMarker;

    private readonly string $bogusEndOrMarker;

    private readonly string $cdataEndOrMarker;

    /** @var array<int, int|false> */
    private array $nextLiteral = [];

    private string $result = '';

    private int $copied = 0;

    private int $index = 0;

    /** @param  HtmlInstrumentation[]  $instrumenters */
    private function __construct(private readonly string $html, string $markers, array $instrumenters)
    {
        [
            $this->safeMarkup,
            $this->literals,
            $this->markerAt,
            $this->anyMarker,
            $this->commentEndOrMarker,
            $this->bogusEndOrMarker,
            $this->cdataEndOrMarker,
        ] = self::$compiled[$markers] ?? self::compile($markers, $instrumenters);

        $this->length = strlen($html);
    }

    /** @param  HtmlInstrumentation[]  $instrumenters */
    public static function remove(string $html, string $markers, array $instrumenters): string
    {
        $cleaned = (new self($html, $markers, $instrumenters))->run();

        while ($cleaned !== $html) {
            $html = $cleaned;
            $cleaned = (new self($html, $markers, $instrumenters))->run();
        }

        return $cleaned;
    }

    public static function commentPattern(string $prefix): string
    {
        return '<!-- '.preg_quote($prefix, '/').':(?:start [A-Za-z0-9+\/=]+|end(?: \d+)?) -->';
    }

    public static function attributePattern(string $name): string
    {
        return ' '.preg_quote($name, '/').'="[^"]*"';
    }

    private function run(): string
    {
        while ($this->index < $this->length) {
            $this->skipSafeMarkup();

            if ($this->index >= $this->length) {
                break;
            }

            if (($next = strpos($this->html, '<', $this->index)) === false) {
                break;
            }

            $this->index = $next;

            if (substr_compare($this->html, '<!--', $this->index, 4) === 0) {
                $this->scanComment();
            } else {
                $this->scanTag();
            }
        }

        if ($this->copied === 0) {
            return $this->html;
        }

        return $this->result.substr($this->html, $this->copied);
    }

    private function skipSafeMarkup(): void
    {
        if ($this->safeMarkup === null) {
            return;
        }

        $matched = preg_match($this->safeMarkup, $this->html, $match, PREG_OFFSET_CAPTURE, $this->index);

        if ($matched === false) {
            $this->safeMarkup = null;
        } elseif ($matched === 1) {
            $this->index = $match[0][1];
        }
    }

    private function scanComment(): void
    {
        if (($marker = $this->markerLengthAt($this->index)) !== null) {
            $this->index += $marker;

            return;
        }

        $this->index += 2;
        $this->stripUntil($this->commentEndOrMarker);
    }

    private function stripUntil(string $terminatorOrMarker): void
    {
        while (preg_match($terminatorOrMarker, $this->html, $match, PREG_OFFSET_CAPTURE, $this->index)) {
            [$found, $at] = $match[0];

            if ($found[0] !== '<' && $found[0] !== ' ') {
                $this->index = $at + strlen($found);

                return;
            }

            $this->strip($at, strlen($found));
        }

        $this->index = $this->length;
    }

    private function scanTag(): void
    {
        $nameStart = $this->index + 1;
        $next = $this->html[$nameStart] ?? '';
        $closing = $next === '/';

        if ($closing) {
            $nameStart++;
        }

        if (! ctype_alpha($this->html[$nameStart] ?? '')) {
            $this->scanBogusMarkup($next);

            return;
        }

        $nameLength = strcspn($this->html, self::SPACE.'/>', $nameStart);
        $name = strtolower(substr($this->html, $nameStart, $nameLength));
        $this->index = $nameStart + $nameLength;

        $this->scanAttributes();

        if ($closing) {
            return;
        }

        if ($name === 'plaintext') {
            $this->stripRawText($name, $this->length);
        } elseif (isset(HtmlSpec::RAW_TEXT_ELEMENTS[$name])) {
            $this->stripRawText($name);
        }
    }

    private function scanBogusMarkup(string $next): void
    {
        if ($next === '!' || $next === '?') {
            $this->index += 2;

            if (substr_compare($this->html, '[CDATA[', $this->index, 7) === 0) {
                $this->stripUntil($this->cdataEndOrMarker);
            } else {
                $this->stripUntil($this->bogusEndOrMarker);
            }

            return;
        }

        if ($next === '/' && $this->index + 2 < $this->length) {
            $this->index += 2;
            $this->stripUntil($this->bogusEndOrMarker);

            return;
        }

        $this->index++;
    }

    private function scanAttributes(): void
    {
        $state = self::IN_TAG;

        while ($this->index < $this->length) {
            $at = $this->index + match ($state) {
                self::UNQUOTED_VALUE => strcspn($this->html, self::SPACE.'<>', $this->index),
                self::BEFORE_VALUE => strspn($this->html, self::SPACE, $this->index),
                default => strcspn($this->html, '<>=', $this->index),
            };

            if ($at >= $this->length) {
                $this->index = $this->length;

                return;
            }

            $char = $this->html[$at];

            if ($char === '<' && ($marker = $this->markerLengthAt($at)) !== null) {
                $this->strip($at, $marker);

                continue;
            }

            $this->index = $at + 1;

            if ($char === '>') {
                return;
            }

            if ($state === self::UNQUOTED_VALUE) {
                $state = $char === '<' ? self::UNQUOTED_VALUE : self::IN_TAG;
            } elseif ($state === self::BEFORE_VALUE) {
                $state = self::UNQUOTED_VALUE;

                if ($char === '"' || $char === "'") {
                    $this->scanQuotedValue($char);
                    $state = self::IN_TAG;
                }
            } elseif ($char === '=') {
                $state = self::BEFORE_VALUE;
            }
        }
    }

    private function scanQuotedValue(string $quote): void
    {
        $close = -1;

        while (true) {
            if ($close !== false && $close < $this->index) {
                $close = strpos($this->html, $quote, $this->index);
            }

            $candidate = $this->nextLiteralBefore($close === false ? $this->length : $close);

            if ($candidate === null) {
                $this->index = $close === false ? $this->length : $close + 1;

                return;
            }

            if (($marker = $this->markerLengthAt($candidate)) !== null) {
                $this->strip($candidate, $marker);
            } else {
                $this->index = $candidate + 1;
            }
        }
    }

    private function stripRawText(string $name, ?int $end = null): void
    {
        $end ??= stripos($this->html, '</'.$name, $this->index);

        if ($end === false) {
            $end = $this->length;
        }

        $text = substr($this->html, $this->index, $end - $this->index);
        $cleaned = preg_replace($this->anyMarker, '', $text) ?? $text;

        if ($cleaned !== $text) {
            $this->result .= substr($this->html, $this->copied, $this->index - $this->copied).$cleaned;
            $this->copied = $end;
        }

        $this->index = $end;
    }

    private function nextLiteralBefore(int $limit): ?int
    {
        $candidate = null;

        foreach ($this->literals as $key => $literal) {
            $position = $this->nextLiteral[$key] ?? -1;

            if ($position !== false && $position < $this->index) {
                $position = $this->nextLiteral[$key] = strpos($this->html, $literal, $this->index);
            }

            if ($position !== false && $position < $limit && ($candidate === null || $position < $candidate)) {
                $candidate = $position;
            }
        }

        return $candidate;
    }

    private function markerLengthAt(int $offset): ?int
    {
        return preg_match($this->markerAt, $this->html, $match, 0, $offset) ? strlen($match[0]) : null;
    }

    private function strip(int $offset, int $length): void
    {
        $this->result .= substr($this->html, $this->copied, $offset - $this->copied);
        $this->index = $this->copied = $offset + $length;
    }

    /**
     * @param  HtmlInstrumentation[]  $instrumenters
     * @return array{0: string, 1: string[], 2: string, 3: string, 4: string}
     */
    private static function compile(string $markers, array $instrumenters): array
    {
        if (count(self::$compiled) >= 16) {
            self::$compiled = [];
        }

        [$commentPrefixes, $attributeNames] = HtmlInstrumentation::markerVocabulary($instrumenters);
        $literals = [];

        foreach ($commentPrefixes as $prefix) {
            $literals[] = '<!-- '.$prefix.':';
        }

        foreach ($attributeNames as $name) {
            $literals[] = ' '.$name.'="';
        }

        return self::$compiled[$markers] = [
            self::safeMarkupPattern($commentPrefixes, $attributeNames),
            array_values(array_unique($literals)),
            '/\G(?:'.$markers.')/',
            '/'.$markers.'/',
            '/--!?>|'.$markers.'/',
            '/>|'.$markers.'/',
            '/\]\]>|'.$markers.'/',
        ];
    }

    /**
     * @param  string[]  $commentPrefixes
     * @param  string[]  $attributeNames
     */
    private static function safeMarkupPattern(array $commentPrefixes, array $attributeNames): string
    {
        $space = self::SPACE;
        $quotedPrefixes = array_map(fn ($prefix) => preg_quote($prefix, '/'), $commentPrefixes);
        $quotedNames = array_map(fn ($name) => preg_quote($name, '/'), $attributeNames);

        $markerCommentOpener = $quotedPrefixes === [] ? null : '!-- (?:'.implode('|', $quotedPrefixes).'):';
        $plainLessThan = $markerCommentOpener === null ? null : '<(?!'.$markerCommentOpener.')';
        $plainSpace = $quotedNames === [] ? null : ' (?!(?:'.implode('|', $quotedNames).')=")';
        $rawTextTag = '(?i:'.implode('|', array_keys(HtmlSpec::RAW_TEXT_ELEMENTS)).')';
        $scannerTag = '(?i:'.implode('|', array_keys(HtmlSpec::RAW_TEXT_ELEMENTS + ['plaintext' => true])).')';

        $markerFree = function (string $excluded, bool $guardSpaces, string $alsoAllowed = '') use ($plainLessThan, $plainSpace): string {
            $guardSpaces = $guardSpaces && $plainSpace !== null;
            $alternatives = '[^'.$excluded.($plainLessThan === null ? '' : '<').($guardSpaces ? ' ' : '').']++'.$alsoAllowed;

            if ($plainLessThan !== null) {
                $alternatives .= '|'.$plainLessThan;
            }

            if ($guardSpaces) {
                $alternatives .= '|'.$plainSpace;
            }

            return '(?:'.$alternatives.')*+';
        };

        $closingQuoteOfValue = implode('', array_map(fn ($name) => '(?<! '.$name.'=)', $quotedNames)).'"';
        $lessThanInTag = $plainLessThan ?? '<';

        $attributeValue = '=['.$space.']*+(?:(?=>)'
            .'|"'.$markerFree('"', false).$closingQuoteOfValue
            .'|\''.$markerFree('\'', true).'\''
            .'|(?:[^'.$space.'<>"\']|'.$lessThanInTag.')'.$markerFree($space.'>', false).'(?:['.$space.']|(?=>))'
            .')';

        $restOfTag = '(?:'.($plainLessThan === null ? '[^>=]++' : '[^<>=]++|'.$plainLessThan).'|'.$attributeValue.')*+>';

        $rawTextLessThan = '<(?!\/(?i:\k<raw>)'.($markerCommentOpener === null ? '' : '|'.$markerCommentOpener).')';
        $rawText = '(?:[^<'.($plainSpace === null ? '' : ' ').']++|'.$rawTextLessThan.($plainSpace === null ? '' : '|'.$plainSpace).')*+';

        $safe = ['text' => '[^<]++'];

        if ($commentPrefixes !== []) {
            $safe['markerComment'] = '(?:'.implode('|', array_unique(array_map([self::class, 'commentPattern'], $commentPrefixes))).')';
        }

        $bogusMarkup = $markerFree('>', true).'>';

        $safe += [
            'comment' => '<!(?=--)'.$markerFree('-', true, '|-(?!-!?>)').'--!?>',
            'cdata' => '<!\[CDATA\['.$markerFree(']', true, '|\](?!\]>)').'\]\]>',
            'declaration' => '<[!?](?!--|\[CDATA\[)'.$bogusMarkup,
            'bogusClosingTag' => '<\/(?![A-Za-z\x80-\xff])(?:>|'.$bogusMarkup.')',
            'strayLessThan' => '<(?![A-Za-z\/!?\x80-\xff])',
            'rawTextElement' => '<(?<raw>'.$rawTextTag.')(?=['.$space.'\/>])'.$restOfTag.$rawText.'(?=<\/(?i:\k<raw>)|\z)',
            'openingTag' => '<(?!'.$scannerTag.'['.$space.'\/>])[A-Za-z][^'.$space.'\/>]*+'.$restOfTag,
            'closingTag' => '<\/[A-Za-z][^'.$space.'\/>]*+'.$restOfTag,
        ];

        return '/\G(?:'.implode('|', $safe).')*+\K/';
    }
}
