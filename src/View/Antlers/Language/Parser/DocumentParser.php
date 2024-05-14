<?php

namespace Statamic\View\Antlers\Language\Parser;

use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Analyzers\RecursiveParentAnalyzer;
use Statamic\View\Antlers\Language\Analyzers\TagPairAnalyzer;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\ParserFailNode;
use Statamic\View\Antlers\Language\Nodes\Position;
use Statamic\View\Antlers\Language\Nodes\Structures\PhpExecutionNode;
use Statamic\View\Antlers\Language\Nodes\TagIdentifier;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class DocumentParser
{
    const K_CHAR = 'char';
    const K_LINE = 'line';

    const NewLine = "\n";
    const AtChar = '@';
    const LeftBrace = '{';
    const RightBrace = '}';
    const LeftBracket = '[';
    const RightBracket = ']';
    const String_EscapeCharacter = '\\';
    const String_Terminator_DoubleQuote = '"';
    const String_Terminator_SingleQuote = "'";
    const Punctuation_Question = '?';
    const Punctuation_Equals = '=';
    const Punctuation_Comma = ',';
    const Punctuation_Colon = ':';
    const Punctuation_Semicolon = ';';
    const Punctuation_Exclamation = '!';
    const Punctuation_Pipe = '|';
    const Punctuation_Ampersand = '&';
    const Punctuation_LessThan = '<';
    const Punctuation_GreaterThan = '>';
    const Punctuation_Octothorp = '#';
    const Punctuation_Tilde = '~';
    const Punctuation_FullStop = '.';
    const Punctuation_Dollar = '$';
    const Punctuation_Asterisk = '*';
    const Punctuation_Percent = '%';
    const Punctuation_Plus = '+';
    const Punctuation_Minus = '-';
    const Punctuation_Underscore = '_';
    const Punctuation_ForwardSlash = '/';
    const Punctuation_Caret = '^';

    const LeftParen = '(';
    const RightParent = ')';

    private $interpolationRegions = [];

    /**
     * @var AntlersNodeParser|null
     */
    private $nodeParser = null;
    private $chars = [];
    private $charLen = 0;

    /**
     * Maintains a reference to the last produced AntlersNode to reduce lookups.
     *
     * @var AntlersNode|null
     */
    private $lastAntlersNode = null;

    private $content = '';
    private $currentIndex = 0;
    private $currentContent = [];
    private $startIndex = 0;
    private $cur = null;
    private $next = null;
    private $prev = null;
    private $nodes = [];
    private $renderNodes = [];

    private $isInterpolatedParser = false;

    private $inputLen = 0;
    private $documentOffsets = [];
    private $isDoubleBrace = false;
    private $interpolationEndOffsets = [];
    private $seedStartLine = 1;
    private $seedStartChar = 1;
    private $lastAntlersEndIndex = -1;
    private $seedOffset = 0;

    private $antlersStartIndex = [];
    private $antlersStartPositionIndex = [];
    private $chunkSize = 5;
    private $currentChunkOffset = 0;
    private $jumpToIndex = null;

    private $interpolatedCollisions = [];
    private $interpolatedCollisionCount = [];
    private $threeCharCollisionCount = -1;
    private $threeCharCollisions = [];
    private $isVirtual = false;
    private $mayBeStartingEscapedContent = false;
    private $isParsingEscapedContent = false;
    private $escapedContentEndSymbol = null;
    private $escapedContentSymbolEncountered = 0;

    /**
     * A list of node visitors.
     *
     * @var NodeVisitorContract[]
     */
    protected $visitors = [];

    public function __construct()
    {
        $this->nodeParser = new AntlersNodeParser();
    }

    public function getText($start, $end)
    {
        return StringUtilities::substr($this->content, $start, $end - $start);
    }

    public function setIsInterpolatedParser($isInterpolation)
    {
        $this->isInterpolatedParser = $isInterpolation;

        return $this;
    }

    public function setStartLineSeed($startLine)
    {
        $this->seedStartLine = $startLine;

        return $this;
    }

    public function setSeedStartChar($startChar)
    {
        $this->seedStartChar = $startChar;

        return $this;
    }

    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;
    }

    /**
     * Fetches content from the source content without appending characters to the current char list.
     *
     * @param  int  $count  The number of characters to fetch.
     * @return string
     */
    private function fetch($count)
    {
        return mb_substr($this->content, $this->currentChunkOffset + $this->chunkSize - count($this->chars), $count);
    }

    private function fetchAt($location, $count)
    {
        return mb_substr($this->content, $location, $count);
    }

    public function getParsedContent()
    {
        return $this->content;
    }

    private function peek($count)
    {
        if ($count == $this->charLen) {
            $nextChunk = mb_str_split(mb_substr($this->content, $this->currentChunkOffset + $this->chunkSize, $this->chunkSize));
            $this->currentChunkOffset += $this->chunkSize;

            foreach ($nextChunk as $nextChar) {
                $this->chars[] = $nextChar;
                $this->charLen += 1;
            }
        }

        return $this->chars[$count];
    }

    public function parseIntermediateText()
    {
        $this->currentContent = [];
        $this->startIndex = 0;

        $this->chars = mb_str_split(mb_substr($this->content, $this->currentChunkOffset, $this->chunkSize));
        $this->charLen = count($this->chars);

        for ($this->currentIndex = 0; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->cur == self::LeftBrace && $this->next == self::LeftBrace && $this->prev == self::AtChar) {
                $this->dumpLiteralNode($this->currentIndex);

                $escapeNode = new EscapedContentNode();
                $escapeNode->name = new TagIdentifier();
                $escapeNode->name->name = 'noparse';

                $escapeNode->content = '{{';
                $escapeNode->startPosition = $this->positionFromOffset(
                    $this->currentIndex + $this->seedOffset,
                    $this->currentIndex + $this->seedOffset
                );
                $escapeNode->endPosition = $this->positionFromOffset(
                    $this->currentIndex + $this->seedOffset,
                    $this->currentIndex + $this->seedOffset
                );

                $this->nodes[] = $escapeNode;
                $this->currentContent = [];
                $this->currentIndex += 1;

                continue;
            }

            if (($this->prev == null || ($this->prev != null && $this->prev != self::AtChar))
                && $this->next != null && $this->cur == self::LeftBrace
                && $this->next == self::LeftBrace) {
                $this->dumpLiteralNode($this->currentIndex);

                $peek = null;

                if ($this->currentIndex + 2 < $this->inputLen) {
                    $peek = $this->peek($this->currentIndex + 2);
                }

                if ($peek == self::Punctuation_Question) {
                    $this->isDoubleBrace = true;
                    $this->currentIndex += 3;
                    $this->scanToEndOfPhpRegion(self::Punctuation_Question);
                    $this->isDoubleBrace = false;
                    break;
                }

                if ($peek == self::Punctuation_Dollar) {
                    $this->isDoubleBrace = true;
                    $this->currentIndex += 3;
                    $this->scanToEndOfPhpRegion(self::Punctuation_Dollar);
                    $this->isDoubleBrace = false;
                    break;
                }

                if ($peek == self::Punctuation_Octothorp) {
                    $this->isDoubleBrace = true;
                    $this->currentIndex += 3;
                    $this->scanToEndOfAntlersCommentRegion();

                    $this->isDoubleBrace = false;

                    break;
                }

                // Advances over the {{.
                $this->startIndex = $this->currentIndex;

                $this->isDoubleBrace = true;
                $this->currentIndex += 2;
                $this->scanToEndOfAntlersRegion();
                $this->isDoubleBrace = false;

                break;
            }

            if ($this->cur == self::AtChar && $this->next != null && $this->next == self::LeftBrace) {
                if ($this->currentIndex + 2 >= $this->inputLen) {
                    $this->currentContent[] = $this->next;
                    $this->dumpLiteralNode($this->currentIndex + 1);
                    break;
                }

                $leftBraceCount = 0;

                for ($countIndex = $this->currentIndex + 1; $countIndex < $this->inputLen; $countIndex++) {
                    $subChar = $this->chars[$countIndex];

                    if ($subChar == self::LeftBrace) {
                        $leftBraceCount += 1;
                    } else {
                        break;
                    }
                }

                $this->currentContent = array_merge(
                    $this->currentContent,
                    StringUtilities::split(str_repeat(self::LeftBrace, $leftBraceCount))
                );
                $this->currentIndex += $leftBraceCount;

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->next == null && ! empty($this->currentContent)) {
                $this->dumpLiteralNode($this->currentIndex);
            }
        }
    }

    public function getRenderNodes()
    {
        return $this->renderNodes;
    }

    private function processInputText($input)
    {
        $this->content = StringUtilities::normalizeLineEndings($input);
        $this->inputLen = mb_strlen($this->content);

        // The document content was normalized, so we can search for "\n".
        preg_match_all('/\n/', $this->content, $documentNewLines, PREG_OFFSET_CAPTURE);
        $newLineCountLen = count($documentNewLines[0]);

        $currentLine = $this->seedStartLine;
        $lastOffset = null;
        for ($i = 0; $i < $newLineCountLen; $i++) {
            $thisNewLine = $documentNewLines[0][$i];
            $thisIndex = $thisNewLine[1];
            $indexChar = $thisIndex;

            if ($lastOffset != null) {
                $indexChar = $thisIndex - $lastOffset;
            } else {
                $indexChar = $indexChar + 1;
            }

            $this->documentOffsets[$thisIndex] = [
                self::K_CHAR => $indexChar,
                self::K_LINE => $currentLine,
            ];

            $currentLine += 1;
            $lastOffset = $thisIndex;
        }

        preg_match_all('/@?{{/', $this->content, $antlersStartCandidates);

        $lastAntlersOffset = 0;
        $lastWasEscaped = false;
        foreach ($antlersStartCandidates[0] as $antlersRegion) {
            if (Str::startsWith($antlersRegion, '@')) {
                $lastAntlersOffset = mb_strpos($this->content, $antlersRegion, $lastAntlersOffset) + 2;
                $lastWasEscaped = true;

                continue;
            }

            $offset = mb_strpos($this->content, $antlersRegion, $lastAntlersOffset);

            if ($lastWasEscaped) {
                if ($lastAntlersOffset == $offset) {
                    $lastAntlersOffset = $offset;

                    continue;
                }
            }

            $this->antlersStartIndex[] = $offset;
            $this->antlersStartPositionIndex[$offset] = 1;
            $lastAntlersOffset = $offset + 2;
            $lastWasEscaped = false;
        }

        return true;
    }

    /**
     * Performs literal escape logic on the input string.
     *
     * @param  string  $content  The input content.
     * @return string
     */
    private function prepareLiteralContent($content)
    {
        return str_replace('@{{', '{{', $content);
    }

    /**
     * Parses the input text and produces a collection of nodes.
     *
     * @param  string  $text  The text to parse.
     * @return array
     *
     * @throws SyntaxErrorException
     */
    public function parse($text)
    {
        $this->resetState();

        if (! $this->processInputText($text)) {
            return [];
        }

        StringUtilities::prepareSplit($text);

        $indexCount = count($this->antlersStartIndex);
        $lastIndex = $indexCount - 1;

        if ($indexCount == 0) {
            $fullDocumentLiteral = new LiteralNode();
            $fullDocumentLiteral->isVirtual = $this->isVirtual;
            $fullDocumentLiteral->content = $this->prepareLiteralContent($this->content);
            $fullDocumentLiteral->startPosition = $this->positionFromOffset(0, 0);
            $fullDocumentLiteral->endPosition = $this->positionFromOffset($this->inputLen - 1, $this->inputLen - 1);
            $this->nodes[] = $fullDocumentLiteral;
        } else {
            for ($i = 0; $i < $indexCount; $i++) {
                $offset = $this->antlersStartIndex[$i];
                $this->seedOffset = $offset;

                if ($i == 0 && $offset > 0) {
                    // Create a literal node representing the start of the document.
                    $node = new LiteralNode();
                    $node->isVirtual = $this->isVirtual;
                    $node->content = $this->prepareLiteralContent(StringUtilities::substr($this->content, 0, $offset));

                    if (! strlen($node->content) == 0) {
                        $node->startPosition = $this->positionFromOffset(0, 0);
                        $node->endPosition = $this->positionFromOffset($offset, $offset);
                        $this->nodes[] = $node;
                    }
                }

                if ($offset < $this->lastAntlersEndIndex) {
                    continue;
                }

                $this->currentChunkOffset = $offset;
                $this->resetIntermediateState();
                $this->parseIntermediateText();

                if ($this->jumpToIndex != null) {
                    $i = $this->jumpToIndex - 1;
                    $this->jumpToIndex = null;

                    continue;
                }

                if ($this->lastAntlersNode != null && $this->lastAntlersNode instanceof PhpExecutionNode == false && $this->lastAntlersNode->isComment) {
                    if ($i + 1 < $indexCount) {
                        $nextAntlersStart = $this->antlersStartIndex[$i + 1];

                        if ($nextAntlersStart < $this->lastAntlersNode->endPosition->offset) {
                            // We want to skip over any potential candidates
                            // now to avoid having to process them later.

                            $skipIndex = null;

                            for ($j = $i + 1; $j < $indexCount; $j++) {
                                if ($this->antlersStartIndex[$j] > $this->lastAntlersNode->endPosition->offset) {
                                    $skipIndex = $j;
                                    break;
                                }
                            }

                            // Drop a literal node, and break.
                            if ($skipIndex == null) {
                                $content = $this->prepareLiteralContent(StringUtilities::substr($this->content, $this->lastAntlersNode->endPosition->offset + 1));

                                if (strlen($content) > 0) {
                                    $node = new LiteralNode();
                                    $node->isVirtual = $this->isVirtual;
                                    $node->content = $content;

                                    $literalStartOffset = $this->lastAntlersNode->endPosition->offset + 1;

                                    $node->startPosition = $this->positionFromOffset($literalStartOffset, $literalStartOffset);
                                    $node->endPosition = $this->positionFromOffset($this->inputLen, $this->inputLen);
                                    $this->nodes[] = $node;
                                }

                                break;
                            } else {
                                // Account for literals between a skipped region. If the span length
                                // is greater than zero, we just left a region where we skipped
                                // a few Antlers-like nodes, but will encounter literal content
                                // before we hit the start of the next Antlers start candidate.
                                $nextStart = $this->antlersStartIndex[$skipIndex];
                                $spanLen = $nextStart - $this->lastAntlersNode->endPosition->offset - 1;

                                if ($spanLen > 0) {
                                    $spanStart = $this->lastAntlersNode->endPosition->offset;
                                    $spanEnd = $nextStart - 1;

                                    $spanStart += 1;
                                    $spanEnd -= 1;

                                    $content = StringUtilities::substr($this->content, $spanStart, $spanLen);

                                    if (strlen($content) > 0) {
                                        $node = new LiteralNode();
                                        $node->isVirtual = $this->isVirtual;
                                        $node->content = $content;

                                        $node->startPosition = $this->positionFromOffset($spanStart, $spanStart);
                                        $node->endPosition = $this->positionFromOffset($spanEnd, $spanEnd);
                                        $this->nodes[] = $node;
                                    }

                                    continue;
                                }

                                $i = $skipIndex - 1;

                                continue;
                            }
                        }
                    }
                }

                if ($i !== $lastIndex && $this->lastAntlersNode != null) {
                    $startCandidate = $this->positionFromOffset($offset, $offset);

                    // Skip processing potential nodes that are inside the last node.
                    if ($startCandidate->isBefore($this->lastAntlersNode->endPosition)) {
                        if ($i + 1 < $indexCount) {
                            $nextAntlersStart = $this->antlersStartIndex[$i + 1];

                            if ($nextAntlersStart < $this->lastAntlersNode->endPosition->offset) {
                                if ($i + 2 < $indexCount) {
                                    $nextAntlersStart = $this->antlersStartIndex[$i + 2];
                                } else {
                                    $literalStart = $this->lastAntlersNode->endPosition->offset + 1;
                                    $finalContent = $this->prepareLiteralContent(StringUtilities::substr($this->content, $literalStart));

                                    if (! strlen($finalContent) == 0) {
                                        $finalLiteral = new LiteralNode();
                                        $finalLiteral->isVirtual = $this->isVirtual;
                                        $finalLiteral->content = $finalContent;
                                        $finalLiteral->startPosition = $this->positionFromOffset($literalStart, $literalStart);
                                        $finalLiteral->endPosition = $this->positionFromOffset($this->inputLen - 1, $literalStart);
                                        $this->nodes[] = $finalLiteral;
                                        break;
                                    }

                                    continue;
                                }
                            }
                        } else {
                            if ($i + 1 != $lastIndex) {
                                continue;
                            }
                        }
                    }
                }

                if ($i + 1 < $indexCount) {
                    $nextAntlersStart = $this->antlersStartIndex[$i + 1];
                    $literalStartIndex = $this->lastAntlersEndIndex + 1;

                    if ($nextAntlersStart < $literalStartIndex) {
                        if ($this->lastAntlersEndIndex > $nextAntlersStart) {
                            $skipIndex = null;
                            for ($j = $i; $j < $indexCount; $j++) {
                                if ($this->antlersStartIndex[$j] > $this->lastAntlersEndIndex) {
                                    $skipIndex = $this->antlersStartIndex[$j];
                                    break;
                                }
                            }

                            if ($skipIndex != null) {
                                $nextAntlersStart = $skipIndex;
                            } else {
                                // In this scenario, we will create the last trailing literal node and break.
                                $thisOffset = $this->currentChunkOffset;
                                $content = StringUtilities::substr($this->content, $literalStartIndex);

                                $node = new LiteralNode();

                                $node->isVirtual = $this->isVirtual;
                                $node->content = $this->prepareLiteralContent($content);

                                if (! strlen($node->content) == 0) {
                                    $node->startPosition = $this->positionFromOffset($thisOffset, $thisOffset);
                                    $node->endPosition = $this->positionFromOffset($nextAntlersStart, $thisOffset);
                                    $this->nodes[] = $node;
                                }
                                break;
                            }
                        } else {
                            continue;
                        }
                    }

                    if ($i + 1 == $lastIndex && ($nextAntlersStart <= $this->lastAntlersEndIndex)) {
                        // In this scenario, we will create the last trailing literal node and break.
                        $thisOffset = $this->currentChunkOffset;
                        $content = StringUtilities::substr($this->content, $literalStartIndex);

                        $node = new LiteralNode();

                        $node->isVirtual = $this->isVirtual;
                        $node->content = $this->prepareLiteralContent($content);

                        if (! strlen($node->content) == 0) {
                            $node->startPosition = $this->positionFromOffset($thisOffset, $thisOffset);
                            $node->endPosition = $this->positionFromOffset($nextAntlersStart, $thisOffset);
                            $this->nodes[] = $node;
                        }

                        break;
                    } else {
                        $literalLength = $nextAntlersStart - $this->lastAntlersEndIndex - 1;

                        if ($literalLength == 0) {
                            continue;
                        }

                        $thisOffset = $this->currentChunkOffset;

                        if ($this->lastAntlersNode instanceof PhpExecutionNode) {
                            $literalStartIndex -= 1;
                            $literalLength += 1;
                        }

                        $content = StringUtilities::substr($this->content, $literalStartIndex, $literalLength);

                        $node = new LiteralNode();

                        $node->isVirtual = $this->isVirtual;
                        $node->content = $this->prepareLiteralContent($content);

                        if (! strlen($node->content) == 0) {
                            $node->startPosition = $this->positionFromOffset($thisOffset, $thisOffset);
                            $node->endPosition = $this->positionFromOffset($nextAntlersStart, $thisOffset);
                            $this->nodes[] = $node;
                        }
                    }

                    continue;
                }

                if ($i == $lastIndex) {
                    $literalStart = $this->currentIndex + $offset;

                    if ($literalStart < $this->inputLen) {
                        $node = new LiteralNode();

                        $node->isVirtual = $this->isVirtual;
                        $node->content = $this->prepareLiteralContent(StringUtilities::substr($this->content, $literalStart));

                        if (! strlen($node->content) == 0) {
                            $node->startPosition = $this->positionFromOffset($literalStart, $literalStart);
                            $node->endPosition = $this->positionFromOffset($this->inputLen - 1, $literalStart);
                            $this->nodes[] = $node;
                        }
                        break;
                    }
                }
            }
        }

        $index = 0;
        /** @var AbstractNode $node */
        foreach ($this->nodes as $node) {
            $node->index = $index;
            $index += 1;
        }

        foreach ($this->nodes as $node) {
            if ($node instanceof AntlersNode && ! empty($node->interpolationRegions)) {
                foreach ($node->interpolationRegions as $varName => $content) {
                    $docParser = new DocumentParser();
                    $docParser->setIsInterpolatedParser(true);

                    $parseResults = $docParser->parse($content);

                    if (count($parseResults) > 1 && $parseResults[1] instanceof AntlersNode) {
                        $parseResults = [$parseResults[1]];
                    } elseif (count($parseResults) == 1 && ($parseResults[0] instanceof AntlersNode) == false) {
                        $parseResults = [];
                    }

                    $node->processedInterpolationRegions[$varName] = $parseResults;
                }
                $node->hasProcessedInterpolationRegions = true;
            }
        }

        $tagPairAnalyzer = new TagPairAnalyzer();
        $this->renderNodes = $tagPairAnalyzer->associate($this->nodes, $this);

        foreach ($this->nodes as $node) {
            if ($node instanceof AntlersNode && $node->isClosingTag && $node->isOpenedBy == null) {
                $errorMessage = 'Unpaired closing tag.';

                if ($node->isInterpolationNode) {
                    $errorMessage .= ' Tag pairs are not supported within Antlers tags.';
                }

                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_UNPAIRED_CLOSING_TAG,
                    $node,
                    $errorMessage
                );
            }
        }

        RecursiveParentAnalyzer::associateRecursiveParent($this->nodes);

        if (count($this->nodes) >= 2) {
            if ($this->nodes[0] instanceof AntlersNode && $this->nodes[0]->isComment) {
                if ($this->nodes[1] instanceof LiteralNode) {
                    $this->nodes[1]->content = ltrim($this->nodes[1]->content);
                }
            }
        }

        foreach ($this->nodes as $node) {
            if ($node instanceof AntlersNode) {
                $node->isInterpolationNode = $this->isInterpolatedParser;
            }

            if ($node instanceof AntlersNode && ! empty($node->interpolationRegions)) {
                foreach ($node->runtimeNodes as $runtimeNode) {
                    if ($runtimeNode instanceof VariableNode) {
                        if (array_key_exists($runtimeNode->name, $node->interpolationRegions)) {
                            $runtimeNode->isInterpolationReference = true;
                            $runtimeNode->interpolationNodes = $node->processedInterpolationRegions[$runtimeNode->name];
                        }
                    }
                }
            }
            if ($node instanceof AntlersNode && $node->hasParameters && ! empty($node->interpolationRegions)) {
                foreach ($node->parameters as $parameter) {
                    foreach ($node->interpolationRegions as $interpolationVariable => $interVar) {
                        if (Str::contains($parameter->value, $interpolationVariable)) {
                            $parameter->interpolations[] = $interpolationVariable;
                        }
                    }
                }
            }
        }

        if (! empty($this->visitors)) {
            foreach ($this->visitors as $visitor) {
                foreach ($this->renderNodes as $node) {
                    $visitor->visit($node);
                }
            }
        }

        return $this->renderNodes;
    }

    /**
     * Registers a NodeVisitorContract instance.
     *
     * @param  NodeVisitorContract  $visitor  The visitor.
     */
    public function addVisitor(NodeVisitorContract $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Clears all registered NodeVisitorContract instances.
     */
    public function clearVisitors()
    {
        $this->visitors = [];
    }

    private function scanToEndOfPhpRegion($checkChar)
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->cur == $checkChar && $this->next != null && $this->next == self::RightBrace) {
                $peek = $this->peek($this->currentIndex + 2);

                if ($peek == self::RightBrace) {
                    $node = $this->makeAntlersPhpNode($this->currentIndex, $checkChar == self::Punctuation_Dollar);

                    $this->currentContent = [];

                    // Advance over the  $}} or ?}}.
                    $this->currentIndex += 3;

                    // Indicate our next "start".
                    $this->startIndex = $this->currentIndex;
                    $this->nodes[] = $node;

                    $this->lastAntlersNode = $node;

                    break;
                }
            }

            $this->currentContent[] = $this->cur;

            if ($this->next == null) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_INCOMPLETE_PHP_EVALUATION_REGION,
                    ParserFailNode::makeWithStartPosition($this->positionFromOffset($this->startIndex, $this->startIndex)),
                    'Unexpected end of input while parsing Antlers PHP region.'
                );
            }
        }
    }

    private function scanToEndOfAntlersCommentRegion()
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->cur == self::Punctuation_Octothorp && $this->next != null && $this->next == self::RightBrace) {
                $peek = $this->peek($this->currentIndex + 2);

                if ($peek == self::RightBrace) {
                    $node = $this->makeAntlersTagNode($this->currentIndex, true);
                    $this->currentContent = [];

                    // Advance over the  #}}.
                    $this->currentIndex += 3;

                    // Indicate our next "start".
                    $this->startIndex = $this->currentIndex;

                    $this->nodes[] = $node;

                    $this->lastAntlersNode = $node;

                    break;
                }
            }

            $this->currentContent[] = $this->cur;

            if ($this->next == null) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_INCOMPLETE_ANTELRS_COMMENT_REGION,
                    ParserFailNode::makeWithStartPosition($this->positionFromOffset($this->startIndex, $this->startIndex)),
                    'Unexpected end of input while parsing Antlers comment region.'
                );
            }
        }
    }

    private function scanToEndOfInterpolatedRegion()
    {
        $subContent = [];

        // We will enter this method when the parser hits the first {.
        $braceCount = 0;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->cur == self::LeftBrace) {
                if ($this->prev == self::AtChar) {
                    $subContent[] = $this->cur;

                    continue;
                }

                $braceCount += 1;
                $subContent[] = $this->cur;
            } elseif ($this->cur == self::RightBrace) {
                if ($this->prev == self::AtChar) {
                    $subContent[] = $this->cur;

                    continue;
                }

                $braceCount -= 1;
                $subContent[] = $this->cur;

                if ($braceCount == 0) {
                    $this->interpolationEndOffsets[$this->currentIndex] = 1;
                    break;
                }
            } else {
                $subContent[] = $this->cur;
            }
        }

        $content = implode($subContent);
        $varSlug = 'int_'.md5($content);
        $varContent = StringUtilities::substr($varSlug, 0, mb_strlen($content));

        // Rotates through more internal variable names when there are collisions.
        // All interpolations are rewritten to normal variables behind the scenes.
        if (array_key_exists($varContent, $this->interpolationRegions) && $content != $this->interpolationRegions[$varContent]) {
            if (! array_key_exists($content, $this->interpolatedCollisions)) {
                if (! array_key_exists($varContent, $this->interpolatedCollisionCount)) {
                    $this->interpolatedCollisionCount[$varContent] = 0;
                }

                if ($varContent == 'int') {
                    if (! array_key_exists($content, $this->threeCharCollisions)) {
                        $this->threeCharCollisionCount += 1;
                        $this->threeCharCollisions[$content] = $this->threeCharCollisionCount;
                    }
                    $varContent = 'i'.$this->threeCharCollisionCount;
                } else {
                    $this->interpolatedCollisionCount[$varContent] += 1;
                    $varContent = str_replace('_', $this->interpolatedCollisionCount[$varContent], $varContent);
                }

                $this->interpolatedCollisions[$content] = $varContent;
            }

            $varContent = $this->interpolatedCollisions[$content];
        }

        // Forcefully rotate the initial int_ to int0 to reduce the chance of string processing collisions.
        if ($varContent == 'int_') {
            $varContent = 'int0';
            $this->interpolationRegions['int_'] = -1;
        }

        $newLen = mb_strlen($varContent);
        $origLen = mb_strlen($content);

        if ($newLen < $origLen) {
            $padLen = $origLen - $newLen;
            $varContent .= str_repeat('x', $padLen);
        }

        $parseContent = str_replace(DocumentParser::LeftBrace, '~', mb_substr($this->content, 0, $this->currentChunkOffset - mb_strlen($content))).'{'.$content.'}';

        return [
            $content,
            $varContent,
            $varContent,
            $parseContent,
        ];
    }

    public function bordersInterpolationRegion(Position $position)
    {
        if (empty($this->interpolationEndOffsets)) {
            return false;
        }

        $offsetCheck = $position->offset - 1;

        if ($offsetCheck <= 0) {
            return false;
        }

        return array_key_exists($offsetCheck, $this->interpolationEndOffsets);
    }

    public static function getPipeEscape()
    {
        return '__antlers:pipe'.GlobalRuntimeState::$environmentId;
    }

    public static function getLeftBraceEscape()
    {
        return '__antlers:leftBrace'.GlobalRuntimeState::$environmentId;
    }

    public static function getRightBraceEscape()
    {
        return '__antlers:rightBrace'.GlobalRuntimeState::$environmentId;
    }

    public static function getPipeEscapeArray()
    {
        return str_split(self::getPipeEscape());
    }

    public static function applyEscapeSequences($string)
    {
        $string = str_replace(DocumentParser::getRightBraceEscape(), DocumentParser::RightBrace, $string);
        $string = str_replace(DocumentParser::getLeftBraceEscape(), DocumentParser::LeftBrace, $string);

        return $string;
    }

    private function getLeftBrace()
    {
        return str_split(self::getLeftBraceEscape());
    }

    private function getRightBrace()
    {
        return str_split(self::getRightBraceEscape());
    }

    private function resetEscapedContentState()
    {
        $this->mayBeStartingEscapedContent = false;
        $this->isParsingEscapedContent = false;
        $this->escapedContentEndSymbol = null;
        $this->escapedContentSymbolEncountered = 0;
    }

    private function scanToEndOfAntlersRegion()
    {
        $this->resetEscapedContentState();

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->cur == self::LeftBrace && $this->prev == self::AtChar) {
                array_pop($this->currentContent);
                $this->currentContent = array_merge($this->currentContent, $this->getLeftBrace());

                continue;
            }

            if ($this->isInterpolatedParser && $this->cur == self::RightBrace && $this->prev == self::AtChar) {
                array_pop($this->currentContent);
                $this->currentContent[] = $this->cur;

                continue;
            }

            if ($this->cur == self::RightBrace && $this->prev == self::AtChar) {
                array_pop($this->currentContent);
                $this->currentContent = array_merge($this->currentContent, $this->getRightBrace());

                continue;
            }

            if ($this->isParsingEscapedContent && $this->cur == $this->escapedContentEndSymbol && $this->prev != self::String_EscapeCharacter) {
                $this->escapedContentSymbolEncountered++;

                if ($this->escapedContentSymbolEncountered >= 2) {
                    $this->resetEscapedContentState();
                }
            }

            if ($this->mayBeStartingEscapedContent) {
                if ($this->cur != null && ctype_space($this->cur) || $this->next == null) {
                    $this->resetEscapedContentState();
                } else {
                    if ($this->cur == self::Punctuation_Equals && ($this->next == self::String_Terminator_SingleQuote || $this->next == self::String_Terminator_DoubleQuote)) {
                        $this->mayBeStartingEscapedContent = false;
                        $this->isParsingEscapedContent = true;
                        $this->escapedContentEndSymbol = $this->next;

                        // We'll use this counter to track the number of
                        // times we've seen the end symbol. We will do
                        // it this way to avoid modifying the logic
                        // below, which is already a bit complex.
                        $this->escapedContentSymbolEncountered = 0;
                    }
                }
            }

            if ($this->cur == self::String_EscapeCharacter && ($this->prev != null && ctype_space($this->prev))) {
                if ($this->next != null && (ctype_alpha($this->next) || $this->next == self::Punctuation_Underscore || $this->next == self::AtChar)) {
                    // It is possible that we might be starting some escaped content.
                    // We will need more information to determine this, but let's
                    // flag that it is currently a possibility and handle it.
                    $this->mayBeStartingEscapedContent = true;
                }
            }

            if (! $this->isParsingEscapedContent && $this->cur == self::LeftBrace) {
                $results = $this->scanToEndOfInterpolatedRegion();
                GlobalRuntimeState::$interpolatedVariables[] = $results[2];

                $this->currentContent = array_merge($this->currentContent, StringUtilities::split($results[2]));
                $this->interpolationRegions[$results[1]] = $results[3];

                continue;
            }

            if (! $this->isParsingEscapedContent && $this->cur == self::RightBrace && $this->next != null && $this->next == self::RightBrace) {
                $node = $this->makeAntlersTagNode($this->currentIndex, false);

                if ($node->name != null && $node->name->name == 'noparse') {
                    $this->currentIndex += 2;
                    $this->nodes[] = $node;

                    $this->lastAntlersNode = $node;

                    if (! $node->isClosingTag) {
                        // Skips everything in the template until it finds the next {{ /noparse }} closing tag.
                        foreach ($this->antlersStartIndex as $sIndex => $start) {
                            if ($start > $node->endPosition->index) {
                                $fetchContent = $this->fetchAt($start, 11);
                                $fetchContent = strtolower(str_replace(' ', '', $fetchContent));

                                if (Str::startsWith($fetchContent, '{{/noparse')) {
                                    $this->jumpToIndex = $sIndex;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $this->currentIndex += 2;
                    $this->nodes[] = $node;

                    $this->lastAntlersNode = $node;
                }

                break;
            }

            $this->currentContent[] = $this->cur;

            if ($this->next == null) {
                $failPosition = $this->startIndex + $this->seedOffset;
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_INCOMPLETE_ANTLERS_REGION,
                    ParserFailNode::makeWithStartPosition($this->positionFromOffset($failPosition, $failPosition)),
                    'Unexpected end of input while parsing Antlers region.'
                );
            }
        }
    }

    private function makeAntlersPhpNode($index, $isEcho)
    {
        $node = new PhpExecutionNode();

        $node->isVirtual = $this->isVirtual;
        $node->isEchoNode = $isEcho;

        if ($isEcho) {
            $node->rawStart = '{{$';
            $node->rawEnd = '$}}';
        } else {
            $node->rawStart = '{{?';
            $node->rawEnd = '?}}';
        }

        $node->content = implode('', $this->currentContent); // Add back the final PHP closing tag.
        $node->startPosition = $this->positionFromOffset(
            $this->startIndex + $this->seedOffset,
            $this->startIndex + $this->seedOffset
        );

        if ($index + 3 > $this->inputLen) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_MANIFESTING_ANTLERS_NODE,
                $node,
                'Unexpected end of input while locating end of Antlers region.'
            );
        }

        $this->lastAntlersEndIndex = $index + 3 + $this->seedOffset;

        $node->endPosition = $this->positionFromOffset(
            $index + $this->seedOffset,
            $index + 3 + $this->seedOffset
        );

        $this->interpolationRegions = [];

        return $node;
    }

    private function makeAntlersTagNode($index, $isComment)
    {
        $node = new AntlersNode();

        $node->isVirtual = $this->isVirtual;

        if ($this->isDoubleBrace) {
            $node->rawStart = '{{';
            $node->rawEnd = '}}';
        } else {
            $node->rawStart = '{';
            $node->rawEnd = '}';
        }

        $isSelfClosing = false;

        $contentLen = count($this->currentContent);

        if ($contentLen > 0 && $this->currentContent[$contentLen - 1] == self::Punctuation_ForwardSlash) {
            array_pop($this->currentContent);
            $isSelfClosing = true;
        }

        $node->isComment = $isComment;
        $node->isSelfClosing = $isSelfClosing;
        $node->withParser($this);
        $node->content = implode('', $this->currentContent);
        $node->isInterpolationNode = $this->isInterpolatedParser;

        $node->startPosition = $this->positionFromOffset(
            $this->startIndex + $this->seedOffset,
            $this->startIndex + $this->seedOffset
        );

        if ($index + 2 > $this->inputLen) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_MANIFESTING_ANTLERS_NODE,
                $node,
                'Unexpected end of input while locating end of Antlers region.'
            );
        }

        if ($isComment) {
            $this->lastAntlersEndIndex = $index + 2 + $this->seedOffset;
        } else {
            $this->lastAntlersEndIndex = $index + 1 + $this->seedOffset;
        }

        $node->endPosition = $this->positionFromOffset(
            $this->lastAntlersEndIndex,
            $this->lastAntlersEndIndex
        );

        $node->interpolationRegions = $this->interpolationRegions;

        if (! $node->isComment) {
            $node = $this->nodeParser->parseNode($node);
        }

        $this->interpolationRegions = [];

        return $node;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    private function dumpLiteralNode($index)
    {
        if (! empty($this->currentContent)) {
            $this->nodes[] = $this->makeLiteralNode($this->currentContent, $this->startIndex, $index);
        }

        $this->currentContent = [];
    }

    private function makeLiteralNode($buffer, $startOffset, $currentOffset)
    {
        $node = new LiteralNode();
        $node->isVirtual = $this->isVirtual;
        $node->content = implode('', $buffer);
        $node->startPosition = $this->positionFromOffset($startOffset, $startOffset);
        $node->endPosition = $this->positionFromOffset($currentOffset, $startOffset);

        return $node;
    }

    /**
     * @param  false  $isRelativeOffset
     * @return Position
     */
    public function positionFromOffset($offset, $index, $isRelativeOffset = false)
    {
        $lineToUse = 0;
        $charToUse = 0;

        if (! array_key_exists($offset, $this->documentOffsets)) {
            if (empty($this->documentOffsets)) {
                $lineToUse = 1;
                $charToUse = $offset + 1;
            } else {
                $nearestOffset = null;
                $nearestOffsetIndex = null;
                foreach ($this->documentOffsets as $documentOffset => $details) {
                    if ($documentOffset >= $offset) {
                        $nearestOffset = $details;
                        $nearestOffsetIndex = $documentOffset;
                        break;
                    }
                }

                if ($nearestOffset != null) {
                    if ($isRelativeOffset) {
                        $charToUse = $offset - $nearestOffset[self::K_CHAR];
                        $lineToUse = $nearestOffset[self::K_LINE];

                        if ($offset <= $nearestOffsetIndex) {
                            $lineToUse = $nearestOffset[self::K_LINE];
                            $charToUse = $offset + 1;
                        } else {
                            $lineToUse = $nearestOffset[self::K_LINE] + 1;
                        }
                    } else {
                        $offsetDelta = $nearestOffset[self::K_CHAR] - $nearestOffsetIndex + $offset;
                        $charToUse = $offsetDelta;
                        $lineToUse = $nearestOffset[self::K_LINE];
                    }
                } else {
                    $lastOffsetKey = array_key_last($this->documentOffsets);
                    $lastOffset = $this->documentOffsets[$lastOffsetKey];
                    $lineToUse = $lastOffset['line'] + 1;
                    $charToUse = $offset - $lastOffsetKey;
                }
            }
        } else {
            $details = $this->documentOffsets[$offset];

            $lineToUse = $details[self::K_LINE];
            $charToUse = $details[self::K_CHAR];
        }

        $position = new Position();

        $position->index = $index;
        $position->offset = $offset;
        $position->line = $lineToUse;
        $position->char = $charToUse;

        return $position;
    }

    private function checkCurrentOffsets()
    {
        if (array_key_exists($this->currentIndex, $this->chars) == false) {
            $this->cur = null;
            $this->prev = null;
            $this->next = null;

            return;
        }

        $this->cur = $this->chars[$this->currentIndex];

        $this->prev = null;
        $this->next = null;

        if ($this->currentIndex > 0) {
            $this->prev = $this->chars[$this->currentIndex - 1];
        }

        if (($this->currentIndex + 1) < $this->inputLen) {
            $doPeek = true;
            if ($this->currentIndex == $this->charLen - 1) {
                $nextChunk = mb_str_split(mb_substr($this->content, $this->currentChunkOffset + $this->chunkSize, $this->chunkSize));
                $this->currentChunkOffset += $this->chunkSize;

                if ($this->currentChunkOffset == $this->inputLen) {
                    $doPeek = false;
                }

                foreach ($nextChunk as $nextChar) {
                    $this->chars[] = $nextChar;
                    $this->charLen += 1;
                }
            }

            if ($doPeek && array_key_exists($this->currentIndex + 1, $this->chars)) {
                $this->next = $this->chars[$this->currentIndex + 1];
            }
        }
    }

    protected function resetIntermediateState()
    {
        $this->chars = [];
        $this->charLen = 0;
        $this->currentIndex = 0;
        $this->currentContent = [];
        $this->cur = null;
        $this->next = null;
        $this->prev = null;
    }

    public function resetState()
    {
        $this->charLen = 0;
        $this->antlersStartIndex = [];
        $this->antlersStartPositionIndex = [];
        $this->lastAntlersEndIndex = -1;

        $this->renderNodes = [];
        $this->nodes = [];

        if (! empty(GlobalRuntimeState::$globalTagEnterStack)) {
            /** @var AntlersNode $lastTagNode */
            $lastTagNode = GlobalRuntimeState::$globalTagEnterStack[count(GlobalRuntimeState::$globalTagEnterStack) - 1];

            if ($lastTagNode->name->name != 'partial') {
                $this->setStartLineSeed($lastTagNode->endPosition->line);
            }
        }

        $this->seedOffset = 0;

        $this->content = '';
        $this->chars = [];
        $this->currentIndex = 0;
        $this->currentIndex = [];
        $this->startIndex = 0;
        $this->cur = null;
        $this->next = null;
        $this->prev = null;
        $this->inputLen = 0;
        $this->documentOffsets = [];
        $this->nodes = [];
        $this->isDoubleBrace = false;
        $this->interpolationRegions = [];
        $this->interpolationEndOffsets = [];
    }
}
