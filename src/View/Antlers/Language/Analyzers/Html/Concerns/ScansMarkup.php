<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait ScansMarkup
{
    private const SCRIPT_DATA = 0;

    private const SCRIPT_ESCAPED = 1;

    private const SCRIPT_DOUBLE_ESCAPED = 2;

    protected function isCurrentRawTextClosingTag($position)
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

    protected function nextRawTextBoundary($position)
    {
        $sourceLength = strlen($this->source);
        $nextAntlers = $this->nextRegionStart($position);
        $container = $this->textElement;
        $antlersBoundary = $this->resolvedEndOffset($nextAntlers, $sourceLength);

        if ($this->arena->name[$container] === 'script') {
            return $this->nextScriptBoundary($position, $antlersBoundary);
        }

        $pattern = '/<\/'.preg_quote($this->arena->name[$container], '/').'(?=[\x00-\x20>\/])/i';
        $closing = false;

        // Only search the literal run we are about to consume. Searching the
        // remaining document again after every Antlers region is quadratic.
        if (preg_match($pattern, substr($this->source, $position, $antlersBoundary - $position), $matches, PREG_OFFSET_CAPTURE)) {
            $closing = $position + $matches[0][1];
        }

        return min(
            $antlersBoundary,
            $this->resolvedEndOffset($closing, $sourceLength)
        );
    }

    protected function nextScriptBoundary($position, $limit)
    {
        for ($index = $position; $index < $limit; $index++) {
            $char = $this->source[$index];

            if ($char === '<' && ($this->source[$index + 1] ?? null) === '/') {
                $closing = strtolower(substr($this->source, $index + 2, 6));
                $delimiter = $this->source[$index + 8] ?? null;

                if ($closing === 'script' && HtmlSpec::isTagNameDelimiter($delimiter)) {
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

    protected function endsEscapedScriptSequence($char, $index)
    {
        if ($char !== '>' || $this->scriptEscape === self::SCRIPT_DATA || $index < 2) {
            return false;
        }

        return $this->source[$index - 1] === '-' && $this->source[$index - 2] === '-';
    }

    protected function markupEnd($start)
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

    protected function bogusCommentEnd($start)
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

    protected function tagMarkupEnd($start, $length)
    {
        $state = 'tag-name';
        $quote = null;
        $index = $start + 1;

        if (($this->source[$index] ?? null) === '/') {
            $index++;
        }

        for (; $index < $length; $index++) {
            if (isset($this->regionEnds[$index])) {
                if ($state === 'before-value') {
                    $state = 'unquoted-value';
                } elseif ($state === 'before-attribute' || $state === 'after-attribute') {
                    $state = 'attribute-name';
                }

                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            $char = $this->source[$index];

            if ($state === 'quoted-value') {
                if ($char === $quote) {
                    $state = 'before-attribute';
                    $quote = null;
                }

                continue;
            }

            if ($state === 'unquoted-value') {
                if ($char === '>') {
                    return $index + 1;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                }

                continue;
            }

            if ($state === 'before-value') {
                if ($this->isHtmlSpace($char)) {
                    continue;
                }

                if ($char === '"' || $char === "'") {
                    $state = 'quoted-value';
                    $quote = $char;
                } elseif ($char === '>') {
                    return $index + 1;
                } else {
                    $state = 'unquoted-value';
                }

                continue;
            }

            if ($state === 'tag-name') {
                if ($char === '>') {
                    return $index + 1;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                } elseif ($char === '/') {
                    $state = 'self-closing';
                }

                continue;
            }

            if ($state === 'attribute-name') {
                if ($char === '=') {
                    $state = 'before-value';
                } elseif ($char === '>') {
                    return $index + 1;
                } elseif ($char === '/') {
                    $state = 'self-closing';
                } elseif ($this->isHtmlSpace($char)) {
                    $state = 'after-attribute';
                }

                continue;
            }

            if ($state === 'after-attribute') {
                if ($char === '=') {
                    $state = 'before-value';
                } elseif ($char === '>') {
                    return $index + 1;
                } elseif ($char === '/') {
                    $state = 'self-closing';
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = 'attribute-name';
                }

                continue;
            }

            if ($state === 'self-closing') {
                if ($char === '>') {
                    return $index + 1;
                }

                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                } else {
                    $state = 'attribute-name';
                }

                continue;
            }

            if ($char === '>') {
                return $index + 1;
            }

            if (! $this->isHtmlSpace($char) && $char !== '/') {
                $state = 'attribute-name';
            }
        }

        return $length;
    }

    protected function nextRegionStart($position)
    {
        $count = count($this->regionStarts);

        while ($this->regionCursor < $count
            && $this->regionStarts[$this->regionCursor] < $position) {
            $this->regionCursor++;
        }

        return $this->regionStarts[$this->regionCursor] ?? null;
    }

    /**
     * @return string|null|false Static name, null for a dynamic name, or false for a missing name.
     */
    protected function tagName($start, $end, $closing)
    {
        $index = $start + 1;

        if ($closing) {
            $index++;

            while ($index < $end && $this->isHtmlSpace($this->source[$index])) {
                $index++;
            }
        }

        $name = '';
        $dynamic = false;

        for (; $index < $end; $index++) {
            if (isset($this->regionEnds[$index])) {
                if (! ($this->regionNodes[$index]->isComment ?? false)) {
                    $dynamic = true;
                }

                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            $char = $this->source[$index];

            if ($char === '>' || $char === '/' || $this->isHtmlSpace($char)) {
                break;
            }

            if ($char === "\0") {
                $char = "\xEF\xBF\xBD";
            }

            $name .= $char;
        }

        if ($name === '' && ! $dynamic) {
            return false;
        }

        if ($dynamic) {
            return null;
        }

        return strtolower($name);
    }

    protected function isSelfClosingTag($start, $end)
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

        $state = 'before-attribute';
        $quote = null;

        for (; $index < $slash; $index++) {
            if (isset($this->regionEnds[$index])) {
                if ($state === 'before-value') {
                    $state = 'unquoted-value';
                }

                $index = $this->regionEnds[$index] - 1;

                continue;
            }

            $char = $this->source[$index];

            if ($state === 'quoted-value') {
                if ($char === $quote) {
                    $state = 'before-attribute';
                    $quote = null;
                }

                continue;
            }

            if ($state === 'unquoted-value') {
                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                }

                continue;
            }

            if ($state === 'before-value') {
                if ($this->isHtmlSpace($char)) {
                    continue;
                }

                if ($char === '"' || $char === "'") {
                    $state = 'quoted-value';
                    $quote = $char;
                } else {
                    $state = 'unquoted-value';
                }

                continue;
            }

            if ($state === 'attribute-name') {
                if ($char === '=') {
                    $state = 'before-value';
                } elseif ($this->isHtmlSpace($char)) {
                    $state = 'after-attribute-name';
                }

                continue;
            }

            if ($state === 'after-attribute-name') {
                if ($char === '=') {
                    $state = 'before-value';
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = 'attribute-name';
                }

                continue;
            }

            if (! $this->isHtmlSpace($char)) {
                $state = 'attribute-name';
            }
        }

        return ! in_array($state, ['before-value', 'quoted-value', 'unquoted-value'], true);
    }

    protected function isHtmlSpace($char)
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    protected function resolvedEndOffset($offset, $sourceLength, $terminatorLength = 0)
    {
        if ($offset === null || $offset === false) {
            return $sourceLength;
        }

        return $offset + $terminatorLength;
    }

    protected function regionIndexAtOrAfter($position)
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

    protected function indexAntlersByteRegions(array $nodes)
    {
        $characterRegions = [];
        $needed = [0 => true];

        foreach ($nodes as $node) {
            if (! $node instanceof LanguageAntlersNode || $node->startPosition === null || $node->endPosition === null) {
                continue;
            }

            $start = $node->startPosition->offset;
            $end = $node->endPosition->offset + 1;
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

    protected function characterToByteOffsets(array $needed)
    {
        return CharacterOffsets::toBytes($this->source, array_keys($needed));
    }
}
