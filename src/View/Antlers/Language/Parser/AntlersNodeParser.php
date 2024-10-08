<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Analyzers\ConditionPairAnalyzer;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Analyzers\TagIdentifierAnalyzer;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Parameters\ParameterNode;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;
use Statamic\View\Antlers\Language\Nodes\TagIdentifier;
use Statamic\View\Antlers\Language\Runtime\Sandbox\TypeCoercion;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class AntlersNodeParser
{
    private $chars = [];
    private $inputLen = 0;
    private $currentIndex = 0;
    private $cur = null;
    private $prev = null;

    /**
     * @var PathParser|null
     */
    private $pathParser = null;

    /**
     * @var AntlersLexer|null
     */
    private $lexer = null;

    public function __construct()
    {
        $this->pathParser = new PathParser();
        $this->lexer = new AntlersLexer();
    }

    /**
     * Resets the internal parser state.
     */
    protected function reset()
    {
        $this->chars = [];
        $this->inputLen = 0;
        $this->currentIndex = 0;
        $this->cur = null;
        $this->prev = null;
    }

    /**
     * Tests if the node is a valid closing node.
     *
     * @param  AntlersNode  $node  The node to tests.
     * @return bool
     */
    private function canBeClosingTag(AntlersNode $node)
    {
        $name = $node->name->name;

        if ($name == 'elseif' || $name == 'else') {
            return true;
        }

        return Str::startsWith($node->name->content, '/');
    }

    /**
     * Parses an AntlersNode and populates relevant runtime data.
     *
     * This parser is responsible for:
     *  - Node type (tag, literal, conditional structure, etc.)
     *  - Runtime Nodes
     *  - Node rewrites (e.g., unless to if structures)
     *
     * @param  AntlersNode  $node  The node to parse.
     * @return AntlersNode|RecursiveNode
     *
     * @throws AntlersException
     * @throws SyntaxErrorException
     */
    public function parseNode(AntlersNode $node)
    {
        $this->reset();

        if (Str::startsWith(trim($node->content), '*subrecursive')) {
            $nodeContent = $node->content;

            $nodeContent = trim($nodeContent);
            $nodeContent = rtrim($nodeContent, '*');
            $nodeContent = trim(StringUtilities::substr($nodeContent, 13));

            $recursiveNode = new RecursiveNode();
            $node->copyBasicDetailsTo($recursiveNode);
            $recursiveNode->originalNode = $node;
            $recursiveNode->name = new TagIdentifier();
            $recursiveNode->name->name = $nodeContent;
            $recursiveNode->isNestedRecursive = true;

            try {
                $recursiveNode->pathReference = $this->pathParser->parse($nodeContent);
            } catch (AntlersException $antlersException) {
                $antlersException->node = $node;

                throw $antlersException;
            }

            $recursiveNode->content = $nodeContent;
            $recursiveNode->runtimeContent = $nodeContent;

            return $recursiveNode;
        }

        if (Str::startsWith(trim($node->content), '*recursive')) {
            $nodeContent = $node->content;

            $nodeContent = trim($nodeContent);
            $nodeContent = rtrim($nodeContent, '*');
            $nodeContent = trim(StringUtilities::substr($nodeContent, 10));

            $recursiveNode = new RecursiveNode();
            $node->copyBasicDetailsTo($recursiveNode);
            $recursiveNode->originalNode = $node;
            $recursiveNode->name = new TagIdentifier();
            $recursiveNode->name->name = $nodeContent;

            try {
                $recursiveNode->pathReference = $this->pathParser->parse($nodeContent);
            } catch (AntlersException $antlersException) {
                $antlersException->node = $node;

                throw $antlersException;
            }

            $recursiveNode->content = $nodeContent;
            $recursiveNode->runtimeContent = $nodeContent;

            return $recursiveNode;
        }

        StringUtilities::prepareSplit($node->content);
        $this->chars = StringUtilities::split($node->content);
        $this->inputLen = count($this->chars);

        $nameContent = [];
        $hasFoundName = false;
        $name = '';
        $isParsingString = false;
        $terminator = null;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->cur = $this->chars[$this->currentIndex];

            if ($this->currentIndex > 0) {
                $this->prev = $this->chars[$this->currentIndex - 1];
            }

            if ($hasFoundName == false) {
                if ($isParsingString == false && (
                    $this->cur == DocumentParser::String_Terminator_DoubleQuote ||
                    $this->cur == DocumentParser::String_Terminator_SingleQuote)) {
                    $terminator = $this->cur;
                    $isParsingString = true;
                    $nameContent[] = $this->cur;

                    continue;
                }

                if ($isParsingString && ctype_space($this->cur)) {
                    $nameContent[] = $this->cur;

                    continue;
                }

                if ($isParsingString && $this->cur == $terminator && $this->prev != DocumentParser::String_EscapeCharacter) {
                    $terminator = null;
                    $isParsingString = false;
                    $nameContent[] = $this->cur;

                    continue;
                }

                if (ctype_space($this->cur) || ($this->currentIndex == ($this->inputLen - 1)) ||
                    $this->cur == DocumentParser::Punctuation_Pipe) {
                    if (count($nameContent) == 0) {
                        continue;
                    } else {
                        if (! ctype_space($this->cur)) {
                            if ($this->cur != DocumentParser::Punctuation_Pipe) {
                                $nameContent[] = $this->cur;
                            }
                        }

                        $name = implode($nameContent);
                        break;
                    }
                }

                $nameContent[] = $this->cur;
            }
        }

        $node->name = TagIdentifierAnalyzer::getIdentifier($name);
        NodeTypeAnalyzer::analyzeNode($node);

        $node->parameters = $this->getParameters($node);

        $node->hasParameters = ! empty($node->parameters);

        if ($node->hasParameters) {
            NodeTypeAnalyzer::analyzeParametersForModifiers($node);
            $node->resetContentCache();
        }

        $parseName = true;

        // If a node begins with "[", we will skip name resolution.
        // If we don't, this can lead to an infinite loop.
        if (Str::startsWith(trim($node->name->name), DocumentParser::LeftBracket)) {
            $parseName = false;
        }

        try {
            if ($parseName) {
                $node->pathReference = $this->pathParser->parse($name);

                if ($node->pathReference->isStrictTagReference) {
                    // Remove the leading # symbol to not frustrate everyone with "tag not found" errors.
                    $node->name->name = StringUtilities::substr($node->name->name, 1);
                    $node->name->content = StringUtilities::substr($node->name->content, 1);
                }
            }
        } catch (AntlersException $antlersException) {
            $antlersException->node = $node;

            throw $antlersException;
        }

        $node->isClosingTag = $this->canBeClosingTag($node);

        $lexerContent = $node->getContent();

        if ($node->name->name == 'if' || $node->name->name == 'unless' || $node->name->name == 'elseif') {
            if (mb_strlen(trim($lexerContent)) > 0) {
                $lexerContent = '('.$lexerContent.')';
            }
        }

        // Need to run node type analysis here before the runtime node step.
        $runtimeNodes = $this->lexer->tokenize($node, $lexerContent);

        $node->runtimeNodes = $runtimeNodes;

        $trimmedInner = trim($node->content);

        if (ConditionPairAnalyzer::isConditionalStructure($node) && $node->name->name != 'else' && count($runtimeNodes) == 0
            && $trimmedInner != '/if' && $trimmedInner != '/unless' && $trimmedInner != '/endunless'
            && $trimmedInner != 'endif' && $trimmedInner != 'endunless'
            && $trimmedInner != '/endif') {
            $this->throwConditionWithoutExpression($node);
        }

        // Convert the endif tag to its /if counterpart.
        if ($node->name->name == 'endif') {
            $replacedNode = $node->copyBasicDetails();
            $parser = $node->getParser();
            $replacedNode->withParser($parser);

            $replacedNode->name = new TagIdentifier();
            $replacedNode->name->name = 'if';
            $replacedNode->name->compound = 'if';

            $replacedNode->isClosingTag = true;
            $replacedNode->content = ' /if ';
            $replacedNode->originalNode = $node;

            return $replacedNode;
        }

        // Convert unless, elseunless tags into their if/elseif equivalents.
        if ($node->name->name == 'unless') {
            $replacedNode = $node->copyBasicDetails();
            $parser = $node->getParser();
            $replacedNode->withParser($parser);

            $replacedNode->name = new TagIdentifier();
            $replacedNode->name->name = 'if';
            $replacedNode->name->compound = 'if';

            if ($node->isClosingTag == false) {
                $originalContent = $node->getContent();
                $unlessContent = ' if !('.$originalContent.') ';
                $replacedNode->content = $unlessContent;
                $replacedNode->originalNode = $node;
                $replacedNode->resetContentCache();

                $replacedNode->runtimeNodes = $this->lexer->tokenize($replacedNode, $replacedNode->getContent());

                $this->testUnlessContent($replacedNode);
            } else {
                $replacedNode->content = ' /if ';
            }

            return $replacedNode;
        } elseif ($node->name->name == 'elseunless') {
            $replacedNode = $node->copyBasicDetails();
            $parser = $node->getParser();
            $replacedNode->withParser($parser);

            $replacedNode->name = new TagIdentifier();
            $replacedNode->name->name = 'elseif';
            $replacedNode->name->compound = 'elseif';

            $originalContent = $node->getContent();
            $unlessContent = ' elseif !('.$originalContent.') ';
            $replacedNode->content = $unlessContent;
            $replacedNode->originalNode = $node;
            $replacedNode->resetContentCache();

            $replacedNode->runtimeNodes = $this->lexer->tokenize($replacedNode, $replacedNode->getContent());

            $this->testUnlessContent($replacedNode);

            return $replacedNode;
        }

        return $node;
    }

    /**
     * Tests the validity of an "unless" nodes content.
     *
     * Nodes with empty content are considered invalid,
     * and will throw a parser error by design.
     *
     * @param  AntlersNode  $node  The node to test.
     *
     * @throws SyntaxErrorException
     */
    private function testUnlessContent(AntlersNode $node)
    {
        $testContent = $node->getContent();

        $testContent = str_replace('!', '', $testContent);
        $testContent = str_replace('(', '', $testContent);
        $testContent = trim(str_replace(')', '', $testContent));

        if (mb_strlen($testContent) == 0) {
            $this->throwConditionWithoutExpression($node);
        }
    }

    /**
     * Throws an empty expression exception for the provided node.
     *
     * @param  AntlersNode  $node  The contextual node.
     *
     * @throws SyntaxErrorException
     */
    private function throwConditionWithoutExpression(AntlersNode $node)
    {
        throw ErrorFactory::makeSyntaxError(
            AntlersErrorCodes::TYPE_PARSE_EMPTY_CONDITIONAL,
            $node,
            'Condition structure lacks comparison expression.'
        );
    }

    private function isValidShorthandCharacter($char)
    {
        if (ctype_alpha($char) || $char == '_' || $char == '-' || ctype_digit($char)) {
            return true;
        }

        return false;
    }

    /**
     * Parses the content of the provided node, and returns any parameters.
     *
     * Parameters must be in the form of:
     *    parameter="value"
     *
     * @param  AntlersNode  $node  The node to analyze.
     * @return ParameterNode[]
     *
     * @throws SyntaxErrorException
     */
    private function getParameters(AntlersNode $node)
    {
        $content = $node->getContent();
        StringUtilities::prepareSplit($content);
        $chars = StringUtilities::split(trim($content));
        $parameters = [];
        $parsingShorthandVariable = false;

        $hasFoundName = false;
        $currentChars = [];
        $name = '';
        $nameStart = 0;
        $startAt = 0;
        $ignorePrevious = false;

        $terminator = null;

        $charCount = count($chars);

        // Calculate the index that appears immediately after the node's name (if available).
        // This index will be utilized to help determine if we should break early when
        // encountering strings. We want to break if we find strings outside of a
        // parameter only if they do not appear as part of the node's name.
        //
        // This should not cause the parser to exit early:
        //     {{ data['key'] first="true" }}
        //
        // This should cause parser to exit early:
        //     {{ data['other_key'] + 'first="true"'; }}
        $parseContentOffset = 0;

        if ($node->name != null) {
            $trimmedName = ltrim($node->content, ' ');
            $leadOffset = mb_strlen($node->content) - mb_strlen($trimmedName);
            $nameLength = mb_strlen($node->name->content);

            if ($leadOffset > 0) {
                $parseContentOffset = $leadOffset + $nameLength;
            } else {
                $parseContentOffset = $nameLength;
            }
        }

        for ($i = 0; $i < $charCount; $i++) {
            $current = $chars[$i];
            $prev = null;
            $next = null;

            if (($i + 1) < $charCount) {
                $next = $chars[$i + 1];
            }

            if (! $ignorePrevious) {
                if ($i > 0) {
                    $prev = $chars[$i - 1];
                }
            } else {
                $prev = '';
                $ignorePrevious = false;
            }

            if ($current == DocumentParser::Punctuation_Colon && $next == DocumentParser::Punctuation_Dollar) {
                $startAt = $i;
                $parsingShorthandVariable = true;
                $currentChars = [];

                $currentChars[] = $current;
                $currentChars[] = $next;

                if ($i + 2 < $charCount) {
                    $peek = $chars[$i + 2];

                    if (ctype_digit($peek)) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_CHARACTER_WHILE_PARSING_SHORTHAND_PARAMETER,
                            $node,
                            'Shorthand variable parameters cannot start with a number.'
                        );
                    } elseif (ctype_space($peek)) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EOI_PARSING_SHORTHAND_PARAMETER,
                            $node,
                            'Unexpected end of input while parsing shorthand variable parameter.'
                        );
                    }
                } else {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_PARSING_SHORTHAND_PARAMETER,
                        $node,
                        'Unexpected end of input while parsing shorthand variable parameter.'
                    );
                }

                $i += 1;

                continue;
            }

            if ($parsingShorthandVariable && ! ctype_space($current) && ! $this->isValidShorthandCharacter($current)) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_UNEXPECTED_CHARACTER_WHILE_PARSING_SHORTHAND_PARAMETER,
                    $node,
                    'Unexpected character while parsing shorthand variable parameter.'
                );
            }

            if ((ctype_space($current) || $next == null) && $parsingShorthandVariable) {
                $endAt = $i;

                if ($next == null) {
                    $currentChars[] = $current;
                    $endAt += 1;
                }

                $parameterNode = new ParameterNode();
                $parameterNode->isVariableReference = true;

                $name = implode('', array_slice($currentChars, 2));

                $parameterNode->name = $name;
                $parameterNode->value = $name;
                $parameterNode->startPosition = $node->relativePositionFromOffset($startAt, $nameStart);

                if (in_array($name, ['as', 'scope', 'handle_prefix'])) {
                    $node->hasScopeAdjustingParameters = true;
                }

                $parameterNode->endPosition = $node->relativePositionFromOffset($endAt, $endAt);
                $parameterNode->parent = $node;

                $parameters[] = $parameterNode;

                if ($next == null) {
                    break;
                }

                $currentChars = [];
                $hasFoundName = false;
                $parsingShorthandVariable = false;
                $name = '';

                continue;
            }

            if ($hasFoundName == false && ctype_space($current)) {
                // Flush the buffer.
                $currentChars = [];

                continue;
            }

            if ($hasFoundName == false && $current == DocumentParser::Punctuation_Equals) {
                if (! empty($currentChars)) {
                    if (! (ctype_alpha($currentChars[0]) || ctype_digit($currentChars[0]) || $currentChars[0] == DocumentParser::Punctuation_Colon || $currentChars[0] == DocumentParser::AtChar || $currentChars[0] == DocumentParser::String_EscapeCharacter)) {
                        $currentChars = [];

                        continue;
                    }
                }

                if ($i + 1 >= $charCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_END_OF_INPUT,
                        $node,
                        'Unexpected end of input.'
                    );
                }

                $peek = null;

                if ($i + 1 < $charCount) {
                    $peek = $chars[$i + 1];
                }

                if ($prev == DocumentParser::Punctuation_Equals) {
                    $currentChars = [];

                    continue;
                }

                if (ctype_space($peek) || $peek == DocumentParser::Punctuation_Equals) {
                    $currentChars = [];

                    continue;
                }

                if (! empty($currentChars)) {
                    $name = implode($currentChars);
                    $nameStart = $startAt;
                    $currentChars = [];
                    $hasFoundName = true;
                }

                if ($next == DocumentParser::String_Terminator_DoubleQuote) {
                    $terminator = DocumentParser::String_Terminator_DoubleQuote;
                    $i += 1;
                }

                if ($next == DocumentParser::String_Terminator_SingleQuote) {
                    $terminator = DocumentParser::String_Terminator_SingleQuote;
                    $i += 1;
                }

                continue;
            }

            if ($hasFoundName && $current == DocumentParser::String_EscapeCharacter) {
                $peek = null;

                if ($i + 1 < $charCount) {
                    $peek = $chars[$i + 1];
                }

                if ($peek == DocumentParser::Punctuation_Pipe) {
                    $currentChars = array_merge($currentChars, DocumentParser::getPipeEscapeArray());
                    $i += 1;

                    continue;
                }

                if ($peek == DocumentParser::String_EscapeCharacter) {
                    $currentChars[] = DocumentParser::String_EscapeCharacter;
                    $i += 1;
                    $ignorePrevious = true;

                    continue;
                }

                if ($peek == DocumentParser::String_Terminator_DoubleQuote) {
                    $currentChars[] = DocumentParser::String_Terminator_DoubleQuote;
                    $i += 1;

                    continue;
                }

                if ($peek == DocumentParser::String_Terminator_SingleQuote) {
                    $currentChars[] = DocumentParser::String_Terminator_SingleQuote;
                    $i += 1;

                    continue;
                }

                if ($peek == 'n') {
                    $currentChars[] = "\n";
                    $i += 1;

                    continue;
                }

                if ($peek == 'r') {
                    $currentChars[] = "\r";
                    $i += 1;

                    continue;
                }
            }

            if ($hasFoundName && (
                ($terminator != null && $current == $terminator) ||
                ($terminator == null && ctype_space($current))
            )) {
                $content = implode($currentChars);
                $hasFoundName = false;
                $terminator = null;
                $currentChars = [];

                $parameterNode = new ParameterNode();

                $parameterNode->originalName = $name;

                if (Str::startsWith($name, DocumentParser::Punctuation_Colon)) {
                    $parameterNode->isVariableReference = true;
                    $name = StringUtilities::substr($name, 1);

                    if (is_numeric($content)) {
                        $content = TypeCoercion::coerceType($content);
                        $parameterNode->isVariableReference = false;
                    }
                }

                if (Str::startsWith($name, DocumentParser::String_EscapeCharacter)) {
                    $parameterNode->containsEscapedContent = true;
                    $name = mb_substr($name, 1);
                }

                $parameterNode->name = $name;
                $parameterNode->value = $content;
                $parameterNode->startPosition = $node->relativePositionFromOffset($startAt, $nameStart);

                if (in_array($name, ['as', 'scope', 'handle_prefix'])) {
                    $node->hasScopeAdjustingParameters = true;
                }

                if ($i + 1 > $charCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_NODE_PARAMETER,
                        $node,
                        'Unexpected end of input while parsing parameter content.'
                    );
                }

                $parameterNode->endPosition = $node->relativePositionFromOffset($i + 1, $i + 1);
                $parameterNode->parent = $node;

                $parameters[] = $parameterNode;

                $name = '';

                continue;
            }

            $currentChars[] = $current;

            if ($hasFoundName == false && ($current == DocumentParser::String_Terminator_DoubleQuote || $current == DocumentParser::String_Terminator_SingleQuote)) {
                if ($i > $parseContentOffset) {
                    break;
                }
            }

            if (count($currentChars) == 1) {
                $startAt = $i + 1;
            }
        }

        if ($terminator != null) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_UNEXPECTED_END_OF_INPUT,
                $node,
                'Unexpected end of input while parsing string.'
            );
        }

        return $parameters;
    }
}
