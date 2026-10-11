<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Instrumentation\HtmlContext;
use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class ElementInspector
{
    protected const STATE_TAG_NAME = 'tag-name';

    protected const STATE_BEFORE_ATTRIBUTE = 'before-attribute';

    protected const STATE_ATTRIBUTE_NAME = 'attribute-name';

    protected const STATE_AFTER_ATTRIBUTE = 'after-attribute';

    protected const STATE_BEFORE_VALUE = 'before-value';

    protected const STATE_DOUBLE_QUOTED = 'double-quoted';

    protected const STATE_SINGLE_QUOTED = 'single-quoted';

    protected const STATE_UNQUOTED_VALUE = 'unquoted-value';

    protected const STATE_SELF_CLOSING = 'self-closing';

    protected const STATE_DONE = 'done';

    public function openingTagAt(string $template, int $startOffset, ?int $startByteOffset = null): string
    {
        $offset = $startByteOffset ?? strlen(mb_substr($template, 0, $startOffset));
        $length = strlen($template);
        $state = self::STATE_TAG_NAME;

        for ($index = $offset + 1; $index < $length; $index++) {
            $char = $template[$index];

            if ($this->startsEmbedded($template, $index)) {
                $end = $this->embeddedEnd($template, $index, $length);

                if ($end === false) {
                    return substr($template, $offset);
                }

                $state = $this->stateAfterAntlers($state);
                $index = $end - 1;

                continue;
            }

            if ($state === self::STATE_DOUBLE_QUOTED || $state === self::STATE_SINGLE_QUOTED) {
                if ($this->isClosingQuote($state, $char)) {
                    $state = self::STATE_BEFORE_ATTRIBUTE;
                }

                continue;
            }

            if ($char === '>' && $state !== self::STATE_TAG_NAME) {
                return substr($template, $offset, $index + 1 - $offset);
            }

            if ($state === self::STATE_TAG_NAME) {
                if ($this->isHtmlSpace($char)) {
                    $state = self::STATE_BEFORE_ATTRIBUTE;
                } elseif ($char === '/') {
                    $state = self::STATE_SELF_CLOSING;
                } elseif ($char === '>') {
                    return substr($template, $offset, $index + 1 - $offset);
                }
            } elseif ($state === self::STATE_BEFORE_ATTRIBUTE) {
                if ($char === '/') {
                    $state = self::STATE_SELF_CLOSING;
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = self::STATE_ATTRIBUTE_NAME;
                }
            } elseif ($state === self::STATE_ATTRIBUTE_NAME) {
                if ($this->isHtmlSpace($char)) {
                    $state = self::STATE_AFTER_ATTRIBUTE;
                } elseif ($char === '/') {
                    $state = self::STATE_SELF_CLOSING;
                } elseif ($char === '=') {
                    $state = self::STATE_BEFORE_VALUE;
                }
            } elseif ($state === self::STATE_AFTER_ATTRIBUTE) {
                if ($char === '/') {
                    $state = self::STATE_SELF_CLOSING;
                } elseif ($char === '=') {
                    $state = self::STATE_BEFORE_VALUE;
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = self::STATE_ATTRIBUTE_NAME;
                }
            } elseif ($state === self::STATE_BEFORE_VALUE) {
                if ($char === '"') {
                    $state = self::STATE_DOUBLE_QUOTED;
                } elseif ($char === "'") {
                    $state = self::STATE_SINGLE_QUOTED;
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = self::STATE_UNQUOTED_VALUE;
                }
            } elseif ($state === self::STATE_UNQUOTED_VALUE) {
                if ($this->isHtmlSpace($char)) {
                    $state = self::STATE_BEFORE_ATTRIBUTE;
                }
            } elseif ($state === self::STATE_SELF_CLOSING) {
                $state = self::STATE_BEFORE_ATTRIBUTE;
                $index--;
            }
        }

        return substr($template, $offset);
    }

    public function attributeInsertionOffset(string $template, int $startByteOffset): int
    {
        $length = strlen($template);
        $index = $startByteOffset + 1;

        while ($index < $length
            && ! HtmlSpec::isTagNameDelimiter($template[$index])) {
            if ($this->startsEmbedded($template, $index)) {
                $end = $this->embeddedEnd($template, $index, $length);

                if ($end === false) {
                    break;
                }

                $index = $end;
            } else {
                $index++;
            }
        }

        return $index;
    }

    /**
     * @param  int[]  $offsets
     * @return array<int, array{0: string, 1: string|null, 2: string}> offset => [kind, attribute name, partial run]
     */
    public function classifyTagPositions(string $markup, array $offsets): array
    {
        $length = strlen($markup);
        $needed = array_fill_keys($offsets, true);
        $classified = [];
        $state = self::STATE_TAG_NAME;
        $tagName = '';
        $attributeName = '';
        $pendingValueOwner = '';
        $index = 1;

        if (($markup[1] ?? null) === '/') {
            $index = 2;
        }

        while ($index < $length && count($classified) < count($needed)) {
            if (isset($needed[$index]) && ! isset($classified[$index])) {
                $classified[$index] = $this->classifyTagState($markup, $index, $length, $state, $tagName, $attributeName, $pendingValueOwner);
            }

            if ($this->startsEmbedded($markup, $index)) {
                $end = $this->embeddedEnd($markup, $index, $length);

                if ($end === false) {
                    break;
                }

                if ($markup[$index] === '<') {
                    foreach ($needed as $offset => $unused) {
                        if ($offset > $index && $offset < $end && ! isset($classified[$offset])) {
                            $classified[$offset] = $this->classifyTagState($markup, $offset, $length, $state, $tagName, $attributeName, $pendingValueOwner);
                        }
                    }
                }

                if ($state === self::STATE_BEFORE_ATTRIBUTE || $state === self::STATE_AFTER_ATTRIBUTE) {
                    $attributeName = '';
                    $pendingValueOwner = '';
                }

                $state = $this->stateAfterAntlers($state);
                $index = $end;

                continue;
            }

            $char = $markup[$index];

            if ($state === self::STATE_DOUBLE_QUOTED || $state === self::STATE_SINGLE_QUOTED) {
                if ($this->isClosingQuote($state, $char)) {
                    $state = self::STATE_BEFORE_ATTRIBUTE;
                    $attributeName = '';
                }

                $index++;

                continue;
            }

            switch ($state) {
                case self::STATE_TAG_NAME:
                    if ($this->isHtmlSpace($char) || $char === '/') {
                        $state = self::STATE_BEFORE_ATTRIBUTE;
                    } elseif ($char === '>') {
                        $state = self::STATE_DONE;
                    } else {
                        $tagName .= $char;
                    }
                    break;
                case self::STATE_BEFORE_ATTRIBUTE:
                    if (! $this->isHtmlSpace($char) && $char !== '/' && $char !== '>') {
                        $state = self::STATE_ATTRIBUTE_NAME;
                        $attributeName = $char;
                    }
                    break;
                case self::STATE_ATTRIBUTE_NAME:
                    if ($char === '=') {
                        $pendingValueOwner = $attributeName;
                        $state = self::STATE_BEFORE_VALUE;
                    } elseif ($this->isHtmlSpace($char)) {
                        $pendingValueOwner = $attributeName;
                        $state = self::STATE_AFTER_ATTRIBUTE;
                    } elseif ($char === '/' || $char === '>') {
                        $state = self::STATE_BEFORE_ATTRIBUTE;
                        $attributeName = '';
                    } else {
                        $attributeName .= $char;
                    }
                    break;
                case self::STATE_AFTER_ATTRIBUTE:
                    if ($char === '=') {
                        $state = self::STATE_BEFORE_VALUE;
                    } elseif ($char === '/') {
                        $state = self::STATE_BEFORE_ATTRIBUTE;
                        $attributeName = '';
                    } elseif (! $this->isHtmlSpace($char)) {
                        $state = self::STATE_ATTRIBUTE_NAME;
                        $attributeName = $char;
                    }
                    break;
                case self::STATE_BEFORE_VALUE:
                    if ($char === '"') {
                        $state = self::STATE_DOUBLE_QUOTED;
                    } elseif ($char === "'") {
                        $state = self::STATE_SINGLE_QUOTED;
                    } elseif (! $this->isHtmlSpace($char)) {
                        $state = self::STATE_UNQUOTED_VALUE;
                    }
                    break;
                case self::STATE_UNQUOTED_VALUE:
                    if ($this->isHtmlSpace($char)) {
                        $state = self::STATE_BEFORE_ATTRIBUTE;
                        $attributeName = '';
                    }
                    break;
            }

            $index++;
        }

        foreach ($needed as $offset => $unused) {
            if (! isset($classified[$offset])) {
                $classified[$offset] = [HtmlContext::KIND_TAG_OPEN, null, ''];
            }
        }

        return $classified;
    }

    /** @return array{0: string, 1: string|null, 2: string} */
    protected function classifyTagState(string $markup, int $index, int $length, string $state, string $tagName, string $attributeName, string $pendingValueOwner): array
    {
        switch ($state) {
            case self::STATE_TAG_NAME:
                return [HtmlContext::KIND_ELEMENT_NAME, null, $tagName];
            case self::STATE_ATTRIBUTE_NAME:
                return [HtmlContext::KIND_ATTRIBUTE_NAME, $this->normalizedAttributeName($attributeName), $attributeName];
            case self::STATE_DOUBLE_QUOTED:
            case self::STATE_SINGLE_QUOTED:
            case self::STATE_UNQUOTED_VALUE:
            case self::STATE_BEFORE_VALUE:
                $owner = $this->attributeValueOwner($state, $attributeName, $pendingValueOwner);

                return [HtmlContext::KIND_ATTRIBUTE_VALUE, $this->normalizedAttributeName($owner), ''];
            case self::STATE_BEFORE_ATTRIBUTE:
            case self::STATE_AFTER_ATTRIBUTE:
                $lookahead = $index;

                if ($this->startsEmbedded($markup, $lookahead)) {
                    $end = $this->embeddedEnd($markup, $lookahead, $length);

                    if ($end === false) {
                        $lookahead = $length;
                    } else {
                        $lookahead = $end;
                    }
                }

                while ($lookahead < $length && $this->isHtmlSpace($markup[$lookahead])) {
                    $lookahead++;
                }

                if (($markup[$lookahead] ?? null) === '=' && $state === self::STATE_BEFORE_ATTRIBUTE) {
                    return [HtmlContext::KIND_ATTRIBUTE_NAME, null, ''];
                }

                return [HtmlContext::KIND_TAG_OPEN, null, ''];
            default:
                return [HtmlContext::KIND_TAG_OPEN, null, ''];
        }
    }

    protected function stateAfterAntlers(string $state): string
    {
        return match ($state) {
            self::STATE_BEFORE_VALUE => self::STATE_UNQUOTED_VALUE,
            self::STATE_BEFORE_ATTRIBUTE, self::STATE_AFTER_ATTRIBUTE => self::STATE_ATTRIBUTE_NAME,
            default => $state,
        };
    }

    protected function normalizedAttributeName(string $name): ?string
    {
        if ($name === '') {
            return null;
        }

        return strtolower($name);
    }

    protected function isClosingQuote(string $state, string $char): bool
    {
        if ($state === self::STATE_DOUBLE_QUOTED) {
            return $char === '"';
        }

        return $state === self::STATE_SINGLE_QUOTED && $char === "'";
    }

    protected function attributeValueOwner(string $state, string $attributeName, string $pendingValueOwner): string
    {
        if ($state === self::STATE_BEFORE_VALUE && $pendingValueOwner) {
            return $pendingValueOwner;
        }

        if ($attributeName) {
            return $attributeName;
        }

        return $pendingValueOwner;
    }

    public function hasAttribute(string $openingTag, string $attributeName): bool
    {
        if (stripos($openingTag, $attributeName) === false) {
            return false;
        }

        $length = strlen($openingTag);
        $index = 1;

        while ($index < $length
            && ! HtmlSpec::isTagNameDelimiter($openingTag[$index])) {
            if ($this->startsEmbedded($openingTag, $index)) {
                $end = $this->embeddedEnd($openingTag, $index, $length);

                if ($end === false) {
                    return false;
                }

                $index = $end;
            } else {
                $index++;
            }
        }

        while ($index < $length) {
            while ($index < $length && $this->isHtmlSpace($openingTag[$index])) {
                $index++;
            }

            if ($index >= $length || $openingTag[$index] === '>') {
                return false;
            }

            if ($openingTag[$index] === '/') {
                $index++;

                continue;
            }

            if ($this->startsEmbedded($openingTag, $index)) {
                $end = $this->embeddedEnd($openingTag, $index, $length);

                if ($end === false) {
                    return false;
                }

                $index = $end;

                continue;
            }

            $nameStart = $index;
            $dynamicName = false;

            if ($openingTag[$index] === '=') {
                $index++;
            }

            while ($index < $length
                && ! HtmlSpec::isAttributeNameDelimiter($openingTag[$index])) {
                if ($this->startsEmbedded($openingTag, $index)) {
                    $dynamicName = true;
                    $end = $this->embeddedEnd($openingTag, $index, $length);

                    if ($end === false) {
                        return false;
                    }

                    $index = $end;
                } else {
                    $index++;
                }
            }

            $name = substr($openingTag, $nameStart, $index - $nameStart);

            if (! $dynamicName && strcasecmp($name, $attributeName) === 0) {
                return true;
            }

            while ($index < $length && $this->isHtmlSpace($openingTag[$index])) {
                $index++;
            }

            if ($index >= $length || $openingTag[$index] !== '=') {
                continue;
            }

            $index++;

            while ($index < $length && $this->isHtmlSpace($openingTag[$index])) {
                $index++;
            }

            if ($index < $length && ($openingTag[$index] === '"' || $openingTag[$index] === "'")) {
                $quote = $openingTag[$index++];

                while ($index < $length && $openingTag[$index] !== $quote) {
                    if ($this->startsEmbedded($openingTag, $index)) {
                        $end = $this->embeddedEnd($openingTag, $index, $length);

                        if ($end === false) {
                            return false;
                        }

                        $index = $end;
                    } else {
                        $index++;
                    }
                }

                if ($index < $length) {
                    $index++;
                }
            } else {
                while ($index < $length
                    && ! $this->isHtmlSpace($openingTag[$index])
                    && $openingTag[$index] !== '>') {
                    if ($this->startsEmbedded($openingTag, $index)) {
                        $end = $this->embeddedEnd($openingTag, $index, $length);

                        if ($end === false) {
                            return false;
                        }

                        $index = $end;
                    } else {
                        $index++;
                    }
                }
            }
        }

        return false;
    }

    protected function isHtmlSpace(string $char): bool
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    protected function startsEmbedded(string $content, int $index): bool
    {
        $char = $content[$index] ?? '';

        if ($char === '{') {
            return ($content[$index + 1] ?? '') === '{';
        }

        return $char === '<' && RegionExtents::startsPhpBlock($content, $index);
    }

    /** @return int|false Offset immediately after the Antlers region or PHP block. */
    protected function embeddedEnd(string $content, int $start, int $length): int|false
    {
        return RegionExtents::embeddedEndInBytes($content, $start, $length) ?? false;
    }
}
