<?php

namespace Statamic\View\Antlers\Language\Lexer;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Errors\TypeLabeler;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\MethodInvocationNode;
use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;
use Statamic\View\Antlers\Language\Nodes\ModifierValueNode;
use Statamic\View\Antlers\Language\Nodes\NumberNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\AdditionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\DivisionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\ExponentiationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\ModulusOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\MultiplicationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\SubtractionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\AdditionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\DivisionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\LeftAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\ModulusAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\MultiplicationAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\SubtractionAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\EqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotStrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\SpaceshipCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\StrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ConditionalVariableFallbackOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalAndOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalNegationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalXorOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\NullCoalesceOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ScopeAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\StringConcatenationOperator;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ArgSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ImplicitArrayBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\ImplicitArrayEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineBranchSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineTernarySeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierValueSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\StatementSeparatorNode;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleListStart;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageKeywords;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class AntlersLexer
{
    private $chars = [];
    private $inputLen = 0;
    private $currentIndex = 0;
    private $currentContent = [];
    private $startIndex = 0;
    private $cur = null;
    private $next = null;
    private $prev = null;
    private $isParsingString = false;
    private $isParsingModifierName = false;
    private $isInModifierParameterValue = false;
    private $runtimeNodes = [];
    private $ignorePrevious = false;
    private $lastNode = null;

    /**
     * @var DocumentParser|null
     */
    private $referenceParser = null;

    /**
     * The AntlersNode instance actively being analyzed.
     *
     * @var AntlersNode|null
     */
    private $activeNode = null;

    private function reset()
    {
        $this->ignorePrevious = false;
        $this->runtimeNodes = [];
        $this->chars = [];
        $this->inputLen = 0;
        $this->currentIndex = 0;
        $this->currentContent = [];
        $this->startIndex = 0;
        $this->cur = null;
        $this->next = null;
        $this->prev = null;
        $this->isParsingString = false;
        $this->isParsingModifierName = false;
        $this->isInModifierParameterValue = false;
        $this->referenceParser = null;
        $this->activeNode = null;
    }

    private function checkCurrentOffsets()
    {
        $this->cur = $this->chars[$this->currentIndex];
        $this->prev = null;
        $this->next = null;

        if (! $this->ignorePrevious) {
            if ($this->currentIndex > 0) {
                $this->prev = $this->chars[$this->currentIndex - 1];
            }
        } else {
            $this->prev = '';
            $this->ignorePrevious = false;
        }

        if (($this->currentIndex + 1) < $this->inputLen) {
            $this->next = $this->chars[$this->currentIndex + 1];
        }
    }

    protected function isValidChar($char)
    {
        if ($char === null) {
            return false;
        }

        if ($char == DocumentParser::Punctuation_Semicolon) {
            return false;
        }

        if ($this->isParsingString == false && $char == ']') {
            return false;
        }

        if ($this->isParsingString == false && $char == ')') {
            return false;
        }

        if (($char == '_' || $char == '.') &&
            (! empty($this->currentContent) || ctype_alpha($this->cur))) {
            return true;
        }

        if (ctype_space($char)) {
            return false;
        }

        if ($this->isParsingModifierName && ($char == DocumentParser::Punctuation_Minus || $char == DocumentParser::Punctuation_Underscore)) {
            return true;
        }

        if (ctype_punct($char)) {
            return false;
        }

        return true;
    }

    private function isRightOfInterpolationRegion()
    {
        $relative = $this->activeNode->relativeOffset($this->currentIndex);

        return $this->referenceParser->bordersInterpolationRegion($relative);
    }

    private function scanForwardTo($char, $skip = 0)
    {
        $returnChars = [];

        for ($i = $this->currentIndex + 1 + $skip; $i < $this->inputLen; $i++) {
            $cur = $this->chars[$i];

            if ($cur == $char) {
                $returnChars[] = $cur;
                break;
            } else {
                $returnChars[] = $cur;
            }
        }

        return $returnChars;
    }

    private function nextNonWhitespace()
    {
        for ($i = $this->currentIndex + 1; $i < $this->inputLen; $i++) {
            $cur = $this->chars[$i];

            if (! ctype_space($cur)) {
                return $cur;
            }
        }

        return null;
    }

    private function guardAgainstNeighboringTypesInModifier($current)
    {
        if ($this->lastNode instanceof ModifierValueNode) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_MODIFIER_INCORRECT_VALUE_POSITION,
                $this->lastNode,
                'Incorrect type ['.TypeLabeler::getPrettyTypeName($current).'] near ['.TypeLabeler::getPrettyTypeName($this->lastNode).']'
            );
        }
    }

    private function adjustValueConsideringWhitespace($value)
    {
        if (strlen(trim($value)) == 0) {
            return $value;
        }

        return rtrim($value);
    }

    public function tokenize(AntlersNode $node, $input)
    {
        $this->reset();
        $this->referenceParser = $node->getParser();
        $this->activeNode = $node;

        StringUtilities::prepareSplit($input);
        $this->chars = StringUtilities::split($input);
        $this->inputLen = count($this->chars);
        $this->runtimeNodes = [];
        $this->lastNode = null;

        $stringStartedOn = null;
        $this->isParsingString = false;
        $this->isParsingModifierName = false;
        $terminator = null;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->isParsingString == false) {
                if ($this->cur == DocumentParser::Punctuation_FullStop && $this->next == DocumentParser::Punctuation_Equals) {
                    $stringConcat = new StringConcatenationOperator();
                    $stringConcat->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $stringConcat->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);
                    $stringConcat->content = '.=';
                    $this->currentContent = [];
                    $this->runtimeNodes[] = $stringConcat;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::String_Terminator_DoubleQuote || $this->cur == DocumentParser::String_Terminator_SingleQuote) {
                    if ($this->prev == DocumentParser::String_EscapeCharacter) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ILLEGAL_STRING_ESCAPE_SEQUENCE,
                            $node,
                            'Illegal string escape sequence outside string parsing.'
                        );
                    }

                    $terminator = $this->cur;
                    $this->isParsingString = true;
                    $stringStartedOn = $this->currentIndex;
                    continue;
                }
            }

            if ($this->isInModifierParameterValue && ! $this->isParsingString) {
                $breakForKeyword = false;

                if (! $this->isParsingString && $this->next != null && ctype_space($this->next)) {
                    $nextWord = strtolower(trim(implode($this->scanForwardTo(' ', 1))));

                    if (strlen($nextWord) > 0 && LanguageKeywords::isLanguageLogicalKeyword($nextWord)) {
                        $breakForKeyword = true;
                    }
                }

                if ($this->next == DocumentParser::String_Terminator_SingleQuote ||
                    $this->next == DocumentParser::String_Terminator_DoubleQuote ||
                    $this->next == null ||
                    $breakForKeyword) {
                    $this->currentContent[] = $this->cur;
                    $implodedCurrentContent = implode($this->currentContent);

                    if (mb_strlen($implodedCurrentContent) > 0) {
                        $parsedValue = implode($this->currentContent);
                        $this->currentContent = [];

                        // If we just received a lot of whitespace, and the next character
                        // looks like it may be a string, let's discard what we've got.
                        if (strlen(rtrim($parsedValue)) === 0) {
                            $nextNonWhitespace = $this->nextNonWhitespace();

                            if ($nextNonWhitespace === DocumentParser::String_Terminator_SingleQuote ||
                                $nextNonWhitespace === DocumentParser::String_Terminator_DoubleQuote) {
                                $this->currentContent = [];
                                continue;
                            }
                        }

                        $modifierValueNode = new ModifierValueNode();
                        $modifierValueNode->name = $parsedValue;
                        $modifierValueNode->value = $this->adjustValueConsideringWhitespace($parsedValue);
                        $modifierValueNode->startPosition = $node->lexerRelativeOffset($this->currentIndex - mb_strlen($parsedValue));
                        $modifierValueNode->endPosition = $node->lexerRelativeOffset($this->currentIndex);

                        $this->guardAgainstNeighboringTypesInModifier($modifierValueNode);

                        $this->runtimeNodes[] = $modifierValueNode;
                        $this->lastNode = $modifierValueNode;
                    }

                    if (strlen($implodedCurrentContent) == 0) {
                        continue;
                    }

                    $this->currentContent = [];
                    $this->isInModifierParameterValue = false;
                    continue;
                }

                if ($this->next == DocumentParser::Punctuation_Pipe ||
                    $this->next == DocumentParser::Punctuation_Colon ||
                    $this->next == DocumentParser::RightParent) {
                    $this->isInModifierParameterValue = false;
                    $this->currentContent[] = $this->cur;

                    $additionalSkip = 0;
                    $trimStartEnd = false;

                    if ($this->currentContent[0] == DocumentParser::String_Terminator_SingleQuote ||
                        $this->currentContent[0] == DocumentParser::String_Terminator_DoubleQuote) {
                        $scan = $this->scanForwardTo($this->currentContent[0]);

                        if (! empty($scan)) {
                            $this->currentContent = array_merge($this->currentContent, $scan);
                            $additionalSkip = count($scan);
                            $trimStartEnd = true;
                        }
                    }

                    $parsedValue = implode($this->currentContent);

                    if ($trimStartEnd) {
                        $parsedValue = StringUtilities::substr($parsedValue, 1);
                        $parsedValue = StringUtilities::substr($parsedValue, 0, -1);
                    }

                    $this->currentContent = [];

                    $modifierValueNode = new ModifierValueNode();
                    $modifierValueNode->name = $parsedValue;
                    $modifierValueNode->value = $this->adjustValueConsideringWhitespace($parsedValue);
                    $modifierValueNode->startPosition = $node->lexerRelativeOffset($this->currentIndex - mb_strlen($parsedValue));
                    $modifierValueNode->endPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $this->runtimeNodes[] = $modifierValueNode;

                    $this->guardAgainstNeighboringTypesInModifier($modifierValueNode);

                    $this->lastNode = $modifierValueNode;

                    $this->currentIndex += $additionalSkip;
                } else {
                    $this->currentContent[] = $this->cur;
                }
                continue;
            }

            if ($this->isParsingString && $this->cur == $terminator) {
                if ($this->prev == DocumentParser::String_EscapeCharacter) {
                    $this->currentContent[] = $terminator;
                } else {
                    if ($this->isInModifierParameterValue) {
                        $parsedValue = implode($this->currentContent);

                        $modifierValueNode = new ModifierValueNode();
                        $modifierValueNode->name = $parsedValue;
                        $modifierValueNode->value = $this->adjustValueConsideringWhitespace($parsedValue);
                        $modifierValueNode->startPosition = $node->lexerRelativeOffset($stringStartedOn);
                        $modifierValueNode->endPosition = $node->lexerRelativeOffset($this->currentIndex);

                        $this->runtimeNodes[] = $modifierValueNode;

                        $this->guardAgainstNeighboringTypesInModifier($modifierValueNode);

                        $this->lastNode = $modifierValueNode;

                        $this->currentContent = [];
                        $this->isParsingString = false;
                        $this->isInModifierParameterValue = false;
                        continue;
                    }

                    $stringNode = new StringValueNode();
                    $stringNode->startPosition = $node->lexerRelativeOffset($stringStartedOn);
                    $stringNode->endPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $stringNode->sourceTerminator = $terminator;
                    $terminator = null;
                    $this->isParsingString = false;
                    $stringNode->value = implode($this->currentContent);

                    $this->currentContent = [];

                    $this->guardAgainstNeighboringTypesInModifier($stringNode);

                    $this->runtimeNodes[] = $stringNode;
                    $this->lastNode = $stringNode;
                }
                continue;
            }

            if ($this->isParsingString && $this->cur == DocumentParser::String_EscapeCharacter) {
                if ($this->next == DocumentParser::String_EscapeCharacter) {
                    $this->currentContent[] = DocumentParser::String_EscapeCharacter;
                    $this->ignorePrevious = true;
                    $this->currentIndex += 1;
                    continue;
                } elseif ($this->next == DocumentParser::String_Terminator_SingleQuote) {
                    $this->currentContent[] = DocumentParser::String_Terminator_SingleQuote;
                    $this->currentIndex += 1;
                    continue;
                } elseif ($this->next == DocumentParser::String_Terminator_DoubleQuote) {
                    $this->currentContent[] = DocumentParser::String_Terminator_DoubleQuote;
                    $this->currentIndex += 1;
                    continue;
                } elseif ($this->next == 'n') {
                    $this->currentContent[] = "\n";
                    $this->currentIndex += 1;
                    continue;
                } elseif ($this->next == 't') {
                    $this->currentContent[] = "\t";
                    $this->currentIndex += 1;
                    continue;
                } elseif ($this->next == 'r') {
                    $this->currentContent[] = "\r";
                    $this->currentIndex += 1;
                    continue;
                }
            }

            if ($this->isParsingString == false) {
                $addCurrent = true;

                if ($this->isValidChar($this->cur) && $this->isValidChar($this->next) == false && count($this->currentContent) == 0) {
                    $this->currentContent[] = $this->cur;
                    $addCurrent = false;
                }

                if (($this->next == null || $this->isValidChar($this->next) == false) && count($this->currentContent) > 0) {
                    if ($addCurrent) {
                        $this->currentContent[] = $this->cur;
                    }

                    $parsedValue = trim(implode('', $this->currentContent));
                    $lowerParsedValue = strtolower($parsedValue);
                    $valueLen = mb_strlen($parsedValue);
                    $valueStartIndex = $this->currentIndex - $valueLen;
                    $startPosition = $node->lexerRelativeOffset($this->currentIndex - mb_strlen($parsedValue));
                    $endPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $this->currentContent = [];

                    // Check against internal keywords.
                    if ($lowerParsedValue == LanguageKeywords::LogicalAnd) {
                        $logicalAnd = new LogicalAndOperator();
                        $logicalAnd->content = LanguageKeywords::LogicalAnd;
                        $logicalAnd->startPosition = $startPosition;
                        $logicalAnd->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalAnd;
                        $this->lastNode = $logicalAnd;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::LogicalOr) {
                        $logicalOr = new LogicalOrOperator();
                        $logicalOr->content = LanguageKeywords::LogicalOr;
                        $logicalOr->startPosition = $startPosition;
                        $logicalOr->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalOr;
                        $this->lastNode = $logicalOr;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::LogicalXor) {
                        $logicalXor = new LogicalXorOperator();
                        $logicalXor->content = LanguageKeywords::LogicalXor;
                        $logicalXor->startPosition = $startPosition;
                        $logicalXor->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalXor;
                        $this->lastNode = $logicalXor;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::ConstNull) {
                        $constNull = new NullConstant();
                        $constNull->content = LanguageKeywords::ConstNull;
                        $constNull->startPosition = $startPosition;
                        $constNull->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constNull;
                        $this->lastNode = $constNull;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::ConstTrue) {
                        $constTrue = new TrueConstant();
                        $constTrue->content = LanguageKeywords::ConstTrue;
                        $constTrue->startPosition = $startPosition;
                        $constTrue->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constTrue;
                        $this->lastNode = $constTrue;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::ConstFalse) {
                        $constFalse = new FalseConstant();
                        $constFalse->content = LanguageKeywords::ConstFalse;
                        $constFalse->startPosition = $startPosition;
                        $constFalse->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constFalse;
                        $this->lastNode = $constFalse;
                        continue;
                    } elseif ($lowerParsedValue == LanguageKeywords::LogicalNot) {
                        $logicNegation = new LogicalNegationOperator();
                        $logicNegation->content = LanguageKeywords::LogicalNot;
                        $logicNegation->startPosition = $startPosition;
                        $logicNegation->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicNegation;
                        $this->lastNode = $logicNegation;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::ArrList && $this->next == DocumentParser::LeftParen) {
                        $tupleListStart = new TupleListStart();
                        $tupleListStart->content = LanguageKeywords::ArrList;
                        $tupleListStart->startPosition = $startPosition;
                        $tupleListStart->endPosition = $endPosition;

                        $this->runtimeNodes[] = $tupleListStart;
                        $this->lastNode = $tupleListStart;
                        continue;
                    }

                    if (is_numeric($parsedValue)) {
                        $numberNode = new NumberNode();
                        $numberNode->startPosition = $startPosition;
                        $numberNode->endPosition = $endPosition;

                        if (Str::contains($parsedValue, '.')) {
                            $numberNode->value = floatval($parsedValue);
                        } else {
                            $numberNode->value = intval($parsedValue);
                        }
                        $this->guardAgainstNeighboringTypesInModifier($numberNode);

                        $this->runtimeNodes[] = $numberNode;
                        $this->lastNode = $numberNode;
                        continue;
                    }

                    if (! empty($this->runtimeNodes)) {
                        $lastValue = $this->runtimeNodes[count($this->runtimeNodes) - 1];

                        if ($lastValue instanceof ModifierSeparator) {
                            $modifierNameNode = new ModifierNameNode();
                            $modifierNameNode->name = $parsedValue;
                            $modifierNameNode->startPosition = $startPosition;
                            $modifierNameNode->endPosition = $endPosition;
                            $this->runtimeNodes[] = $modifierNameNode;
                            $this->lastNode = $modifierNameNode;
                            continue;
                        } elseif ($lastValue instanceof ModifierValueSeparator) {
                            $modifierValueNode = new ModifierValueNode();
                            $modifierValueNode->name = $parsedValue;
                            $modifierValueNode->value = $parsedValue;
                            $modifierValueNode->startPosition = $startPosition;
                            $modifierValueNode->endPosition = $endPosition;
                            $this->runtimeNodes[] = $modifierValueNode;
                            $this->lastNode = $modifierValueNode;

                            $this->isParsingModifierName = false;
                            continue;
                        }
                    }

                    if ($parsedValue == DocumentParser::Punctuation_Minus) {
                        $subtractionOperator = new SubtractionOperator();
                        $subtractionOperator->content = '-';
                        $subtractionOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $subtractionOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                        $this->runtimeNodes[] = $subtractionOperator;
                        $this->lastNode = $subtractionOperator;
                    } else {
                        $variableRefNode = new VariableNode();
                        $variableRefNode->name = $parsedValue;
                        $variableRefNode->startPosition = $startPosition;
                        $variableRefNode->endPosition = $endPosition;
                        $this->runtimeNodes[] = $variableRefNode;
                        $this->lastNode = $variableRefNode;
                    }

                    continue;
                }
            } else {
                $this->currentContent[] = $this->cur;
                continue;
            }

            if (ctype_space($this->cur)) {
                continue;
            }

            if ($this->isParsingString == false) {
                if ($this->cur == DocumentParser::Punctuation_Equals && $this->next == DocumentParser::Punctuation_GreaterThan) {
                    $scopeAssignment = new ScopeAssignmentOperator();
                    $scopeAssignment->content = '=>';
                    $scopeAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $scopeAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $scopeAssignment;
                    $this->lastNode = $scopeAssignment;
                    $this->currentContent = [];
                    $this->currentIndex += 1;

                    continue;
                }

                // ,
                if ($this->cur == DocumentParser::Punctuation_Comma) {
                    $argSeparator = new ArgSeparator();
                    $argSeparator->content = DocumentParser::Punctuation_Comma;
                    $argSeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $argSeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $argSeparator;
                    $this->lastNode = $argSeparator;
                    continue;
                }

                // ;
                if ($this->cur == DocumentParser::Punctuation_Semicolon) {
                    $statementSeparator = new StatementSeparatorNode();
                    $statementSeparator->content = DocumentParser::Punctuation_Semicolon;
                    $statementSeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $statementSeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $statementSeparator;
                    $this->lastNode = $statementSeparator;
                    continue;
                }

                // +
                if ($this->cur == DocumentParser::Punctuation_Plus) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $additionAssignment = new AdditionAssignmentOperator();
                        $additionAssignment->content = '+=';
                        $additionAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $additionAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $additionAssignment;
                        $this->lastNode = $additionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $additionOperator = new AdditionOperator();
                    $additionOperator->content = '+';
                    $additionOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $additionOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $additionOperator;
                    $this->lastNode = $additionOperator;
                    continue;
                }

                // -
                if ($this->isParsingModifierName == false && $this->cur == DocumentParser::Punctuation_Minus) {
                    if (ctype_digit($this->next) && (
                            ctype_digit((string) $this->prev) == false &&
                            $this->prev != DocumentParser::RightParent) && $this->isRightOfInterpolationRegion() == false) {
                        $this->currentContent[] = $this->cur;
                        continue;
                    }

                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $subtractionAssignment = new SubtractionAssignmentOperator();
                        $subtractionAssignment->content = '-=';
                        $subtractionAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $subtractionAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $subtractionAssignment;
                        $this->lastNode = $subtractionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    } elseif ($this->next == DocumentParser::Punctuation_GreaterThan) {
                        $methodInvocation = new MethodInvocationNode();
                        $methodInvocation->content = '->';
                        $methodInvocation->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $methodInvocation->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $methodInvocation;
                        $this->lastNode = $methodInvocation;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $subtractionOperator = new SubtractionOperator();
                    $subtractionOperator->content = '-';
                    $subtractionOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $subtractionOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $subtractionOperator;
                    $this->lastNode = $subtractionOperator;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Asterisk) {
                    // **
                    if ($this->next == DocumentParser::Punctuation_Asterisk) {
                        $exponentiationOperator = new ExponentiationOperator();
                        $exponentiationOperator->content = '**';
                        $exponentiationOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $exponentiationOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $exponentiationOperator;
                        $this->lastNode = $exponentiationOperator;
                        $this->currentIndex += 1;
                        continue;
                    } elseif ($this->next == DocumentParser::Punctuation_Equals) {
                        $multiplicationAssignment = new MultiplicationAssignmentOperator();
                        $multiplicationAssignment->content = '*=';
                        $multiplicationAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $multiplicationAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $multiplicationAssignment;
                        $this->lastNode = $multiplicationAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // *
                    $multiplicationOperator = new MultiplicationOperator();
                    $multiplicationOperator->content = '*';
                    $multiplicationOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $multiplicationOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $multiplicationOperator;
                    $this->lastNode = $multiplicationOperator;
                    continue;
                }

                // /
                if ($this->cur == DocumentParser::Punctuation_ForwardSlash) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $divisionAssignment = new DivisionAssignmentOperator();
                        $divisionAssignment->content = '/=';
                        $divisionAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $divisionAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $divisionAssignment;
                        $this->lastNode = $divisionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $divisionOperator = new DivisionOperator();
                    $divisionOperator->content = '/';
                    $divisionOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $divisionOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $divisionOperator;
                    $this->lastNode = $divisionOperator;
                    continue;
                }

                // %
                if ($this->cur == DocumentParser::Punctuation_Percent) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $modulusAssignment = new ModulusAssignmentOperator();
                        $modulusAssignment->content = '%=';
                        $modulusAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $modulusAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $modulusAssignment;
                        $this->lastNode = $modulusAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $modulusOperator = new ModulusOperator();
                    $modulusOperator->content = '%';
                    $modulusOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $modulusOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $modulusOperator;
                    $this->lastNode = $modulusOperator;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_LessThan) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $peek = null;

                        if (($this->currentIndex + 2) < $this->inputLen) {
                            $peek = $this->chars[$this->currentIndex + 2];
                        }

                        // <=>
                        if ($peek == DocumentParser::Punctuation_GreaterThan) {
                            $spaceshipOperator = new SpaceshipCompOperator();
                            $spaceshipOperator->content = '<=>';
                            $spaceshipOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                            $spaceshipOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 3);

                            $this->runtimeNodes[] = $spaceshipOperator;
                            $this->lastNode = $spaceshipOperator;
                            $this->currentIndex += 2;
                            continue;
                        }

                        // <=
                        $lessThanEqual = new LessThanEqualCompOperator();
                        $lessThanEqual->content = '<=';
                        $lessThanEqual->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $lessThanEqual->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $lessThanEqual;
                        $this->lastNode = $lessThanEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // <
                    $lessThan = new LessThanCompOperator();
                    $lessThan->content = '<';
                    $lessThan->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $lessThan->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $lessThan;
                    $this->lastNode = $lessThan;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_GreaterThan) {
                    // >=
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $greaterThanEqual = new GreaterThanEqualCompOperator();
                        $greaterThanEqual->content = '>=';
                        $greaterThanEqual->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $greaterThanEqual->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $greaterThanEqual;
                        $this->lastNode = $greaterThanEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $greaterThan = new GreaterThanCompOperator();
                    $greaterThan->content = '>';
                    $greaterThan->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $greaterThan->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $greaterThan;
                    $this->lastNode = $greaterThan;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Equals && $this->next != DocumentParser::Punctuation_Equals) {
                    $leftAssignment = new LeftAssignmentOperator();
                    $leftAssignment->content = '=';
                    $leftAssignment->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $leftAssignment->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $leftAssignment;
                    $this->lastNode = $leftAssignment;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Equals && $this->next == DocumentParser::Punctuation_Equals) {
                    $peek = null;

                    if (($this->currentIndex + 2) < $this->inputLen) {
                        $peek = $this->chars[$this->currentIndex + 2];
                    }

                    if ($peek == DocumentParser::Punctuation_Equals) {
                        // ===
                        $strictEqual = new StrictEqualCompOperator();
                        $strictEqual->content = '===';
                        $strictEqual->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $strictEqual->endPosition = $node->lexerRelativeOffset($this->currentIndex + 3);

                        $this->runtimeNodes[] = $strictEqual;
                        $this->lastNode = $strictEqual;
                        $this->currentIndex += 2;
                    } else {
                        // ==
                        $equalOperator = new EqualCompOperator();
                        $equalOperator->content = '==';
                        $equalOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $equalOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $equalOperator;
                        $this->lastNode = $equalOperator;
                        $this->currentIndex += 1;
                    }
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Ampersand) {
                    // &&
                    if ($this->next == DocumentParser::Punctuation_Ampersand) {
                        $logicalAnd = new LogicalAndOperator();
                        $logicalAnd->content = '&&';
                        $logicalAnd->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $logicalAnd->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $logicalAnd;
                        $this->lastNode = $logicalAnd;
                        $this->currentIndex += 1;
                        continue;
                    }

                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $concatOperator = new StringConcatenationOperator();
                        $concatOperator->content = '&=';
                        $concatOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $concatOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                        $this->runtimeNodes[] = $concatOperator;
                        $this->lastNode = $concatOperator;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $logicalAnd = new LogicalAndOperator();
                    $logicalAnd->content = '&';
                    $logicalAnd->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $logicalAnd->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalAnd;
                    $this->lastNode = $logicalAnd;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Pipe && $this->next != DocumentParser::Punctuation_Pipe) {
                    $modifierSeparator = new ModifierSeparator();
                    $modifierSeparator->content = '|';
                    $modifierSeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $modifierSeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $modifierSeparator;
                    $this->lastNode = $modifierSeparator;

                    $this->isParsingModifierName = true;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Pipe && $this->next == DocumentParser::Punctuation_Pipe) {
                    // ||
                    $logicalOr = new LogicalOrOperator();
                    $logicalOr->content = '||';
                    $logicalOr->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $logicalOr->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $logicalOr;
                    $this->lastNode = $logicalOr;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Exclamation) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $peek = null;

                        if (($this->currentIndex + 2) < $this->inputLen) {
                            $peek = $this->chars[$this->currentIndex + 2];
                        }

                        if ($peek == DocumentParser::Punctuation_Equals) {
                            // !==
                            $strictNotEqual = new NotStrictEqualCompOperator();
                            $strictNotEqual->content = '!==';
                            $strictNotEqual->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                            $strictNotEqual->endPosition = $node->lexerRelativeOffset($this->currentIndex + 3);

                            $this->runtimeNodes[] = $strictNotEqual;
                            $this->lastNode = $strictNotEqual;
                            $this->currentIndex += 2;
                            continue;
                        }

                        // !=
                        $notEqual = new NotEqualCompOperator();
                        $notEqual->content = '!=';
                        $notEqual->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                        $notEqual->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $notEqual;
                        $this->lastNode = $notEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // !
                    $logicalNot = new LogicalNegationOperator();
                    $logicalNot->content = '!';
                    $logicalNot->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $logicalNot->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalNot;
                    $this->lastNode = $logicalNot;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Equals) {
                    // ?=
                    $conditionalFallback = new ConditionalVariableFallbackOperator();
                    $conditionalFallback->content = '?=';
                    $conditionalFallback->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $conditionalFallback->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $conditionalFallback;
                    $this->lastNode = $conditionalFallback;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Question) {
                    // ??
                    $nullCoalesceOperator = new NullCoalesceOperator();
                    $nullCoalesceOperator->content = '??';
                    $nullCoalesceOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $nullCoalesceOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $nullCoalesceOperator;
                    $this->lastNode = $nullCoalesceOperator;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Colon) {
                    // ?:
                    $nullCoalesceOperator = new NullCoalesceOperator();
                    $nullCoalesceOperator->content = '?:';
                    $nullCoalesceOperator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $nullCoalesceOperator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $nullCoalesceOperator;
                    $this->lastNode = $nullCoalesceOperator;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question) {
                    // ?
                    $ternarySeparator = new InlineTernarySeparator();
                    $ternarySeparator->content = '?';
                    $ternarySeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $ternarySeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $ternarySeparator;
                    $this->lastNode = $ternarySeparator;
                    continue;
                }

                if ($this->cur == DocumentParser::LeftParen) {
                    $logicalGroupBegin = new LogicGroupBegin();
                    $logicalGroupBegin->content = '(';
                    $logicalGroupBegin->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $logicalGroupBegin->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalGroupBegin;
                    $this->lastNode = $logicalGroupBegin;
                    continue;
                }

                if ($this->cur == DocumentParser::RightParent) {
                    $logicalGroupEnd = new LogicGroupEnd();
                    $logicalGroupEnd->content = ')';
                    $logicalGroupEnd->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $logicalGroupEnd->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalGroupEnd;
                    $this->lastNode = $logicalGroupEnd;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Colon) {
                    if (count($this->runtimeNodes)) {
                        $lastItem = $this->runtimeNodes[count($this->runtimeNodes) - 1];

                        if ($lastItem instanceof ModifierNameNode) {
                            $modifierValueSeparator = new ModifierValueSeparator();
                            $modifierValueSeparator->content = ':';
                            $modifierValueSeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                            $modifierValueSeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                            $this->runtimeNodes[] = $modifierValueSeparator;

                            $this->lastNode = $modifierValueSeparator;

                            if ($this->isInModifierParameterValue == false) {
                                $this->isInModifierParameterValue = true;
                            } else {
                                $this->isInModifierParameterValue = false;
                            }
                            continue;
                        }
                    }

                    $branchSeparator = new InlineBranchSeparator();
                    $branchSeparator->content = ':';
                    $branchSeparator->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $branchSeparator->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $branchSeparator;
                    $this->lastNode = $branchSeparator;
                    $this->isParsingModifierName = false;
                    continue;
                }

                if ($this->cur == DocumentParser::LeftBracket) {
                    $implicitArrayBegin = new ImplicitArrayBegin();
                    $implicitArrayBegin->content = '[';
                    $implicitArrayBegin->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $implicitArrayBegin->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $implicitArrayBegin;
                    $this->lastNode = $implicitArrayBegin;
                    continue;
                }

                if ($this->cur == DocumentParser::RightBracket) {
                    $implicitArrayEnd = new ImplicitArrayEnd();
                    $implicitArrayEnd->content = ']';
                    $implicitArrayEnd->startPosition = $node->lexerRelativeOffset($this->currentIndex);
                    $implicitArrayEnd->endPosition = $node->lexerRelativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $implicitArrayEnd;
                    $this->lastNode = $implicitArrayEnd;
                    continue;
                }
            }

            if ($addCurrent) {
                $this->currentContent[] = $this->cur;
            }
        }

        return $this->runtimeNodes;
    }
}
