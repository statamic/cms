<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Nodes\Paths\AccessorNode;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class PathParser
{
    const ColonSeparator = ':';
    const LeftBracket = '[';
    const RightBracket = ']';
    const DotPathSeparator = '.';

    /**
     * A cache of parsed path nodes.
     *
     * @var array
     */
    protected static $pathCache = [];

    private $chars = [];
    private $currentIndex = 0;
    private $lastIndex = 0;
    private $inputLength = 0;
    private $prev = null;
    private $cur = null;
    private $next = null;
    private $isParsingString = false;
    private $isStringKeyPath = false;

    private function isValidChar($char)
    {
        if ($this->isParsingString == false && ctype_space($char)) {
            return false;
        }

        if ($this->isParsingString == false && $char == self::LeftBracket) {
            return false;
        }

        if ($this->isParsingString == false && $char == self::RightBracket) {
            return false;
        }

        return true;
    }

    public static function normalizePath($path)
    {
        $path = str_replace(':', '_', $path);
        $path = str_replace('.', '_', $path);
        $path = str_replace('[', '_', $path);

        return str_replace(']', '_', $path);
    }

    public function parse($content)
    {
        // Note: this second call to clone is not an accident.
        if (isset(self::$pathCache[$content])) {
            return clone self::$pathCache[$content];
        }

        StringUtilities::prepareSplit($content);

        $originalContent = $content;

        $isStrictVariableReference = false;
        $isExplicitVariableReference = false;
        $isVariableVariable = false;
        $isStrictTagReference = false;

        if (Str::startsWith($content, DocumentParser::AtChar)) {
            $isVariableVariable = true;
            $content = StringUtilities::substr($content, 1);
        }

        if (Str::startsWith($content, DocumentParser::Punctuation_Percent)) {
            $isStrictTagReference = true;
            $content = StringUtilities::substr($content, 1);
        } else {
            if (Str::startsWith($content, DocumentParser::Punctuation_Dollar)) {
                $isStrictVariableReference = true;
                $content = StringUtilities::substr($content, 1);
            }

            // If it still starts with a $, it's an explicit var reference.
            if (Str::startsWith($content, DocumentParser::Punctuation_Dollar)) {
                $isExplicitVariableReference = true;
                $content = StringUtilities::substr($content, 1);
            }
        }

        $this->chars = [];
        $this->currentIndex = 0;
        $this->lastIndex = 0;
        $this->inputLength = 0;
        $this->cur = null;
        $this->prev = null;
        $this->next = null;

        $this->chars = StringUtilities::split($content);
        $this->inputLength = count($this->chars);
        $this->lastIndex = $this->inputLength - 1;

        $isParsingAccessor = false;
        $currentChars = [];
        /** @var PathNode[]|VariableReference[] $parts */
        $parts = [];

        $activeDelimiter = self::ColonSeparator;
        $this->isParsingString = false;
        $ignorePrevious = false;
        $terminator = null;
        $isStringVar = false;
        $addCur = true;

        for ($this->currentIndex; $this->currentIndex < $this->inputLength; $this->currentIndex += 1) {
            $this->cur = $this->chars[$this->currentIndex];

            $this->next = null;

            if (! $ignorePrevious) {
                if ($this->currentIndex > 0) {
                    $this->prev = $this->chars[$this->currentIndex - 1];
                }
            } else {
                $ignorePrevious = false;
                $this->prev = '';
            }

            if ($this->currentIndex + 1 < $this->inputLength) {
                $this->next = $this->chars[$this->currentIndex + 1];
            }

            if ($this->isParsingString == false && $this->cur == self::ColonSeparator) {
                $activeDelimiter = self::ColonSeparator;
            }

            if ($this->isParsingString == false && $this->cur == self::DotPathSeparator) {
                $activeDelimiter = self::DotPathSeparator;
            }

            if ($this->isParsingString && $this->cur == DocumentParser::String_EscapeCharacter && $this->next == $terminator) {
                $currentChars[] = $terminator;
                $this->currentIndex += 1;

                continue;
            }

            if ($this->cur == DocumentParser::String_Terminator_SingleQuote || $this->cur == DocumentParser::String_Terminator_DoubleQuote) {
                if ($this->isParsingString) {
                    if ($this->cur == $terminator) {
                        if ($this->prev == DocumentParser::String_EscapeCharacter) {
                            $currentChars[] = $this->cur;

                            continue;
                        } else {
                            $this->isParsingString = false;
                            $addCur = false;
                        }
                    } else {
                        $currentChars[] = $this->cur;

                        continue;
                    }
                } else {
                    $this->isParsingString = true;
                    $isStringVar = true;
                    $terminator = $this->cur;
                    $this->isStringKeyPath = true;

                    continue;
                }
            }

            if ($this->isParsingString) {
                if (ctype_space($this->cur)) {
                    $currentChars[] = $this->cur;

                    continue;
                } elseif ($this->cur == DocumentParser::String_EscapeCharacter) {
                    if ($this->next == DocumentParser::String_EscapeCharacter) {
                        $currentChars[] = DocumentParser::String_EscapeCharacter;
                        $this->currentIndex += 1;
                        $ignorePrevious = true;

                        continue;
                    } elseif ($this->next == 'n') {
                        $currentChars[] = "\n";
                        $this->currentIndex += 1;

                        continue;
                    } elseif ($this->next == 't') {
                        $currentChars[] = "\t";
                        $this->currentIndex += 1;

                        continue;
                    } elseif ($this->next == 'r') {
                        $currentChars[] = "\r";
                        $this->currentIndex += 1;

                        continue;
                    }
                }
            }

            if (
                $this->isParsingString == false && $this->cur == self::LeftBracket &&
                (
                    ctype_alnum($this->prev) ||
                    $this->prev == DocumentParser::LeftBrace ||
                    $this->prev == DocumentParser::RightBracket ||
                    $this->prev == DocumentParser::Punctuation_FullStop
                )
            ) {
                if (! empty($currentChars)) {
                    $pathNode = new PathNode();
                    $pathNode->name = implode($currentChars);
                    $pathNode->delimiter = $activeDelimiter;
                    $parts[] = $pathNode;
                    $currentChars = [];
                }

                if ($this->next == null || ctype_space($this->next)) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_ILLEGAL_VARPATH_SPACE_RIGHT,
                        null,
                        'Unexpected end of input or whitespace while parsing variable accessor path.'
                    );
                }

                $results = $this->locateEndOfAccessor();
                $this->currentIndex = $results[0];
                $parser = new PathParser();

                $parts[] = $parser->parse($results[1]);

                $isParsingAccessor = true;

                continue;
            }

            if ($this->isParsingString == false && $this->next == self::RightBracket && $isParsingAccessor) {
                if ($addCur) {
                    $currentChars[] = $this->cur;
                } else {
                    $addCur = true;
                }

                $accessorNode = new AccessorNode();
                $accessorNode->name = implode($currentChars);

                $parts[] = $accessorNode;
                $currentChars = [];
                $activeDelimiter = self::ColonSeparator;
                $this->currentIndex += 1;
                $isParsingAccessor = false;

                continue;
            }

            if ($this->isParsingString == false && ($this->cur == self::LeftBracket || $this->cur == self::ColonSeparator ||
                $this->cur == self::DotPathSeparator || $this->currentIndex == $this->lastIndex)) {
                if ($this->next == null || ctype_space($this->next)) {
                    if ($this->cur == self::ColonSeparator) {
                        if (count($parts) > 0 && $parts[count($parts) - 1] instanceof PathNode) {
                            $lastPart = $parts[count($parts) - 1];

                            if ($lastPart->delimiter == self::ColonSeparator) {
                                $lastPart->delimiter .= $this->cur;

                                continue;
                            }
                        }

                        if (count($currentChars) == 0) {
                            throw ErrorFactory::makeSyntaxError(
                                AntlersErrorCodes::TYPE_UNEXPECTED_BRANCH_SEPARATOR,
                                null,
                                'Unexpected [T_BRANCH_SEPARATOR] while parsing input text.'
                            );
                        }

                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ILLEGAL_VARPATH_RIGHT,
                            null,
                            'Variable paths cannot end with the ":" character.'
                        );
                    }

                    if ($this->cur == self::LeftBracket) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ILLEGAL_VARPATH_SUBPATH_START,
                            null,
                            'Illegal variable sub-path start.'
                        );
                    }
                }

                if ($this->currentIndex == $this->lastIndex && $this->isValidChar($this->cur)) {
                    if ($addCur) {
                        $currentChars[] = $this->cur;
                    } else {
                        $addCur = true;
                    }
                }

                $pathNode = new PathNode();
                $pathNode->delimiter = $activeDelimiter;
                $pathNode->name = implode($currentChars);

                if ($isStringVar) {
                    $pathNode->isStringVar = $isStringVar;
                    $pathNode->name = StringUtilities::substr($originalContent, 1);
                    $pathNode->name = StringUtilities::substr($pathNode->name, 0, -1);
                    $isStringVar = false;
                }

                $parts[] = $pathNode;
                $currentChars = [];

                continue;
            } else {
                $currentChars[] = $this->cur;

                continue;
            }
        }

        $partLen = count($parts);

        if ($partLen > 0) {
            $parts[$partLen - 1]->isFinal = true;
        }

        $variableReference = new VariableReference();
        $variableReference->isStrictTagReference = $isStrictTagReference;
        $variableReference->isStrictVariableReference = $isStrictVariableReference;
        $variableReference->isExplicitVariableReference = $isExplicitVariableReference;
        $variableReference->pathParts = $parts;
        $variableReference->originalContent = $content;
        $variableReference->isVariableVariable = $isVariableVariable;
        $variableReference->normalizedReference = str_replace(':', '.', $content);

        self::$pathCache[$originalContent] = clone $variableReference;

        return $variableReference;
    }

    private function locateEndOfAccessor()
    {
        $nestedChars = [];
        $bracketCount = 1;
        $foundOn = -1;
        $nestedContent = '';
        $isParsingString = false;
        $terminator = null;

        // If an accessor starts with a string delimiter,
        // we can switch to string parsing here within
        // the accessor location code, without having
        // to pass state globally or directly here.

        for ($i = $this->currentIndex + 1; $i < $this->inputLength; $i++) {
            $cur = $this->chars[$i];
            $next = null;
            $prev = null;

            if ($i > 0) {
                $prev = $this->chars[$i - 1];
            }

            if ($i + 1 < $this->inputLength) {
                $next = $this->chars[$i + 1];
            }

            if ($isParsingString == false && (
                $cur == DocumentParser::String_Terminator_SingleQuote ||
                $cur == DocumentParser::String_Terminator_DoubleQuote
            )) {
                $isParsingString = true;
                $terminator = $cur;
                $nestedChars[] = $cur;

                continue;
            }

            if ($isParsingString == false && $cur == self::LeftBracket) {
                $bracketCount += 1;
                $nestedChars[] = $cur;

                continue;
            }

            if ($isParsingString && $cur == $terminator && $prev != DocumentParser::String_EscapeCharacter) {
                $nestedChars[] = $cur;
                $isParsingString = false;
                $terminator = null;

                continue;
            }

            if ($isParsingString == false && $cur == self::RightBracket) {
                $bracketCount -= 1;

                if ($bracketCount == 0) {
                    $foundOn = $i;
                    $nestedContent = implode($nestedChars);
                    break;
                } else {
                    $nestedChars[] = $cur;

                    continue;
                }
            } else {
                $nestedChars[] = $cur;
            }

            if ($isParsingString == false && ctype_space($next)) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_UNEXPECTED_EOI_VARPATH_ACCESSOR,
                    null,
                    'Unexpected end of input or whitespace while parsing inner variable accessor path.'
                );
            }

            if ($next == null) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_UNEXPECTED_EOI_VARPATH_ACCESSOR,
                    null,
                    'Unexpected end of input or whitespace while parsing inner variable accessor path.'
                );
            }
        }

        return [$foundOn, $nestedContent];
    }
}
