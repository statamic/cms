<?php

namespace Statamic\View\Antlers\Language\Lexer;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
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
use Statamic\View\Antlers\Language\Nodes\Structures\InlineBranchSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineTernarySeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicalGroupBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicalGroupEnd;
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

        if ($this->currentIndex > 0) {
            $this->prev = $this->chars[$this->currentIndex - 1];
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

        if ($this->isParsingString == false && $char == ')') {
            return false;
        }

        if (($char == '_' || $char == '.' || $char == '[' || $char == ']') &&
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

    public function tokenize(AntlersNode $node, $input)
    {
        $this->reset();
        $this->referenceParser = $node->getParser();
        $this->activeNode = $node;

        StringUtilities::prepareSplit($input);
        $this->chars = StringUtilities::split($input);
        $this->inputLen = count($this->chars);
        $this->runtimeNodes = [];

        $stringStartedOn = null;
        $this->isParsingString = false;
        $this->isParsingModifierName = false;
        $terminator = null;

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex += 1) {
            $this->checkCurrentOffsets();

            if ($this->isInModifierParameterValue) {
                $breakForKeyword = false;

                if (! $this->isParsingString && ctype_space($this->next)) {
                    $nextWord = strtolower(trim(implode($this->scanForwardTo(' ', 1))));

                    if (strlen($nextWord) > 0 && LanguageKeywords::isLanguageLogicalKeyword($nextWord)) {
                        $breakForKeyword = true;
                    }
                }

                if ($this->next == DocumentParser::String_Terminator_SingleQuote ||
                    $this->next == DocumentParser::String_Terminator_DoubleQuote ||
                    $this->next == null ||
                    $breakForKeyword) {
                    $implodedCurrentContent = implode($this->currentContent);

                    if (mb_strlen(trim($implodedCurrentContent)) > 0) {
                        $this->currentContent[] = $this->cur;
                        $parsedValue = implode($this->currentContent);
                        $this->currentContent = [];

                        $modifierValueNode = new ModifierValueNode();
                        $modifierValueNode->name = $parsedValue;
                        $modifierValueNode->value = rtrim($parsedValue);
                        $modifierValueNode->startPosition = $node->relativeOffset($this->currentIndex - mb_strlen($parsedValue));
                        $modifierValueNode->endPosition = $node->relativeOffset($this->currentIndex);
                        $this->runtimeNodes[] = $modifierValueNode;
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
                    $modifierValueNode->value = $parsedValue;
                    $modifierValueNode->startPosition = $node->relativeOffset($this->currentIndex - mb_strlen($parsedValue));
                    $modifierValueNode->endPosition = $node->relativeOffset($this->currentIndex);
                    $this->runtimeNodes[] = $modifierValueNode;

                    $this->currentIndex += $additionalSkip;
                } else {
                    $this->currentContent[] = $this->cur;
                }
                continue;
            }

            if ($this->isParsingString == false) {
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

            if ($this->isParsingString && $this->cur == $terminator) {
                if ($this->prev == DocumentParser::String_EscapeCharacter) {
                    $this->currentContent[] = $terminator;
                } else {
                    $stringNode = new StringValueNode();
                    $stringNode->startPosition = $node->relativeOffset($stringStartedOn);
                    $stringNode->endPosition = $node->relativeOffset($this->currentIndex);
                    $stringNode->sourceTerminator = $terminator;
                    $terminator = null;
                    $this->isParsingString = false;
                    $stringNode->value = implode($this->currentContent);

                    $this->currentContent = [];
                    $this->runtimeNodes[] = $stringNode;
                }
                continue;
            }

            if ($this->isParsingString && $this->cur == DocumentParser::String_EscapeCharacter) {
                if ($this->next == DocumentParser::String_EscapeCharacter) {
                    $this->currentContent[] = DocumentParser::String_EscapeCharacter;
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
                    $valueLen = mb_strlen($parsedValue);
                    $valueStartIndex = $this->currentIndex - $valueLen;
                    $startPosition = $node->relativeOffset($valueStartIndex);
                    $endPosition = $node->relativeOffset($this->currentIndex);
                    $this->currentContent = [];

                    // Check against internal keywords.
                    if ($parsedValue == LanguageKeywords::LogicalAnd) {
                        $logicalAnd = new LogicalAndOperator();
                        $logicalAnd->content = LanguageKeywords::LogicalAnd;
                        $logicalAnd->startPosition = $startPosition;
                        $logicalAnd->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalAnd;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::LogicalOr) {
                        $logicalOr = new LogicalOrOperator();
                        $logicalOr->content = LanguageKeywords::LogicalOr;
                        $logicalOr->startPosition = $startPosition;
                        $logicalOr->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalOr;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::LogicalXor) {
                        $logicalXor = new LogicalXorOperator();
                        $logicalXor->content = LanguageKeywords::LogicalXor;
                        $logicalXor->startPosition = $startPosition;
                        $logicalXor->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicalXor;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::ConstNull) {
                        $constNull = new NullConstant();
                        $constNull->content = LanguageKeywords::ConstNull;
                        $constNull->startPosition = $startPosition;
                        $constNull->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constNull;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::ConstTrue) {
                        $constTrue = new TrueConstant();
                        $constTrue->content = LanguageKeywords::ConstNull;
                        $constTrue->startPosition = $startPosition;
                        $constTrue->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constTrue;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::ConstFalse) {
                        $constFalse = new FalseConstant();
                        $constFalse->content = LanguageKeywords::ConstFalse;
                        $constFalse->startPosition = $startPosition;
                        $constFalse->endPosition = $endPosition;

                        $this->runtimeNodes[] = $constFalse;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::LogicalNot) {
                        $logicNegation = new LogicalNegationOperator();
                        $logicNegation->content = LanguageKeywords::LogicalNot;
                        $logicNegation->startPosition = $startPosition;
                        $logicNegation->endPosition = $endPosition;

                        $this->runtimeNodes[] = $logicNegation;
                        continue;
                    } elseif ($parsedValue == LanguageKeywords::ArrList && $this->next == DocumentParser::LeftParen) {
                        $tupleListStart = new TupleListStart();
                        $tupleListStart->content = LanguageKeywords::ArrList;
                        $tupleListStart->startPosition = $startPosition;
                        $tupleListStart->endPosition = $endPosition;

                        $this->runtimeNodes[] = $tupleListStart;
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

                        $this->runtimeNodes[] = $numberNode;
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
                            continue;
                        } elseif ($lastValue instanceof ModifierValueSeparator) {
                            $modifierValueNode = new ModifierValueNode();
                            $modifierValueNode->name = $parsedValue;
                            $modifierValueNode->value = $parsedValue;
                            $modifierValueNode->startPosition = $startPosition;
                            $modifierValueNode->endPosition = $endPosition;
                            $this->runtimeNodes[] = $modifierValueNode;
                            $this->isParsingModifierName = false;
                            continue;
                        }
                    }

                    $variableRefNode = new VariableNode();
                    $variableRefNode->name = $parsedValue;
                    $variableRefNode->startPosition = $startPosition;
                    $variableRefNode->endPosition = $endPosition;
                    $this->runtimeNodes[] = $variableRefNode;

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
                    $scopeAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                    $scopeAssignment->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $scopeAssignment;
                    $this->currentContent = [];
                    $this->currentIndex += 1;

                    continue;
                }

                // _
                if ($this->cur == DocumentParser::Punctuation_Underscore && $this->next == DocumentParser::LeftParen) {
                    $tupleListStart = new TupleListStart();
                    $tupleListStart->content = DocumentParser::Punctuation_Underscore;
                    $tupleListStart->startPosition = $node->relativeOffset($this->currentIndex);
                    $tupleListStart->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $tupleListStart;
                    continue;
                }

                // ,
                if ($this->cur == DocumentParser::Punctuation_Comma) {
                    $argSeparator = new ArgSeparator();
                    $argSeparator->content = DocumentParser::Punctuation_Comma;
                    $argSeparator->startPosition = $node->relativeOffset($this->currentIndex);
                    $argSeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $argSeparator;
                    continue;
                }

                // ;
                if ($this->cur == DocumentParser::Punctuation_Semicolon) {
                    $statementSeparator = new StatementSeparatorNode();
                    $statementSeparator->content = DocumentParser::Punctuation_Semicolon;
                    $statementSeparator->startPosition = $node->relativeOffset($this->currentIndex);
                    $statementSeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $statementSeparator;
                    continue;
                }

                // +
                if ($this->cur == DocumentParser::Punctuation_Plus) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $additionAssignment = new AdditionAssignmentOperator();
                        $additionAssignment->content = '+=';
                        $additionAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                        $additionAssignment->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $additionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $additionOperator = new AdditionOperator();
                    $additionOperator->content = '+';
                    $additionOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $additionOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $additionOperator;
                    continue;
                }

                // -
                if ($this->isParsingModifierName == false && $this->cur == DocumentParser::Punctuation_Minus) {
                    if (ctype_digit($this->next) && (
                            ctype_digit($this->prev) == false &&
                            $this->prev != DocumentParser::RightParent) && $this->isRightOfInterpolationRegion() == false) {
                        $this->currentContent[] = $this->cur;
                        continue;
                    }

                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $subtractionAssignment = new SubtractionAssignmentOperator();
                        $subtractionAssignment->content = '-=';
                        $subtractionAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                        $subtractionAssignment->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $subtractionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    } elseif ($this->next == DocumentParser::Punctuation_GreaterThan) {
                        $methodInvocation = new MethodInvocationNode();
                        $methodInvocation->content = '->';
                        $methodInvocation->startPosition = $node->relativeOffset($this->currentIndex);
                        $methodInvocation->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $methodInvocation;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $subtractionOperator = new SubtractionOperator();
                    $subtractionOperator->content = '-';
                    $subtractionOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $subtractionOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $subtractionOperator;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Asterisk) {
                    // **
                    if ($this->next == DocumentParser::Punctuation_Asterisk) {
                        $exponentiationOperator = new ExponentiationOperator();
                        $exponentiationOperator->content = '**';
                        $exponentiationOperator->startPosition = $node->relativeOffset($this->currentIndex);
                        $exponentiationOperator->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $exponentiationOperator;
                        $this->currentIndex += 1;
                        continue;
                    } elseif ($this->next == DocumentParser::Punctuation_Equals) {
                        $multiplicationAssignment = new MultiplicationAssignmentOperator();
                        $multiplicationAssignment->content = '*=';
                        $multiplicationAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                        $multiplicationAssignment->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $multiplicationAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // *
                    $multiplicationOperator = new MultiplicationOperator();
                    $multiplicationOperator->content = '*';
                    $multiplicationOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $multiplicationOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $multiplicationOperator;
                    continue;
                }

                // /
                if ($this->cur == DocumentParser::Punctuation_ForwardSlash) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $divisionAssignment = new DivisionAssignmentOperator();
                        $divisionAssignment->content = '/=';
                        $divisionAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                        $divisionAssignment->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $divisionAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $divisionOperator = new DivisionOperator();
                    $divisionOperator->content = '/';
                    $divisionOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $divisionOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $divisionOperator;
                    continue;
                }

                // %
                if ($this->cur == DocumentParser::Punctuation_Percent) {
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $modulusAssignment = new ModulusAssignmentOperator();
                        $modulusAssignment->content = '%=';
                        $modulusAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                        $modulusAssignment->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $modulusAssignment;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $modulusOperator = new ModulusOperator();
                    $modulusOperator->content = '%';
                    $modulusOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $modulusOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $modulusOperator;
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
                            $spaceshipOperator->startPosition = $node->relativeOffset($this->currentIndex);
                            $spaceshipOperator->endPosition = $node->relativeOffset($this->currentIndex + 3);

                            $this->runtimeNodes[] = $spaceshipOperator;
                            $this->currentIndex += 2;
                            continue;
                        }

                        // <=
                        $lessThanEqual = new LessThanEqualCompOperator();
                        $lessThanEqual->content = '<=';
                        $lessThanEqual->startPosition = $node->relativeOffset($this->currentIndex);
                        $lessThanEqual->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $lessThanEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // <
                    $lessThan = new LessThanCompOperator();
                    $lessThan->content = '<';
                    $lessThan->startPosition = $node->relativeOffset($this->currentIndex);
                    $lessThan->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $lessThan;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_GreaterThan) {
                    // >=
                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $greaterThanEqual = new GreaterThanEqualCompOperator();
                        $greaterThanEqual->content = '>=';
                        $greaterThanEqual->startPosition = $node->relativeOffset($this->currentIndex);
                        $greaterThanEqual->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $greaterThanEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $greaterThan = new GreaterThanCompOperator();
                    $greaterThan->content = '>';
                    $greaterThan->startPosition = $node->relativeOffset($this->currentIndex);
                    $greaterThan->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $greaterThan;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Equals && $this->next != DocumentParser::Punctuation_Equals) {
                    $leftAssignment = new LeftAssignmentOperator();
                    $leftAssignment->content = '=';
                    $leftAssignment->startPosition = $node->relativeOffset($this->currentIndex);
                    $leftAssignment->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $leftAssignment;
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
                        $strictEqual->startPosition = $node->relativeOffset($this->currentIndex);
                        $strictEqual->endPosition = $node->relativeOffset($this->currentIndex + 3);

                        $this->runtimeNodes[] = $strictEqual;
                        $this->currentIndex += 2;
                    } else {
                        // ==
                        $equalOperator = new EqualCompOperator();
                        $equalOperator->content = '==';
                        $equalOperator->startPosition = $node->relativeOffset($this->currentIndex);
                        $equalOperator->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $equalOperator;
                        $this->currentIndex += 1;
                    }
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Ampersand) {
                    // &&
                    if ($this->next == DocumentParser::Punctuation_Ampersand) {
                        $logicalAnd = new LogicalAndOperator();
                        $logicalAnd->content = '&&';
                        $logicalAnd->startPosition = $node->relativeOffset($this->currentIndex);
                        $logicalAnd->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $logicalAnd;
                        $this->currentIndex += 1;
                        continue;
                    }

                    if ($this->next == DocumentParser::Punctuation_Equals) {
                        $concatOperator = new StringConcatenationOperator();
                        $concatOperator->content = '&=';
                        $concatOperator->startPosition = $node->relativeOffset($this->currentIndex);
                        $concatOperator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                        $this->runtimeNodes[] = $concatOperator;
                        $this->currentIndex += 1;
                        continue;
                    }

                    $logicalAnd = new LogicalAndOperator();
                    $logicalAnd->content = '&';
                    $logicalAnd->startPosition = $node->relativeOffset($this->currentIndex);
                    $logicalAnd->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalAnd;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Pipe && $this->next != DocumentParser::Punctuation_Pipe) {
                    $modifierSeparator = new ModifierSeparator();
                    $modifierSeparator->content = '|';
                    $modifierSeparator->startPosition = $node->relativeOffset($this->currentIndex);
                    $modifierSeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $modifierSeparator;

                    $this->isParsingModifierName = true;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Pipe && $this->next == DocumentParser::Punctuation_Pipe) {
                    // ||
                    $logicalOr = new LogicalOrOperator();
                    $logicalOr->content = '||';
                    $logicalOr->startPosition = $node->relativeOffset($this->currentIndex);
                    $logicalOr->endPosition = $node->relativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $logicalOr;
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
                            // !===
                            $strictNotEqual = new NotStrictEqualCompOperator();
                            $strictNotEqual->content = '!==';
                            $strictNotEqual->startPosition = $node->relativeOffset($this->currentIndex);
                            $strictNotEqual->endPosition = $node->relativeOffset($this->currentIndex + 3);

                            $this->runtimeNodes[] = $strictNotEqual;
                            $this->currentIndex += 2;
                            continue;
                        }

                        // !=
                        $notEqual = new NotEqualCompOperator();
                        $notEqual->content = '!=';
                        $notEqual->startPosition = $node->relativeOffset($this->currentIndex);
                        $notEqual->endPosition = $node->relativeOffset($this->currentIndex + 2);

                        $this->runtimeNodes[] = $notEqual;
                        $this->currentIndex += 1;
                        continue;
                    }

                    // !
                    $logicalNot = new LogicalNegationOperator();
                    $logicalNot->content = '!';
                    $logicalNot->startPosition = $node->relativeOffset($this->currentIndex);
                    $logicalNot->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalNot;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Equals) {
                    // ?=
                    $conditionalFallback = new ConditionalVariableFallbackOperator();
                    $conditionalFallback->content = '?=';
                    $conditionalFallback->startPosition = $node->relativeOffset($this->currentIndex);
                    $conditionalFallback->endPosition = $node->relativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $conditionalFallback;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Question) {
                    // ??
                    $nullCoalesceOperator = new NullCoalesceOperator();
                    $nullCoalesceOperator->content = '??';
                    $nullCoalesceOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $nullCoalesceOperator->endPosition = $node->relativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $nullCoalesceOperator;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question && $this->next == DocumentParser::Punctuation_Colon) {
                    // ?:
                    $nullCoalesceOperator = new NullCoalesceOperator();
                    $nullCoalesceOperator->content = '?:';
                    $nullCoalesceOperator->startPosition = $node->relativeOffset($this->currentIndex);
                    $nullCoalesceOperator->endPosition = $node->relativeOffset($this->currentIndex + 2);

                    $this->runtimeNodes[] = $nullCoalesceOperator;
                    $this->currentIndex += 1;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Question) {
                    // ?
                    $ternarySeparator = new InlineTernarySeparator();
                    $ternarySeparator->content = '?';
                    $ternarySeparator->startPosition = $node->relativeOffset($this->currentIndex);
                    $ternarySeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $ternarySeparator;
                    continue;
                }

                if ($this->cur == DocumentParser::LeftParen) {
                    $logicalGroupBegin = new LogicalGroupBegin();
                    $logicalGroupBegin->content = '(';
                    $logicalGroupBegin->startPosition = $node->relativeOffset($this->currentIndex);
                    $logicalGroupBegin->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalGroupBegin;
                    continue;
                }

                if ($this->cur == DocumentParser::RightParent) {
                    $logicalGroupEnd = new LogicalGroupEnd();
                    $logicalGroupEnd->content = ')';
                    $logicalGroupEnd->startPosition = $node->relativeOffset($this->currentIndex);
                    $logicalGroupEnd->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $logicalGroupEnd;
                    continue;
                }

                if ($this->cur == DocumentParser::Punctuation_Colon) {
                    if (count($this->runtimeNodes)) {
                        $lastItem = $this->runtimeNodes[count($this->runtimeNodes) - 1];

                        if ($lastItem instanceof ModifierNameNode) {
                            $modifierValueSeparator = new ModifierValueSeparator();
                            $modifierValueSeparator->content = ':';
                            $modifierValueSeparator->startPosition = $node->relativeOffset($this->currentIndex);
                            $modifierValueSeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                            $this->runtimeNodes[] = $modifierValueSeparator;

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
                    $branchSeparator->startPosition = $node->relativeOffset($this->currentIndex);
                    $branchSeparator->endPosition = $node->relativeOffset($this->currentIndex + 1);

                    $this->runtimeNodes[] = $branchSeparator;
                    $this->isParsingModifierName = false;
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
