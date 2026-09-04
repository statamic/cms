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
use Statamic\View\Antlers\Language\Nodes\DirectiveNode;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\ParserFailNode;
use Statamic\View\Antlers\Language\Nodes\Position;
use Statamic\View\Antlers\Language\Nodes\Structures\PhpExecutionNode;
use Statamic\View\Antlers\Language\Nodes\TagIdentifier;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract;
use Statamic\View\Antlers\Language\Utilities\CharacterOffsets;
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
     * Character length of each neutralized document prefix handed to an interpolation
     * sub-parser, keyed by the prefix's byte length, so the sub-parse loop can recover
     * it from the region text without recounting the prefix.
     *
     * @var array<int, int>
     */
    private $prefixCharacterLengths = [];

    /**
     * The document content with every "{" replaced by "~", built on first use and
     * released once the interpolation regions have been parsed. The replacement is
     * byte-for-byte, which is what lets a prefix of it share the document's offsets.
     *
     * @var string|null
     */
    private $neutralizedContent = null;

    /**
     * Added to every line number read from the newline table. Non-zero only in an
     * interpolation sub-parser whose inherited table was numbered from a different
     * seed line than its own.
     *
     * @var int
     */
    private $lineShift = 0;

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
    /**
     * The newline table: character offset of each "\n" => [K_CHAR => column, K_LINE => line],
     * and the same offsets as a sorted list for binary searching. An interpolation
     * sub-parser shares its parent's arrays and only the first $newlineCount entries
     * belong to its own document, so every reader goes through that bound.
     */
    private $documentOffsets = [];
    private $documentOffsetKeys = [];
    private $newlineCount = 0;
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
    private $nextChunkOffset = 0;

    /**
     * Character offset => byte offset for every offset the scanner has touched.
     * Only maintained for multibyte content; otherwise the two are equal.
     *
     * @var array<int, int>
     */
    private $sourceByteOffsets = [];
    private $isMultibyteContent = false;
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
        return $this->sourceSubstr($start, $end - $start);
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
     * Extracts characters from the content by character offset, with mb_substr()
     * semantics: a negative start counts from the end, a negative length omits that
     * many characters from the end, and an empty range yields an empty string.
     *
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    private function sourceSubstr($start, $length = null)
    {
        if (! $this->isMultibyteContent) {
            return substr($this->content, $start, $length);
        }

        if ($start < 0) {
            $start = max(0, $this->inputLen + $start);
        }

        if ($length === null) {
            $end = $this->inputLen;
        } elseif ($length < 0) {
            $end = $this->inputLen + $length;
        } else {
            $end = min($start + $length, $this->inputLen);
        }

        if ($end <= $start) {
            return '';
        }

        $byteStart = $this->sourceByteOffset($start);

        return substr($this->content, $byteStart, $this->sourceByteOffset($end) - $byteStart);
    }

    /**
     * Returns the byte offset of a character offset, resolving and memoizing it
     * when the scanner has not touched that offset yet.
     *
     * @param  int  $character
     * @return int
     */
    private function sourceByteOffset($character)
    {
        if (! $this->isMultibyteContent) {
            return min(max($character, 0), $this->inputLen);
        }

        if (! isset($this->sourceByteOffsets[$character])) {
            $this->sourceByteOffsets += CharacterOffsets::toBytes($this->content, [$character], true);
        }

        return $this->sourceByteOffsets[$character];
    }

    /** @return array<int, string> */
    private function sourceChunk($characterOffset)
    {
        $byteOffset = $this->sourceByteOffset($characterOffset);

        if ($this->isMultibyteContent) {
            $chunk = mb_strcut($this->content, $byteOffset, $this->chunkSize * 4, 'UTF-8');
            $characters = array_slice(mb_str_split($chunk), 0, $this->chunkSize);

            foreach ($characters as $index => $character) {
                $this->sourceByteOffsets[$characterOffset + $index] = $byteOffset;
                $byteOffset += strlen($character);
            }

            $this->sourceByteOffsets[$characterOffset + count($characters)] = $byteOffset;
        } else {
            $characters = str_split(substr($this->content, $byteOffset, $this->chunkSize));
        }

        $this->nextChunkOffset = $characterOffset + count($characters);

        return $characters;
    }

    private function appendSourceChunk()
    {
        $this->currentChunkOffset = $this->nextChunkOffset;
        $nextChunk = $this->sourceChunk($this->currentChunkOffset);

        foreach ($nextChunk as $nextChar) {
            $this->chars[] = $nextChar;
            $this->charLen += 1;
        }

        return $nextChunk !== [];
    }

    public function getParsedContent()
    {
        return $this->content;
    }

    private function peek($count)
    {
        if ($count == $this->charLen) {
            $this->appendSourceChunk();
        }

        return $this->chars[$count];
    }

    public function parseIntermediateText()
    {
        $this->currentContent = [];
        $this->startIndex = 0;

        $this->chars = $this->sourceChunk($this->currentChunkOffset);
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

            if ($this->cur == self::AtChar && $this->next != null && ctype_alpha($this->next)) {
                $this->scanToEndOfDirective();
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

    /**
     * @param  string  $input
     * @param  array|null  $prefix  Set when parsing an interpolation region for a parent parser; see interpolatedRegionPrefix().
     * @return bool
     */
    private function processInputText($input, ?array $prefix)
    {
        if ($prefix === null) {
            $this->content = StringUtilities::normalizeLineEndings($input);
            $this->inputLen = mb_strlen($this->content);
        } else {
            // Interpolation sub-parse: the input is the parent's already-normalized
            // document prefix (braces neutralized) followed by the interpolated tail.
            // The prefix's newline table is inherited from the parent below, so only
            // the tail is scanned.
            $this->content = $input;
            $this->inputLen = $prefix['characters'] + $prefix['tailCharacters'];
        }

        $this->isMultibyteContent = $this->inputLen !== strlen($this->content);
        $this->sourceByteOffsets = [
            0 => 0,
            $this->inputLen => strlen($this->content),
        ];

        $currentLine = $this->seedStartLine;
        $newlineCount = 0;
        $lastOffset = -1; // A virtual newline just before the document.
        $scanFromByte = 0;
        $scanFromCharacter = 0;

        if ($prefix !== null) {
            $this->sourceByteOffsets[$prefix['characters']] = $prefix['bytes'];

            // The parent's table is shared as-is (only the first $newlineCount entries
            // fall inside the prefix) and stays numbered from the parent's seed line;
            // the difference from this parser's seed is applied on read.
            $this->documentOffsets = $prefix['newlineOffsets'];
            $this->documentOffsetKeys = $prefix['newlineKeys'];
            $newlineCount = $prefix['newlineCount'];
            $this->lineShift = $this->seedStartLine - $prefix['startLine'];
            $currentLine = $prefix['startLine'] + $newlineCount;
            $lastOffset = $newlineCount > 0 ? $this->documentOffsetKeys[$newlineCount - 1] : -1;
            $scanFromByte = $prefix['bytes'];
            $scanFromCharacter = $prefix['characters'];
        }

        // The document content was normalized, so we can search for "\n".
        preg_match_all('/\n/', $this->content, $documentNewLines, PREG_OFFSET_CAPTURE, $scanFromByte);
        $newLineByteOffsets = array_column($documentNewLines[0], 1);
        $newLineCharacterOffsets = CharacterOffsets::toCharacters(
            $this->content,
            $newLineByteOffsets,
            $this->isMultibyteContent,
            $scanFromByte,
            $scanFromCharacter
        );

        if ($prefix !== null && $newLineByteOffsets !== []) {
            // The tail adds entries, so this parser needs its own copy cut at the prefix.
            $this->documentOffsets = array_slice($this->documentOffsets, 0, $newlineCount, true);
            $this->documentOffsetKeys = array_slice($this->documentOffsetKeys, 0, $newlineCount);
        }

        foreach ($newLineByteOffsets as $newLineByteOffset) {
            $thisIndex = $newLineCharacterOffsets[$newLineByteOffset];

            $this->documentOffsets[$thisIndex] = [
                self::K_CHAR => $thisIndex - $lastOffset,
                self::K_LINE => $currentLine,
            ];
            $this->documentOffsetKeys[] = $thisIndex;

            $newlineCount += 1;
            $currentLine += 1;
            $lastOffset = $thisIndex;
        }

        $this->newlineCount = $newlineCount;

        // An inherited prefix contains no "{" and any directive in it would be discarded
        // by the sub-parse loop, so only the tail is scanned for candidates. The byte
        // before the tail is included so that an "@" ending the prefix still escapes
        // the tail's braces exactly as a scan of the whole text would; that loses the
        // interpolation (a pre-existing quirk kept for compatibility, see
        // InterpolationRegionTest::test_an_at_sign_just_before_an_interpolation_escapes_it).
        $candidateScanFromByte = $scanFromByte;

        if ($candidateScanFromByte > 0 && $this->content[$candidateScanFromByte - 1] === self::AtChar) {
            $candidateScanFromByte--;
        }

        preg_match_all('/(@?{{|@(props|aware|cascade))/u', $this->content, $antlersStartCandidates, PREG_OFFSET_CAPTURE, $candidateScanFromByte);
        $candidateCharacterOffsets = CharacterOffsets::toCharacters(
            $this->content,
            array_column($antlersStartCandidates[0], 1),
            $this->isMultibyteContent,
            $scanFromByte,
            $scanFromCharacter
        );

        $lastAntlersByteOffset = 0;
        $lastWasEscaped = false;
        foreach ($antlersStartCandidates[0] as $antlersMatch) {
            $antlersRegion = $antlersMatch[0];
            $matchByteOffset = $antlersMatch[1];
            $offset = $candidateCharacterOffsets[$matchByteOffset];

            if ($this->isMultibyteContent) {
                $this->sourceByteOffsets[$offset] = $matchByteOffset;
            }

            if (Str::startsWith($antlersRegion, '@')) {
                if (in_array($antlersRegion, ['@props', '@aware', '@cascade'])) {
                    if ($matchByteOffset > 0) {
                        if ($this->content[$matchByteOffset - 1] === self::AtChar) {
                            $lastAntlersByteOffset = $matchByteOffset;
                            $lastWasEscaped = true;

                            continue;
                        }
                    }

                    if (in_array($antlersRegion, ['@props', '@aware'])) {
                        $hasArgs = false;

                        for ($k = $matchByteOffset + strlen($antlersRegion), $byteLen = strlen($this->content); $k < $byteLen; $k++) {
                            $ch = $this->content[$k];

                            if (ctype_space($ch)) {
                                continue;
                            }

                            if ($ch === '(') {
                                $hasArgs = true;
                            }

                            break;
                        }

                        if (! $hasArgs) {
                            $lastAntlersByteOffset = $matchByteOffset + strlen($antlersRegion);
                            $lastWasEscaped = false;

                            continue;
                        }
                    }

                    $this->antlersStartIndex[] = $offset;
                    $this->antlersStartPositionIndex[$offset] = 1;

                    $lastWasEscaped = false;
                    $lastAntlersByteOffset = $matchByteOffset + strlen($antlersRegion);

                    continue;
                }

                $lastAntlersByteOffset = $matchByteOffset + 2;
                $lastWasEscaped = true;

                continue;
            }

            // A "{{" sitting exactly where the last escaped region ended overlaps it
            // (as in "@{{{"), and every "{{" after it is skipped until the next escape.
            if ($lastWasEscaped && substr($this->content, $lastAntlersByteOffset, 2) === '{{') {
                continue;
            }

            $this->antlersStartIndex[] = $offset;
            $this->antlersStartPositionIndex[$offset] = 1;
            $lastAntlersByteOffset = $matchByteOffset + 2;
            $lastWasEscaped = false;
        }

        return true;
    }

    /**
     * Returns the document content with every left brace replaced by "~". This is
     * the prefix handed to interpolation sub-parsers so their positions line up
     * with the document without the prefix producing Antlers nodes.
     *
     * @return string
     */
    private function neutralizedContent()
    {
        if ($this->neutralizedContent === null) {
            $this->neutralizedContent = str_replace(self::LeftBrace, self::Punctuation_Tilde, $this->content);
        }

        return $this->neutralizedContent;
    }

    /**
     * Describes the document prefix inside an interpolation region's text so that
     * the sub-parser can inherit this parser's newline table for it instead of
     * rescanning it: the prefix's lengths, and the table entries that fall inside it.
     *
     * @param  string  $regionText
     * @return array|null
     */
    private function interpolatedRegionPrefix($regionText)
    {
        // The prefix is brace-neutralized, so the first "{" is where the tail starts.
        $prefixBytes = strpos($regionText, self::LeftBrace);

        // Regions this parser did not build (a DirectiveNode carries the regions of
        // the parser that handled its arguments) belong to another document and are
        // parsed from scratch, as are any this parser has no record of.
        if ($prefixBytes === false
            || ! isset($this->prefixCharacterLengths[$prefixBytes])
            || strncmp($regionText, $this->neutralizedContent(), $prefixBytes) !== 0) {
            return null;
        }

        $prefixCharacters = $this->isMultibyteContent ? $this->prefixCharacterLengths[$prefixBytes] : $prefixBytes;

        return [
            'characters' => $prefixCharacters,
            'bytes' => $prefixBytes,
            // The tail is short; counting it directly keeps a region whose prefix is
            // empty independent of this parser's own encoding flag.
            'tailCharacters' => mb_strlen(substr($regionText, $prefixBytes)),
            // The line the table is numbered from, which may itself be inherited.
            'startLine' => $this->seedStartLine - $this->lineShift,
            'newlineCount' => $this->newlineCountBefore($prefixCharacters),
            'newlineKeys' => $this->documentOffsetKeys,
            'newlineOffsets' => $this->documentOffsets,
        ];
    }

    /**
     * Counts the newline offsets of this document that fall before $offset.
     *
     * @param  int  $offset
     * @return int
     */
    private function newlineCountBefore($offset)
    {
        $low = 0;
        $high = $this->newlineCount;

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($this->documentOffsetKeys[$middle] < $offset) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $low;
    }

    /**
     * Performs literal escape logic on the input string.
     *
     * @param  string  $content  The input content.
     * @return string
     */
    private function prepareLiteralContent($content)
    {
        return strtr($content, [
            '@{{' => '{{',
            '@@props' => '@props',
            '@@aware' => '@aware',
            '@@cascade' => '@cascade',
        ]);
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
        return $this->parseDocument($text, null);
    }

    /**
     * @param  string  $text
     * @param  array|null  $interpolationPrefix  Set when $text is an interpolation region of a parent parser: its neutralized document prefix followed by the interpolated tail. See interpolatedRegionPrefix().
     * @return array
     */
    private function parseDocument($text, ?array $interpolationPrefix)
    {
        $this->resetState();

        if (! $this->processInputText($text, $interpolationPrefix)) {
            return [];
        }

        StringUtilities::$splitMethod = $this->isMultibyteContent
            ? StringUtilities::SPLIT_METHOD_MB_STR_SPLIT
            : StringUtilities::SPLIT_METHOD_STR_SPLIT;

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

                if ($i == 0 && $offset > 0 && $interpolationPrefix === null) {
                    // Create a literal node representing the start of the document. In an
                    // interpolation sub-parse it would be the neutralized prefix, which the
                    // parent discards, so it is not built at all.
                    $node = new LiteralNode();
                    $node->isVirtual = $this->isVirtual;
                    $node->content = $this->prepareLiteralContent($this->sourceSubstr(0, $offset));

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
                                $content = $this->prepareLiteralContent($this->sourceSubstr($this->lastAntlersNode->endPosition->offset + 1));

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

                                    $content = $this->sourceSubstr($spanStart, $spanLen);

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
                                    $finalContent = $this->prepareLiteralContent($this->sourceSubstr($literalStart));

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
                                $content = $this->sourceSubstr($literalStartIndex);

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
                        $content = $this->sourceSubstr($literalStartIndex);

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

                        $content = $this->sourceSubstr($literalStartIndex, $literalLength);

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
                        $node->content = $this->prepareLiteralContent($this->sourceSubstr($literalStart));

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

                    $parseResults = $docParser->parseDocument($content, $this->interpolatedRegionPrefix($content));

                    // The interpolated content is parsed with the document prefix in front of it so
                    // positions line up. The interpolation itself is the final Antlers or PHP node;
                    // a region that could not be prefixed (parsed from scratch) may also yield the
                    // prefix's literal and directive nodes, which are skipped.
                    $interpolationNode = null;

                    foreach ($parseResults as $parseResult) {
                        if ($parseResult instanceof DirectiveNode) {
                            continue;
                        }

                        if ($parseResult instanceof AntlersNode || $parseResult instanceof PhpExecutionNode) {
                            $interpolationNode = $parseResult;
                        }
                    }

                    $parseResults = $interpolationNode === null ? [] : [$interpolationNode];

                    $node->processedInterpolationRegions[$varName] = $parseResults;
                }
                $node->hasProcessedInterpolationRegions = true;
            }
        }

        // Only the interpolation sub-parses need these; the nodes keep their own copies.
        $this->neutralizedContent = null;
        $this->prefixCharacterLengths = [];

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

    private function scanToEndOfDirective()
    {
        $name = '';

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            $name .= $this->cur;

            if ($this->next == null || ! ctype_alpha($this->next)) {
                $this->currentIndex++;
                break;
            }
        }

        $args = '';
        $parseArgs = true;

        if ($this->next === null || ctype_space($this->next) || ctype_space($this->cur)) {
            $rollbackTo = $this->currentIndex;
            $this->advanceWhitespace();

            if (ctype_space($this->cur) && $this->next === '(') {
                $this->currentIndex++;
                $this->checkCurrentOffsets();
            }

            if ($this->cur != '(') {
                $parseArgs = false;
                $this->currentIndex = $rollbackTo;
                $this->checkCurrentOffsets();
            }
        }

        if ($parseArgs) {
            $argsStarted = $this->currentIndex;

            $this->currentIndex += 1;

            $args = '(';
            $inString = false;
            $stringTerminator = null;
            $parenCount = 1;

            for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
                $this->checkCurrentOffsets();

                if (! $inString && ($this->cur === self::String_Terminator_SingleQuote || $this->cur === self::String_Terminator_DoubleQuote)) {
                    $inString = true;
                    $stringTerminator = $this->cur;
                    $args .= $this->cur;

                    continue;
                }

                if ($inString) {
                    $args .= $this->cur;

                    if ($this->cur === $stringTerminator && $this->prev !== self::String_EscapeCharacter) {
                        $inString = false;
                    }

                    continue;
                }

                if ($this->cur === self::LeftParen) {
                    $parenCount++;
                    $args .= $this->cur;

                    continue;
                } elseif ($this->cur === self::RightParent) {
                    $parenCount--;
                    $args .= $this->cur;

                    if ($parenCount === 0) {
                        break;
                    }

                    continue;
                }

                $args .= $this->cur;
            }

            if ($parenCount != 0) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_DIRECTIVE_MISSING_ARGUMENTS,
                    ParserFailNode::makeWithStartPosition($this->positionFromOffset($argsStarted, $argsStarted)),
                    "Incomplete arguments for {$name} directive",
                );
            }
        }

        $node = $this->makeDirectiveNode($name, $args, $this->currentIndex);
        $this->currentContent = [];
        $this->nodes[] = $node;
        $this->lastAntlersNode = $node;
        $this->startIndex = $this->currentIndex;
        $this->currentIndex += 1;
    }

    private function advanceWhitespace()
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->next == null || ! ctype_space($this->next)) {
                break;
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

        // The sub-parser is handed the document prefix (braces neutralized) followed by
        // the interpolated content so that its node positions line up with the document.
        // The cut is chunk-aligned and can fall before the document start, in which case
        // it counts from the end like mb_substr() would.
        $prefixLength = $this->currentChunkOffset - $origLen;

        if ($prefixLength < 0) {
            $prefixLength = max(0, $this->inputLen + $prefixLength);
        }

        $prefixBytes = $this->sourceByteOffset($prefixLength);
        $this->prefixCharacterLengths[$prefixBytes] = $prefixLength;
        $parseContent = substr($this->neutralizedContent(), 0, $prefixBytes).'{'.$content.'}';

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
                                $fetchContent = $this->sourceSubstr($start, 11);
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

    private function makeDirectiveNode($name, $args, $index)
    {
        $directive = new DirectiveNode();
        $directive->withParser($this);
        $directive->directiveName = $name;
        $directive->args = $args;

        $this->lastAntlersEndIndex = $index + $this->seedOffset;
        $directive->startPosition = $this->positionFromOffset(
            $this->startIndex + $this->seedOffset,
            $this->startIndex + $this->seedOffset
        );
        $directive->endPosition = $this->positionFromOffset(
            $index + $this->seedOffset,
            $index + $this->seedOffset
        );

        $directive->content = trim($args, '() ');

        $this->nodeParser->parseNode($directive);

        return $directive;
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

        if ($this->newlineCount === 0) {
            $lineToUse = 1;
            $charToUse = $offset + 1;
        } else {
            $nearestIndex = $this->newlineCountBefore($offset);

            if ($nearestIndex === $this->newlineCount) {
                // Past the last newline.
                $lastOffsetKey = $this->documentOffsetKeys[$nearestIndex - 1];
                $lastOffset = $this->documentOffsets[$lastOffsetKey];
                $lineToUse = $lastOffset[self::K_LINE] + 1 + $this->lineShift;
                $charToUse = $offset - $lastOffsetKey;
            } else {
                $nearestOffsetIndex = $this->documentOffsetKeys[$nearestIndex];
                $nearestOffset = $this->documentOffsets[$nearestOffsetIndex];

                if ($nearestOffsetIndex === $offset) {
                    // Exactly on a newline.
                    $lineToUse = $nearestOffset[self::K_LINE] + $this->lineShift;
                    $charToUse = $nearestOffset[self::K_CHAR];
                } elseif ($isRelativeOffset) {
                    $charToUse = $offset - $nearestOffset[self::K_CHAR];
                    $lineToUse = $nearestOffset[self::K_LINE] + $this->lineShift;

                    if ($offset <= $nearestOffsetIndex) {
                        $charToUse = $offset + 1;
                    } else {
                        $lineToUse += 1;
                    }
                } else {
                    $charToUse = $nearestOffset[self::K_CHAR] - $nearestOffsetIndex + $offset;
                    $lineToUse = $nearestOffset[self::K_LINE] + $this->lineShift;
                }
            }
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
                $doPeek = $this->appendSourceChunk();
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
        $this->nextChunkOffset = 0;
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

            if (! in_array($lastTagNode->name->name, ['partial', 'include'])) {
                $this->setStartLineSeed($lastTagNode->endPosition->line);
            }
        }

        $this->seedOffset = 0;
        $this->nextChunkOffset = 0;
        $this->sourceByteOffsets = [];
        $this->isMultibyteContent = false;
        $this->lineShift = 0;

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
        $this->documentOffsetKeys = [];
        $this->newlineCount = 0;
        $this->nodes = [];
        $this->isDoubleBrace = false;
        $this->interpolationRegions = [];
        $this->prefixCharacterLengths = [];
        $this->neutralizedContent = null;
        $this->interpolationEndOffsets = [];
    }
}
