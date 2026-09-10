<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class ElementInspector
{
    public function openingTagAt($template, $startOffset, $startByteOffset = null)
    {
        $offset = $startByteOffset ?? strlen(mb_substr($template, 0, $startOffset));
        $length = strlen($template);
        $state = 'tag-name';

        for ($index = $offset + 1; $index < $length; $index++) {
            $char = $template[$index];

            if ($char === '{' && substr($template, $index, 2) === '{{') {
                $end = $this->antlersEndInBytes($template, $index, $length);

                if ($end === false) {
                    return substr($template, $offset);
                }

                $state = $this->stateAfterAntlers($state);
                $index = $end - 1;

                continue;
            }

            if ($state === 'double-quoted' || $state === 'single-quoted') {
                if ($this->isClosingQuote($state, $char)) {
                    $state = 'before-attribute';
                }

                continue;
            }

            if ($char === '>' && $state !== 'tag-name') {
                return substr($template, $offset, $index + 1 - $offset);
            }

            if ($state === 'tag-name') {
                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                } elseif ($char === '/') {
                    $state = 'self-closing';
                } elseif ($char === '>') {
                    return substr($template, $offset, $index + 1 - $offset);
                }
            } elseif ($state === 'before-attribute') {
                if ($char === '/') {
                    $state = 'self-closing';
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = 'attribute-name';
                }
            } elseif ($state === 'attribute-name') {
                if ($this->isHtmlSpace($char)) {
                    $state = 'after-attribute';
                } elseif ($char === '/') {
                    $state = 'self-closing';
                } elseif ($char === '=') {
                    $state = 'before-value';
                }
            } elseif ($state === 'after-attribute') {
                if ($char === '/') {
                    $state = 'self-closing';
                } elseif ($char === '=') {
                    $state = 'before-value';
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = 'attribute-name';
                }
            } elseif ($state === 'before-value') {
                if ($char === '"') {
                    $state = 'double-quoted';
                } elseif ($char === "'") {
                    $state = 'single-quoted';
                } elseif (! $this->isHtmlSpace($char)) {
                    $state = 'unquoted-value';
                }
            } elseif ($state === 'unquoted-value') {
                if ($this->isHtmlSpace($char)) {
                    $state = 'before-attribute';
                }
            } elseif ($state === 'self-closing') {
                $state = 'before-attribute';
                $index--;
            }
        }

        return substr($template, $offset);
    }

    /**
     * @param  string  $template
     * @param  int  $startByteOffset  Byte offset of the element's `<`.
     * @return int
     */
    public function attributeInsertionOffset($template, $startByteOffset)
    {
        $length = strlen($template);
        $index = $startByteOffset + 1;

        while ($index < $length
            && ! HtmlSpec::isTagNameDelimiter($template[$index])) {
            if (substr($template, $index, 2) === '{{') {
                $end = $this->antlersEndInBytes($template, $index, $length);

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
     * @param  string  $markup  The tag markup, starting at its `<`.
     * @param  int[]  $offsets  Byte offsets relative to the markup start.
     * @return array<int, array{0: string, 1: string|null, 2: string}> offset => [kind, attribute name, partial run]
     */
    public function classifyTagPositions($markup, array $offsets)
    {
        $length = strlen($markup);
        $needed = array_fill_keys($offsets, true);
        $classified = [];
        $state = 'tag-name';
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

            if (substr($markup, $index, 2) === '{{') {
                $end = $this->antlersEndInBytes($markup, $index, $length);

                if ($end === false) {
                    break;
                }

                if ($state === 'before-attribute' || $state === 'after-attribute') {
                    $attributeName = '';
                    $pendingValueOwner = '';
                }

                $state = $this->stateAfterAntlers($state);
                $index = $end;

                continue;
            }

            $char = $markup[$index];

            if ($state === 'double-quoted' || $state === 'single-quoted') {
                if ($this->isClosingQuote($state, $char)) {
                    $state = 'before-attribute';
                    $attributeName = '';
                }

                $index++;

                continue;
            }

            switch ($state) {
                case 'tag-name':
                    if ($this->isHtmlSpace($char) || $char === '/') {
                        $state = 'before-attribute';
                    } elseif ($char === '>') {
                        $state = 'done';
                    } else {
                        $tagName .= $char;
                    }
                    break;
                case 'before-attribute':
                    if (! $this->isHtmlSpace($char) && $char !== '/' && $char !== '>') {
                        $state = 'attribute-name';
                        $attributeName = $char;
                    }
                    break;
                case 'attribute-name':
                    if ($char === '=') {
                        $pendingValueOwner = $attributeName;
                        $state = 'before-value';
                    } elseif ($this->isHtmlSpace($char)) {
                        $pendingValueOwner = $attributeName;
                        $state = 'after-attribute';
                    } elseif ($char === '/' || $char === '>') {
                        $state = 'before-attribute';
                        $attributeName = '';
                    } else {
                        $attributeName .= $char;
                    }
                    break;
                case 'after-attribute':
                    if ($char === '=') {
                        $state = 'before-value';
                    } elseif ($char === '/') {
                        $state = 'before-attribute';
                        $attributeName = '';
                    } elseif (! $this->isHtmlSpace($char)) {
                        $state = 'attribute-name';
                        $attributeName = $char;
                    }
                    break;
                case 'before-value':
                    if ($char === '"') {
                        $state = 'double-quoted';
                    } elseif ($char === "'") {
                        $state = 'single-quoted';
                    } elseif (! $this->isHtmlSpace($char)) {
                        $state = 'unquoted-value';
                    }
                    break;
                case 'unquoted-value':
                    if ($this->isHtmlSpace($char)) {
                        $state = 'before-attribute';
                        $attributeName = '';
                    }
                    break;
            }

            $index++;
        }

        foreach ($needed as $offset => $unused) {
            if (! isset($classified[$offset])) {
                $classified[$offset] = ['tag-open', null, ''];
            }
        }

        return $classified;
    }

    /** @return array{0: string, 1: string|null, 2: string} */
    protected function classifyTagState($markup, $index, $length, $state, $tagName, $attributeName, $pendingValueOwner)
    {
        switch ($state) {
            case 'tag-name':
                return ['element-name', null, $tagName];
            case 'attribute-name':
                return ['attribute-name', $this->normalizedAttributeName($attributeName), $attributeName];
            case 'double-quoted':
            case 'single-quoted':
            case 'unquoted-value':
            case 'before-value':
                $owner = $this->attributeValueOwner($state, $attributeName, $pendingValueOwner);

                return ['attribute-value', $this->normalizedAttributeName($owner), ''];
            case 'before-attribute':
            case 'after-attribute':
                $lookahead = $index;

                if (substr($markup, $lookahead, 2) === '{{') {
                    $end = $this->antlersEndInBytes($markup, $lookahead, $length);

                    if ($end === false) {
                        $lookahead = $length;
                    } else {
                        $lookahead = $end;
                    }
                }

                while ($lookahead < $length && $this->isHtmlSpace($markup[$lookahead])) {
                    $lookahead++;
                }

                if (($markup[$lookahead] ?? null) === '=' && $state === 'before-attribute') {
                    return ['attribute-name', null, ''];
                }

                return ['tag-open', null, ''];
            default:
                return ['tag-open', null, ''];
        }
    }

    protected function stateAfterAntlers($state)
    {
        return match ($state) {
            'before-value' => 'unquoted-value',
            'before-attribute', 'after-attribute' => 'attribute-name',
            default => $state,
        };
    }

    protected function normalizedAttributeName($name)
    {
        if ($name === '') {
            return null;
        }

        return strtolower($name);
    }

    protected function isClosingQuote($state, $char)
    {
        if ($state === 'double-quoted') {
            return $char === '"';
        }

        return $state === 'single-quoted' && $char === "'";
    }

    protected function attributeValueOwner($state, $attributeName, $pendingValueOwner)
    {
        if ($state === 'before-value' && $pendingValueOwner) {
            return $pendingValueOwner;
        }

        if ($attributeName) {
            return $attributeName;
        }

        return $pendingValueOwner;
    }

    public function hasAttribute($openingTag, $attributeName)
    {
        if (stripos($openingTag, $attributeName) === false) {
            return false;
        }

        $length = strlen($openingTag);
        $index = 1;

        while ($index < $length
            && ! HtmlSpec::isTagNameDelimiter($openingTag[$index])) {
            if (substr($openingTag, $index, 2) === '{{') {
                $end = $this->antlersEndInBytes($openingTag, $index, $length);

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

            if (substr($openingTag, $index, 2) === '{{') {
                $end = $this->antlersEndInBytes($openingTag, $index, $length);

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
                if (substr($openingTag, $index, 2) === '{{') {
                    $dynamicName = true;
                    $end = $this->antlersEndInBytes($openingTag, $index, $length);

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
                    if (substr($openingTag, $index, 2) === '{{') {
                        $end = $this->antlersEndInBytes($openingTag, $index, $length);

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
                    if (substr($openingTag, $index, 2) === '{{') {
                        $end = $this->antlersEndInBytes($openingTag, $index, $length);

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

    protected function isHtmlSpace($char)
    {
        return HtmlSpec::isHtmlSpace($char);
    }

    /** @return int|false Offset immediately after the closing delimiter. */
    protected function antlersEndInBytes($content, $start, $length)
    {
        return RegionExtents::endInBytes($content, $start, $length);
    }
}
