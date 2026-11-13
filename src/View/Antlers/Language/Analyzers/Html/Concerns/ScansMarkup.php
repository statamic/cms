<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Antlers\Language\Nodes\Structures\PhpExecutionNode;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\Antlers\RegionExtents;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait ScansMarkup
{
    private const SCRIPT_DATA = 0;

    private const SCRIPT_ESCAPED = 1;

    private const SCRIPT_DOUBLE_ESCAPED = 2;

    private const TAG_STATE_NAME = 'tag-name';

    private const TAG_STATE_BEFORE_ATTRIBUTE = 'before-attribute';

    private const TAG_STATE_ATTRIBUTE_NAME = 'attribute-name';

    private const TAG_STATE_AFTER_ATTRIBUTE = 'after-attribute';

    private const TAG_STATE_BEFORE_VALUE = 'before-value';

    private const TAG_STATE_QUOTED_VALUE = 'quoted-value';

    private const TAG_STATE_UNQUOTED_VALUE = 'unquoted-value';

    private const TAG_STATE_SELF_CLOSING = 'self-closing';

    protected function isCurrentRawTextClosingTag(int $position): bool
    {
        $container = $this->textElement;

        if ($container === null || substr($this->source, $position, 2) !== '</') {
            return false;
        }

        if ($this->arena->name[$container] === 'script' && $this->scriptEscape === self::SCRIPT_DOUBLE_ESCAPED) {
            return false;
        }

        $name = preg_quote($this->arena->name[$container], '/');

        return (bool) preg_match('/^<\/'.$name.'(?=[\x00-\x20>\/])/i', substr($this->source, $position));
    }

    protected function nextRawTextBoundary(int $position): int
    {
        $sourceLength = strlen($this->source);
        $nextAntlers = $this->nextRegionStart($position);
        $container = $this->textElement;
        $antlersBoundary = $this->resolvedEndOffset($nextAntlers, $sourceLength);

        if ($this->arena->name[$container] === 'script') {
            return $this->nextScriptBoundary($position, $antlersBoundary);
        }

        if ($this->rawTextClosingElement !== $container || $this->rawTextClosingOffset < $position) {
            $this->rawTextClosingElement = $container;
            $this->rawTextClosingOffset = $this->rawTextClosingTagOffset($container, $position, $sourceLength);
        }

        return min($antlersBoundary, $this->rawTextClosingOffset);
    }

    protected function rawTextClosingTagOffset(int $container, int $position, int $sourceLength): int
    {
        $pattern = '/<\/'.preg_quote($this->arena->name[$container], '/').'(?=[\x00-\x20>\/])/i';

        if (preg_match($pattern, $this->source, $matches, PREG_OFFSET_CAPTURE, $position)) {
            return $matches[0][1];
        }

        return $sourceLength;
    }

    protected function nextScriptBoundary(int $position, int $limit): int
    {
        for ($index = $position; $index < $limit; $index++) {
            $char = $this->source[$index];

            if ($char === '<' && ($this->source[$index + 1] ?? null) === '/') {
                $closing = strtolower(substr($this->source, $index + 2, 6));
                $delimiter = $this->source[$index + 8] ?? null;

                if ($closing === 'script' && $delimiter !== null && HtmlSpec::isTagNameDelimiter($delimiter)) {
                    if ($this->scriptEscape === self::SCRIPT_DOUBLE_ESCAPED) {
                        $this->scriptEscape = self::SCRIPT_ESCAPED;
                        $index += 7;

                        continue;
                    }

                    return $index;
                }
            }

            if ($char === '<') {
                if ($this->scriptEscape === self::SCRIPT_DATA && substr($this->source, $index + 1, 3) === '!--') {
                    $this->scriptEscape = self::SCRIPT_ESCAPED;
                    $index += 3;
                } elseif ($this->scriptEscape === self::SCRIPT_ESCAPED
                    && strtolower(substr($this->source, $index + 1, 6)) === 'script') {
                    $delimiter = $this->source[$index + 7] ?? null;

                    if (HtmlSpec::isTagNameDelimiter($delimiter)) {
                        $this->scriptEscape = self::SCRIPT_DOUBLE_ESCAPED;
                        $index += 6;
                    }
                }
            } elseif ($this->endsEscapedScriptSequence($char, $index)) {
                $this->scriptEscape = self::SCRIPT_DATA;
            }
        }

        return $limit;
    }

    protected function endsEscapedScriptSequence(string $char, int $index): bool
    {
        if ($char !== '>' || $this->scriptEscape === self::SCRIPT_DATA || $index < 2) {
            return false;
        }

        return $this->source[$index - 1] === '-' && $this->source[$index - 2] === '-';
    }

    protected function markupEnd(int $start): int
    {
        $length = strlen($this->source);
        $declaration = ($this->source[$start + 1] ?? null) === '!';
        $processingInstruction = ($this->source[$start + 1] ?? null) === '?';
        $comment = $declaration && substr($this->source, $start, 4) === '<!--';
        $cdata = $declaration
            && ! $comment
            && $this->currentContainerIsForeign()
            && substr($this->source, $start, 9) === '<![CDATA[';

        if (! $declaration && ! $processingInstruction) {
            return $this->tagMarkupEnd($start, $length);
        }

        if ($processingInstruction && $this->startsPhpBlock($this->source, $start)) {
            return $this->phpBlockEnd($start, $length);
        }

        if (empty($this->regionStarts)) {
            if ($comment) {
                if (($this->source[$start + 4] ?? null) === '>') {
                    return $start + 5;
                }

                if (substr($this->source, $start + 4, 2) === '->') {
                    return $start + 6;
                }

                $normalEnd = strpos($this->source, '-->', $start + 4);
                $bangEnd = strpos($this->source, '--!>', $start + 4);

                if ($normalEnd === false) {
                    $end = $bangEnd;
                    $terminatorLength = 4;
                } elseif ($bangEnd === false || $normalEnd < $bangEnd) {
                    $end = $normalEnd;
                    $terminatorLength = 3;
                } else {
                    $end = $bangEnd;
                    $terminatorLength = 4;
                }

                return $this->resolvedEndOffset($end, $length, $terminatorLength);
            }

            if ($cdata) {
                $end = strpos($this->source, ']]>', $start + 9);

                return $this->resolvedEndOffset($end, $length, 3);
            }

        }

        $scanStart = $start + 1;

        if ($comment) {
            $scanStart = $start + 4;
        }

        for ($index = $scanStart; $index < $length; $index++) {
            if (isset($this->regionEnds[$index])) {
                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            if ($comment) {
                if (substr($this->source, $index, 3) === '-->') {
                    return $index + 3;
                }

                if (substr($this->source, $index, 4) === '--!>') {
                    return $index + 4;
                }

                if ($index === $start + 4 && $this->source[$index] === '>') {
                    return $index + 1;
                }

                if ($index === $start + 4 && substr($this->source, $index, 2) === '->') {
                    return $index + 2;
                }

                continue;
            }

            if ($cdata) {
                if (substr($this->source, $index, 3) === ']]>') {
                    return $index + 3;
                }

                continue;
            }

            if ($this->source[$index] === '>') {
                return $index + 1;
            }
        }

        return $length;
    }

    protected function startsPhpBlock(string $source, int $start): bool
    {
        return RegionExtents::startsPhpBlock($source, $start);
    }

    protected function phpBlockEnd(int $start, int $length): int
    {
        for ($index = $start + 2; $index < $length; $index++) {
            if (isset($this->regionEnds[$index])) {
                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            if ($this->source[$index] === '?' && ($this->source[$index + 1] ?? null) === '>') {
                return $index + 2;
            }
        }

        return $length;
    }

    protected function bogusCommentEnd(int $start): int
    {
        $length = strlen($this->source);

        for ($index = $start + 2; $index < $length; $index++) {
            if (isset($this->regionEnds[$index])) {
                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            if ($this->source[$index] === '>') {
                return $index + 1;
            }
        }

        return $length;
    }

    protected function tagMarkupEnd(int $start, int $length): int
    {
        $state = self::TAG_STATE_NAME;
        $quote = null;
        $index = $start + 1;

        if (($this->source[$index] ?? null) === '/') {
            $index++;
        }

        $regionIndex = $this->regionIndexAtOrAfter($index);
        $nextRegion = $this->regionStarts[$regionIndex] ?? PHP_INT_MAX;

        while ($index < $length) {
            $limit = min($length, $nextRegion);

            if ($index < $limit) {
                switch ($state) {
                    case self::TAG_STATE_QUOTED_VALUE:
                        $index += strcspn($this->source, $quote.'<', $index, $limit - $index);
                        break;
                    case self::TAG_STATE_UNQUOTED_VALUE:
                        $index += strcspn($this->source, "\t\n\f\r ><", $index, $limit - $index);
                        break;
                    case self::TAG_STATE_NAME:
                        $index += strcspn($this->source, "\t\n\f\r >/<", $index, $limit - $index);
                        break;
                    case self::TAG_STATE_ATTRIBUTE_NAME:
                        $index += strcspn($this->source, "\t\n\f\r =>/<", $index, $limit - $index);
                        break;
                    case self::TAG_STATE_BEFORE_VALUE:
                    case self::TAG_STATE_AFTER_ATTRIBUTE:
                        $index += strspn($this->source, "\t\n\f\r ", $index, $limit - $index);
                        break;
                    case self::TAG_STATE_BEFORE_ATTRIBUTE:
                        $index += strspn($this->source, "\t\n\f\r /", $index, $limit - $index);
                        break;
                }
            }

            if ($index >= $length) {
                break;
            }

            $embeddedEnd = null;

            if ($index === $nextRegion) {
                $embeddedEnd = $this->regionEnds[$nextRegion];
                $regionIndex++;
                $nextRegion = $this->regionStarts[$regionIndex] ?? PHP_INT_MAX;
            } elseif ($this->source[$index] === '<' && $this->startsPhpBlock($this->source, $index)) {
                $embeddedEnd = $this->phpBlockEnd($index, $length);
                $regionIndex = $this->regionIndexAtOrAfter($embeddedEnd);
                $nextRegion = $this->regionStarts[$regionIndex] ?? PHP_INT_MAX;
            }

            if ($embeddedEnd !== null) {
                if ($state === self::TAG_STATE_BEFORE_VALUE) {
                    $state = self::TAG_STATE_UNQUOTED_VALUE;
                } elseif ($state === self::TAG_STATE_BEFORE_ATTRIBUTE || $state === self::TAG_STATE_AFTER_ATTRIBUTE) {
                    $state = self::TAG_STATE_ATTRIBUTE_NAME;
                }

                $index = $embeddedEnd;

                continue;
            }

            $char = $this->source[$index];
            $index++;

            if ($state === self::TAG_STATE_QUOTED_VALUE) {
                if ($char === $quote) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                    $quote = null;
                }

                continue;
            }

            if ($state === self::TAG_STATE_UNQUOTED_VALUE) {
                if ($char === '>') {
                    return $index;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_BEFORE_VALUE) {
                if ($char === '"' || $char === "'") {
                    $state = self::TAG_STATE_QUOTED_VALUE;
                    $quote = $char;
                } elseif ($char === '>') {
                    return $index;
                } else {
                    $state = self::TAG_STATE_UNQUOTED_VALUE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_NAME) {
                if ($char === '>') {
                    return $index;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                } elseif ($char === '/') {
                    $state = self::TAG_STATE_SELF_CLOSING;
                }

                continue;
            }

            if ($state === self::TAG_STATE_ATTRIBUTE_NAME) {
                if ($char === '=') {
                    $state = self::TAG_STATE_BEFORE_VALUE;
                } elseif ($char === '>') {
                    return $index;
                } elseif ($char === '/') {
                    $state = self::TAG_STATE_SELF_CLOSING;
                } elseif ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_AFTER_ATTRIBUTE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_AFTER_ATTRIBUTE) {
                if ($char === '=') {
                    $state = self::TAG_STATE_BEFORE_VALUE;
                } elseif ($char === '>') {
                    return $index;
                } elseif ($char === '/') {
                    $state = self::TAG_STATE_SELF_CLOSING;
                } else {
                    $state = self::TAG_STATE_ATTRIBUTE_NAME;
                }

                continue;
            }

            if ($state === self::TAG_STATE_SELF_CLOSING) {
                if ($char === '>') {
                    return $index;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                } else {
                    $state = self::TAG_STATE_ATTRIBUTE_NAME;
                }

                continue;
            }

            if ($char === '>') {
                return $index;
            }

            $state = self::TAG_STATE_ATTRIBUTE_NAME;
        }

        return $length;
    }

    protected function nextRegionStart(int $position): ?int
    {
        $count = count($this->regionStarts);

        while ($this->regionCursor < $count
            && $this->regionStarts[$this->regionCursor] < $position) {
            $this->regionCursor++;
        }

        return $this->regionStarts[$this->regionCursor] ?? null;
    }

    protected function tagName(int $start, int $end, bool $closing): string|false|null
    {
        $index = $start + 1;

        if ($closing) {
            $index++;
            $index += strspn($this->source, "\t\n\f\r ", $index, max(0, $end - $index));
        }

        $name = '';
        $dynamic = false;
        $regionIndex = $this->regionIndexAtOrAfter($index);
        $nextRegion = $this->regionStarts[$regionIndex] ?? PHP_INT_MAX;

        while ($index < $end) {
            $limit = min($end, $nextRegion);
            $stop = $index + strcspn($this->source, "\t\n\f\r >/", $index, $limit - $index);
            $name .= substr($this->source, $index, $stop - $index);
            $index = $stop;

            if ($stop < $limit || $stop !== $nextRegion) {
                break;
            }

            if (! ($this->regionNodes[$nextRegion]->isComment ?? false)) {
                $dynamic = true;
            }

            $index = $this->regionEnds[$nextRegion];
            $regionIndex++;
            $nextRegion = $this->regionStarts[$regionIndex] ?? PHP_INT_MAX;
        }

        if ($name === '' && ! $dynamic) {
            return false;
        }

        if ($dynamic) {
            return null;
        }

        return strtolower(str_replace("\0", "\xEF\xBF\xBD", $name));
    }

    protected function isSelfClosingTag(int $start, int $end): bool
    {
        $slash = $end - 2;

        if ($slash <= $start || $this->source[$slash] !== '/') {
            return false;
        }

        $index = $start + 1;

        while ($index < $slash) {
            if (isset($this->regionEnds[$index])) {
                $index = $this->regionEnds[$index];

                continue;
            }

            $char = $this->source[$index];

            if ($this->isHtmlSpace($char) || $char === '/' || $char === '>') {
                break;
            }

            $index++;
        }

        $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
        $quote = null;

        for (; $index < $slash; $index++) {
            $embeddedEnd = $this->regionEnds[$index] ?? null;
            $char = $this->source[$index];

            if ($embeddedEnd === null && $char === '<' && $this->startsPhpBlock($this->source, $index)) {
                $embeddedEnd = $this->phpBlockEnd($index, $end);
            }

            if ($embeddedEnd !== null) {
                if ($state === self::TAG_STATE_BEFORE_VALUE) {
                    $state = self::TAG_STATE_UNQUOTED_VALUE;
                }

                $index = $embeddedEnd - 1;

                continue;
            }

            if ($state === self::TAG_STATE_QUOTED_VALUE) {
                if ($char === $quote) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                    $quote = null;
                }

                continue;
            }

            if ($state === self::TAG_STATE_UNQUOTED_VALUE) {
                if ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_BEFORE_ATTRIBUTE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_BEFORE_VALUE) {
                if ($this->isHtmlSpace($char)) {
                    continue;
                }

                if ($char === '"' || $char === "'") {
                    $state = self::TAG_STATE_QUOTED_VALUE;
                    $quote = $char;
                } else {
                    $state = self::TAG_STATE_UNQUOTED_VALUE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_ATTRIBUTE_NAME) {
                if ($char === '=') {
                    $state = self::TAG_STATE_BEFORE_VALUE;
                } elseif ($this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_AFTER_ATTRIBUTE;
                }

                continue;
            }

            if ($state === self::TAG_STATE_AFTER_ATTRIBUTE) {
                if ($char === '=') {
                    $state = self::TAG_STATE_BEFORE_VALUE;
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = self::TAG_STATE_ATTRIBUTE_NAME;
                }

                continue;
            }

            if (! $this->isHtmlSpace($char)) {
                $state = self::TAG_STATE_ATTRIBUTE_NAME;
            }
        }

        return ! in_array($state, [self::TAG_STATE_BEFORE_VALUE, self::TAG_STATE_QUOTED_VALUE, self::TAG_STATE_UNQUOTED_VALUE], true);
    }

    protected function isHtmlSpace(string $char): bool
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    protected function resolvedEndOffset(int|false|null $offset, int $sourceLength, int $terminatorLength = 0): int
    {
        if ($offset === null || $offset === false) {
            return $sourceLength;
        }

        return $offset + $terminatorLength;
    }

    protected function regionIndexAtOrAfter(int $position): int
    {
        $low = 0;
        $high = count($this->regionStarts);

        while ($low < $high) {
            $middle = ($low + $high) >> 1;

            if ($this->regionStarts[$middle] < $position) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $low;
    }

    protected function indexAntlersByteRegions(array $nodes): void
    {
        $characterRegions = [];
        $needed = [0 => true];

        foreach ($nodes as $node) {
            if (! $node instanceof LanguageAntlersNode && ! $node instanceof PhpExecutionNode) {
                continue;
            }

            if ($node->startPosition === null || $node->endPosition === null) {
                continue;
            }

            $start = $node->startPosition->offset;
            $end = $node->endPosition->offset + 1;

            if ($node instanceof PhpExecutionNode) {
                $end = $node->endPosition->offset + strlen($node->rawEnd);
            }

            $characterRegions[] = [$start, $end, $node];
            $needed[$start] = true;
            $needed[$end] = true;
        }

        $this->regionEnds = [];
        $this->regionNodes = [];
        $this->regionStarts = [];

        if (empty($characterRegions)) {
            return;
        }

        $byteOffsets = $this->characterToByteOffsets($needed);

        foreach ($characterRegions as [$start, $end, $node]) {
            if (isset($byteOffsets[$start], $byteOffsets[$end])) {
                $this->regionEnds[$byteOffsets[$start]] = $byteOffsets[$end];
                $this->regionNodes[$byteOffsets[$start]] = $node;
            }
        }

        ksort($this->regionEnds, SORT_NUMERIC);
        $this->regionStarts = array_keys($this->regionEnds);
    }

    protected function characterToByteOffsets(array $needed): array
    {
        return CharacterOffsets::toBytes($this->source, array_keys($needed));
    }
}
