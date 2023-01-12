<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Errors\LineRetriever;
use Statamic\View\Antlers\Language\Errors\TypeLabeler;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\ArgumentGroup;
use Statamic\View\Antlers\Language\Nodes\AssignmentOperatorNodeContract;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\MethodInvocationNode;
use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierChainNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierNode;
use Statamic\View\Antlers\Language\Nodes\ModifierValueNode;
use Statamic\View\Antlers\Language\Nodes\NamedArgumentNode;
use Statamic\View\Antlers\Language\Nodes\NameValueNode;
use Statamic\View\Antlers\Language\Nodes\NumberNode;
use Statamic\View\Antlers\Language\Nodes\OperatorNodeContract;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\AdditionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\DivisionOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\ExponentiationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic\FactorialOperator;
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
use Statamic\View\Antlers\Language\Nodes\Operators\LanguageOperatorConstruct;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalAndOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalNegationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalXorOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\NullCoalesceOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\ScopeAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\StringConcatenationOperator;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\AliasedScopeLogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ArgSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ArrayNode;
use Statamic\View\Antlers\Language\Nodes\Structures\DirectionGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\FieldsNode;
use Statamic\View\Antlers\Language\Nodes\Structures\GroupByField;
use Statamic\View\Antlers\Language\Nodes\Structures\ImplicitArrayBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\ImplicitArrayEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineBranchSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\InlineTernarySeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ListValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupBegin;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\ModifierValueSeparator;
use Statamic\View\Antlers\Language\Nodes\Structures\NullCoalescenceGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ScopedLogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\StatementSeparatorNode;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchCase;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\TernaryCondition;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleListStart;
use Statamic\View\Antlers\Language\Nodes\Structures\ValueDirectionNode;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Runtime\Sandbox\LanguageOperatorRegistry;
use Statamic\View\Antlers\Language\Utilities\NodeHelpers;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class LanguageParser
{
    /**
     * @var PathParser
     */
    private $pathParser = null;

    protected $tokens = [];

    private $isRoot = true;
    private $createdMethods = false;

    public function __construct()
    {
        $this->pathParser = new PathParser();
    }

    public function parse($tokens)
    {
        $this->tokens = [];

        $this->tokens = $this->combineVariablePaths($tokens);

        $this->tokens = $this->rewriteImplicitArraysToKeywordForm($this->tokens);

        $this->tokens = $this->createLanguageOperators($this->tokens);

        $this->tokens = $this->createLogicalGroups($this->tokens);
        $this->tokens = $this->associateMethodCalls($this->tokens);
        $this->tokens = $this->createTupleLists($this->tokens);

        $this->tokens = $this->createOperatorInvocations($this->tokens);
        $this->tokens = $this->createLogicGroupsAroundMethodCalls($this->tokens);
        $this->tokens = $this->associateModifiers($this->tokens);
        $this->tokens = $this->createNullCoalescenceGroups($this->tokens);

        // With modifiers and variables accounted for, this will
        // rewrite some tokens to a more "correct" type based
        // on the now more well-known context.
        $this->tokens = $this->correctTypes($this->tokens);

        $this->tokens = $this->createTernaryGroups($this->tokens);
        $this->tokens = $this->applyOperationOrder($this->tokens);
        $this->validateNeighboringOperators($this->tokens);

        $this->tokens = $this->createLogicGroupsToRemoveMethodInvocationAmbiguity($this->tokens);
        $this->tokens = $this->createLogicGroupsToResolveOperatorAmbiguity($this->tokens);
        $this->validateNoDanglingLogicGroupEnds($this->tokens);

        if ($this->isRoot) {
            $this->tokens = $this->insertAutomaticStatementSeparators($this->tokens);
        }

        return $this->createSemanticGroups($this->tokens);
    }

    public function setIsRoot($isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    private function rewriteImplicitArraysToKeywordForm($nodes)
    {
        $newNodes = [];
        $nodeLen = count($nodes);

        for ($i = 0; $i < $nodeLen; $i++) {
            $thisNode = $nodes[$i];

            if ($thisNode instanceof ImplicitArrayBegin) {
                // Emit the "arr" language operator followed by a LogicGroupBegin.
                $arrConstruct = new LanguageOperatorConstruct();
                $arrConstruct->startPosition = $thisNode->startPosition;
                $arrConstruct->endPosition = $thisNode->endPosition;
                $arrConstruct->originalAbstractNode = $thisNode;
                $arrConstruct->content = LanguageOperatorRegistry::ARR_MAKE;

                $logicGroupBegin = new LogicGroupBegin();
                $newNodes[] = $arrConstruct;
                $newNodes[] = $logicGroupBegin;
                continue;
            } elseif ($thisNode instanceof ImplicitArrayEnd) {
                $logicGroupEnd = new LogicGroupEnd();
                $newNodes[] = $logicGroupEnd;
                continue;
            } else {
                $newNodes[] = $thisNode;
            }
        }

        return $newNodes;
    }

    private function createLogicGroupsToRemoveMethodInvocationAmbiguity($nodes)
    {
        $newNodes = [];
        $nodeLen = count($nodes);
        $lastNodeIndex = $nodeLen - 1;

        for ($i = 0; $i < $nodeLen; $i++) {
            $thisNode = $nodes[$i];

            if ($thisNode instanceof MethodInvocationNode) {
                /** @var AbstractNode $lastNode */
                $lastNode = array_pop($newNodes);

                $wrapperGroup = new LogicGroup();
                $wrapperGroup->startPosition = $lastNode->startPosition;
                $wrapperGroup->nodes[] = $lastNode;
                $wrapperGroup->nodes[] = $thisNode;

                $doBreak = false;

                if ($i != $lastNodeIndex) {
                    for ($j = $i + 1; $j < $nodeLen; $j++) {
                        if ($nodes[$j] instanceof MethodInvocationNode) {
                            $wrapperGroup->nodes[] = $nodes[$j];

                            if ($j == $lastNodeIndex) {
                                $doBreak = true;
                                break;
                            }
                        } else {
                            if ($j == $lastNodeIndex) {
                                $doBreak = true;
                                break;
                            }

                            $i = $j - 1;
                            break;
                        }
                    }
                }

                $wrapperGroup->endPosition = $wrapperGroup->nodes[count($wrapperGroup->nodes) - 1]->endPosition;

                $newNodes[] = $wrapperGroup;

                if ($doBreak) {
                    break;
                }
            } else {
                $newNodes[] = $thisNode;
            }
        }

        return $newNodes;
    }

    private function reduceVariableReferenceForObjectAccessor(VariableNode $node)
    {
        $lastPath = array_pop($node->variableReference->pathParts);
        $accessor = $lastPath->name;
        $accessorLen = mb_strlen($accessor) + 1;

        $node->name = StringUtilities::substr($node->name, 0, -$accessorLen);
        $node->variableReference->originalContent = StringUtilities::substr($node->variableReference->originalContent, 0, -$accessorLen);
        $node->variableReference->normalizedReference = StringUtilities::substr($node->variableReference->normalizedReference, 0, -$accessorLen);

        $node->variableReference->pathParts[count($node->variableReference->pathParts) - 1]->isFinal = true;

        $node->endPosition->offset -= $accessorLen;
        $node->endPosition->char -= $accessorLen;

        return [$accessor, $accessorLen];
    }

    private function cleanVariableForMethodInvocation(VariableNode $node)
    {
        if (Str::startsWith($node->name, [':', '.'])) {
            $node->name = StringUtilities::substr($node->name, 1);
            $node->variableReference->originalContent = StringUtilities::substr($node->variableReference->originalContent, 1);
            $node->variableReference->normalizedReference = StringUtilities::substr($node->variableReference->normalizedReference, 1);

            $node->startPosition->offset += 1;
            $node->startPosition->char += 1;
        }
    }

    private function convertVariableToNumericNode(VariableNode $node)
    {
        $numericNode = new NumberNode();
        $numericNode->startPosition = $node->startPosition;
        $numericNode->endPosition = $node->endPosition;
        $numericNode->content = $node->name;
        $numericNode->refId = $node->refId;
        $numericNode->index = $node->index;
        $numericNode->originalAbstractNode = $node;

        if (Str::contains($node->name, '.')) {
            $numericNode->value = floatval($node->name);
        } else {
            $numericNode->value = intval($node->name);
        }

        return $numericNode;
    }

    public function associateMethodCalls($tokens)
    {
        $nodeCount = count($tokens);
        $newTokens = [];

        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $tokens[$i];
            $prevNode = null;
            $next = null;

            if (! empty($newTokens)) {
                $prevNode = $newTokens[count($newTokens) - 1];
            }

            if ($i + 1 < $nodeCount) {
                $next = $tokens[$i + 1];
            }

            if ($thisNode instanceof VariableNode &&
                $next instanceof LogicGroup && $prevNode != null &&
                ($this->isProperMethodChainTargetStrict($prevNode) || $prevNode instanceof InlineBranchSeparator)) {
                $argGroup = $next;

                $argNodes = [];

                if (! empty($argGroup->nodes)) {
                    if ($argGroup->nodes[0] instanceof SemanticGroup) {
                        $argNodes = $argGroup->nodes[0]->nodes;
                    } else {
                        $argNodes = $argGroup->nodes;
                    }
                }

                $methodInvocation = new MethodInvocationNode();
                $methodInvocation->startPosition = $thisNode->startPosition;

                if ($prevNode instanceof InlineBranchSeparator) {
                    /** @var InlineBranchSeparator $branchSeparator */
                    $branchSeparator = array_pop($newTokens);
                    $methodInvocation->startPosition = $branchSeparator->startPosition;
                }

                $this->cleanVariableForMethodInvocation($thisNode);

                $methodInvocation->endPosition = $argGroup->endPosition;
                $methodInvocation->args = $this->makeArgGroup($argNodes);
                $methodInvocation->method = $thisNode;

                $newTokens[] = $methodInvocation;
                $this->createdMethods = true;

                $i += 1;
                continue;
            } elseif ($thisNode instanceof LogicGroup && $prevNode instanceof VariableNode && $prevNode->variableReference != null &&
                count($prevNode->variableReference->pathParts) >= 2) {
                $argNodes = [];

                if (! empty($thisNode->nodes)) {
                    if ($thisNode->nodes[0] instanceof SemanticGroup) {
                        $argNodes = $thisNode->nodes[0]->nodes;
                    } else {
                        $argNodes = $thisNode->nodes;
                    }
                }

                $reductionResult = $this->reduceVariableReferenceForObjectAccessor($prevNode);
                // Construct a dynamic MethodInvocationNode.
                $methodInvocation = new MethodInvocationNode();
                $methodInvocation->startPosition = $thisNode->startPosition;
                $methodInvocation->endPosition = $thisNode->endPosition;

                $wrappedMethod = new VariableNode();
                $wrappedMethod->name = $reductionResult[0];

                $methodInvocation->startPosition->offset -= $reductionResult[1];
                $methodInvocation->startPosition->char -= $reductionResult[1];
                $methodInvocation->method = $wrappedMethod;
                $args = $this->makeArgGroup($argNodes);
                $methodInvocation->args = $args;

                if (is_numeric($prevNode->name)) {
                    /** @var VariableNode $varNode */
                    $varNode = array_pop($newTokens);
                    $newTokens[] = $this->convertVariableToNumericNode($varNode);
                }

                $newTokens[] = $methodInvocation;
                $this->createdMethods = true;

                continue;
            } elseif ($thisNode instanceof VariableNode && $prevNode instanceof MethodInvocationNode) {
                $this->cleanVariableForMethodInvocation($thisNode);

                if ($i + 1 > $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_METHOD_CALL_MISSING_ARG_GROUP,
                        $thisNode,
                        'Unexpected end of input while parsing method call.'
                    );
                }

                $argGroup = $tokens[$i + 1];

                if (! $argGroup instanceof LogicGroup) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_METHOD_CALL_ARG_GROUP,
                        $thisNode,
                        'Unexpected ['.TypeLabeler::getPrettyTypeName($argGroup).'] while parsing [T_METHOD_CALL]; expecting [T_ARG_GROUP].'
                    );
                }

                $argNodes = [];

                if (! empty($argGroup->nodes)) {
                    if ($argGroup->nodes[0] instanceof SemanticGroup) {
                        $argNodes = $argGroup->nodes[0]->nodes;
                    } else {
                        $argNodes = $argGroup->nodes;
                    }
                }

                $methodInvocation = new MethodInvocationNode();
                $methodInvocation->startPosition = $thisNode->startPosition;
                $methodInvocation->endPosition = $argGroup->endPosition;
                $methodInvocation->args = $this->makeArgGroup($argNodes);
                $methodInvocation->method = $thisNode;

                $newTokens[] = $methodInvocation;
                $this->createdMethods = true;

                $i += 1;
                continue;
            } elseif ($thisNode instanceof InlineBranchSeparator && $prevNode instanceof MethodInvocationNode) {
                if ($i + 1 > $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_METHOD_CALL_MISSING_METHOD,
                        $thisNode,
                        'Unexpected end of input while parsing method call.'
                    );
                }

                $next = $tokens[$i + 1];

                if ($i + 2 > $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_METHOD_CALL_MISSING_ARG_GROUP,
                        $thisNode,
                        'Unexpected end of input while parsing method call.'
                    );
                }

                /** @var LogicGroup $argGroup */
                $argGroup = $tokens[$i + 2];

                if (! $argGroup instanceof LogicGroup) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_METHOD_CALL_ARG_GROUP,
                        $thisNode,
                        'Unexpected ['.TypeLabeler::getPrettyTypeName($argGroup).'] while parsing [T_METHOD_CALL]; expecting [T_ARG_GROUP].'
                    );
                }

                $argNodes = [];

                if (! empty($argGroup->nodes)) {
                    if ($argGroup->nodes[0] instanceof SemanticGroup) {
                        $argNodes = $argGroup->nodes[0]->nodes;
                    } else {
                        $argNodes = $argGroup->nodes;
                    }
                }

                $methodInvocation = new MethodInvocationNode();
                $methodInvocation->startPosition = $thisNode->startPosition;
                $methodInvocation->endPosition = $argGroup->endPosition;
                $methodInvocation->args = $this->makeArgGroup($argNodes);
                $methodInvocation->method = $next;

                $newTokens[] = $methodInvocation;
                $this->createdMethods = true;

                $i += 2;
                continue;
            } elseif ($thisNode instanceof MethodInvocationNode) {
                if ($i + 1 > $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_METHOD_CALL_MISSING_METHOD,
                        $thisNode,
                        'Unexpected end of input while parsing method call.'
                    );
                }

                /** @var VariableNode $methodNode */
                $methodNode = $tokens[$i + 1];

                if ($i + 2 > $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_METHOD_CALL_ARG_GROUP,
                        $thisNode,
                        'Unexpected end of input while parsing method call.'
                    );
                }

                /** @var LogicGroup $next */
                $next = $tokens[$i + 2];

                if (! $next instanceof LogicGroup) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_METHOD_CALL_ARG_GROUP,
                        $thisNode,
                        'Unexpected ['.TypeLabeler::getPrettyTypeName($next).'] while parsing [T_METHOD_CALL]; expecting [T_ARG_GROUP].'
                    );
                }

                $argNodes = [];

                if (! empty($next->nodes)) {
                    if ($next->nodes[0] instanceof SemanticGroup) {
                        $argNodes = $next->nodes[0]->nodes;
                    } else {
                        $argNodes = $next->nodes;
                    }
                }

                $args = $this->makeArgGroup($argNodes);

                $thisNode->method = $methodNode;

                $thisNode->args = $args;
                $i += 2;
                $newTokens[] = $thisNode;

                continue;
            } else {
                $newTokens[] = $thisNode;
            }
        }

        return $newTokens;
    }

    private function createLanguageOperators($tokens)
    {
        // We need to run through the nodes and check what appears after
        // all of the language operators so we can be sure it really
        // is a language operator. They should be followed by a
        // variable or logic grouping (or an interpolation).
        $nodeCount = count($tokens);
        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $tokens[$i];

            if ($thisNode instanceof VariableNode) {
                if (Str::contains($thisNode->name, DocumentParser::Punctuation_FullStop)) {
                    $checkParts = explode(DocumentParser::Punctuation_FullStop, $thisNode->name);

                    if (array_key_exists($checkParts[0], LanguageOperatorRegistry::$operators)) {
                        $tokens[$i] = $this->convertVarNodeToOperator($thisNode);
                        continue;
                    }
                }

                if (array_key_exists($thisNode->name, LanguageOperatorRegistry::$operators)) {
                    $tokens[$i] = $this->convertVarNodeToOperator($thisNode);
                    continue;
                }
            }

            if ($thisNode instanceof LanguageOperatorConstruct) {
                if ($i + 1 >= $nodeCount) {
                    // Convert it into a variable type node.
                    $tokens[$i] = $this->convertOperatorToVarNode($thisNode);
                    continue;
                }

                $next = $tokens[$i + 1];

                if ($next instanceof StatementSeparatorNode) {
                    // Convert it into a variable type node.
                    $tokens[$i] = $this->convertOperatorToVarNode($thisNode);
                    continue;
                }
            }
        }

        return $tokens;
    }

    private function convertVarNodeToOperator(VariableNode $variable)
    {
        $operator = new LanguageOperatorConstruct();
        $operator->content = $variable->name;
        $operator->startPosition = $variable->startPosition;
        $operator->endPosition = $variable->endPosition;
        $operator->originalAbstractNode = $variable;

        return $operator;
    }

    private function convertOperatorToVarNode(LanguageOperatorConstruct $operator)
    {
        $varNodeWrap = new VariableNode();
        $varNodeWrap->startPosition = $operator->startPosition;
        $varNodeWrap->endPosition = $operator->endPosition;
        $varNodeWrap->content = $operator->content;
        $varNodeWrap->name = $operator->content;
        $varNodeWrap->originalAbstractNode = $operator;

        return $varNodeWrap;
    }

    private function createTupleLists($tokens)
    {
        $tokenCount = count($tokens);
        $newTokens = [];

        for ($i = 0; $i < $tokenCount; $i++) {
            $thisToken = $tokens[$i];

            if ($thisToken instanceof TupleListStart) {
                if ($i + 1 >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_PARSING_TUPLE_LIST,
                        $thisToken,
                        'Unexpected end of input while parsing tuple list.'
                    );
                }

                $peek = $tokens[$i + 1];

                if ($peek instanceof LogicGroup == false) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_TYPE_FOR_TUPLE_LIST,
                        $peek,
                        'Unexpected ['.TypeLabeler::getPrettyTypeName($peek).'] while parsing tuple list.'
                    );
                }

                // Each value in the next T_LOGIC_GROUP
                // should be a semantic group instance.
                // The first semantic group will have
                // the tuple list's variable names.
                $listNodeLength = count($peek->nodes);

                if ($listNodeLength == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_MISSING_BODY_TUPLE_LIST,
                        $peek,
                        'Missing tuple list body while parsing tuple list.'
                    );
                }

                if ($peek->nodes[0] instanceof SemanticGroup == false) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_MISSING_NAMES_TUPLE_LIST,
                        $peek,
                        'Missing name expression while parsing tuple list.'
                    );
                }

                /** @var SemanticGroup $nameGroup */
                $nameGroup = array_shift($peek->nodes);
                $targetGroupLength = count($nameGroup->nodes);

                $listNames = [];

                for ($j = 0; $j < $targetGroupLength; $j++) {
                    $subNode = $nameGroup->nodes[$j];

                    if ($subNode instanceof ArgSeparator == false) {
                        if ($subNode instanceof VariableNode == false || count($subNode->variableReference->pathParts) > 1 ||
                            strlen(trim($subNode->name)) == 0) {
                            throw ErrorFactory::makeSyntaxError(
                                AntlersErrorCodes::TYPE_INVALID_TUPLE_LIST_NAME_TYPE,
                                $peek,
                                'Invalid ['.TypeLabeler::getPrettyTypeName($subNode).'] name type found while parsing tuple list.'
                            );
                        }

                        $listNames[] = $this->convertVariableToStringNode($subNode);
                    }
                }

                $listValueLength = count($listNames);

                if ($listValueLength == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_MANIFESTED_NAME_GROUP,
                        $peek,
                        'Invalid Name expression produced an invalid name group while parsing tuple list.'
                    );
                }

                // We will simply convert the tuple list syntax into an array node under the hood.
                $arrayNode = new ArrayNode();
                $arrayNode->startPosition = $thisToken->startPosition;
                $arrayNode->endPosition = $thisToken->startPosition;

                foreach ($peek->nodes as $valueNodeCandidate) {
                    if (! $valueNodeCandidate instanceof SemanticGroup) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_INVALID_TUPLE_LIST_VALUE_TYPE_GROUP,
                            $peek,
                            'Invalid ['.TypeLabeler::getPrettyTypeName($valueNodeCandidate).'] name type found while parsing tuple list value expression.'
                        );
                    }

                    if (count($valueNodeCandidate->nodes) != $targetGroupLength) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_VALUE_NAME_LENGTH_MISMATCH_TUPLE_LIST,
                            $peek,
                            'Value expression group length does not match name expression group length.'
                        );
                    }

                    $nestedArrayNode = new ArrayNode();
                    $nestedArrayNode->startPosition = $valueNodeCandidate->startPosition;

                    $valueCandidates = [];

                    for ($j = 0; $j < $targetGroupLength; $j++) {
                        $valueToken = $valueNodeCandidate->nodes[$j];

                        if (! $valueToken instanceof ArgSeparator) {
                            if (! $this->isOperand($valueToken)) {
                                throw ErrorFactory::makeSyntaxError(
                                    AntlersErrorCodes::TYPE_INVALID_TUPLE_LIST_VALUE_TYPE,
                                    $thisToken,
                                    'Unexpected ['.TypeLabeler::getPrettyTypeName($valueToken).']  while parsing tuple list value.'
                                );
                            }

                            $valueCandidates[] = $valueToken;
                        }
                    }

                    for ($j = 0; $j < $listValueLength; $j++) {
                        $valueToken = $valueCandidates[$j];

                        $namedValueNode = new NameValueNode();
                        $namedValueNode->startPosition = $valueToken->startPosition;
                        $namedValueNode->endPosition = $valueToken->endPosition;
                        $namedValueNode->name = $listNames[$j];
                        $namedValueNode->value = $valueToken;

                        $nestedArrayNode->nodes[] = $namedValueNode;
                        $arrayNode->endPosition = $valueToken->endPosition;
                        $nestedArrayNode->endPosition = $valueToken->endPosition;
                    }

                    $arrayNode->nodes[] = $nestedArrayNode;
                }

                $newTokens[] = $arrayNode;
                $i += 1;
                continue;
            } else {
                $newTokens[] = $thisToken;
            }
        }

        return $newTokens;
    }

    private function convertVariableToStringNode(VariableNode $node)
    {
        $wrappedNode = new StringValueNode();
        $wrappedNode->content = $node->name;
        $wrappedNode->value = $node->name;
        $wrappedNode->originalAbstractNode = $node;
        $wrappedNode->startPosition = $node->startPosition;
        $wrappedNode->endPosition = $node->endPosition;
        $wrappedNode->index = $node->index;

        return $wrappedNode;
    }

    private function validateNeighboringOperators($tokens)
    {
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $thisToken = $tokens[$i];

            if ($this->isOperatorType($thisToken)) {
                if ($i + 1 >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_OPERATOR,
                        $thisToken,
                        'Unexpected operator while parsing input text.'
                    );
                }

                $peek = $tokens[$i + 1];

                if ($this->isOperatorType($peek)) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_OPERATOR_INVALID_ON_RIGHT,
                        $thisToken,
                        'Unexpected operator while parsing input text.'
                    );
                }
            }
        }
    }

    private function isAssignmentOperator($token)
    {
        if ($token instanceof AdditionAssignmentOperator ||
            $token instanceof DivisionAssignmentOperator || $token instanceof LeftAssignmentOperator ||
            $token instanceof ModulusAssignmentOperator || $token instanceof MultiplicationAssignmentOperator ||
            $token instanceof SubtractionAssignmentOperator) {
            return true;
        }

        return false;
    }

    private function isOperatorType($token)
    {
        if ($token instanceof OperatorNodeContract || $token instanceof AdditionAssignmentOperator ||
            $token instanceof DivisionAssignmentOperator || $token instanceof LeftAssignmentOperator ||
            $token instanceof ModulusAssignmentOperator || $token instanceof MultiplicationAssignmentOperator ||
            $token instanceof SubtractionAssignmentOperator) {
            return true;
        }

        return false;
    }

    private function createOperatorInvocations($tokens)
    {
        $newTokens = [];

        $tokenCount = count($tokens);
        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $tokens[$i];

            if ($token instanceof LanguageOperatorConstruct) {
                if ($token->content == LanguageOperatorRegistry::ARR_ORDERBY) {
                    if ($i + 1 >= $tokenCount) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EOI_PARSING_ORDER_GROUP,
                            $token,
                            'Unexpected end of input while parsing order group.'
                        );
                    }

                    $nextToken = $tokens[$i + 1];

                    if ($nextToken instanceof LogicGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_EXPECTING_ORDER_GROUP_FOR_ORDER_BY_OPERAND,
                            $token,
                            'Unexpected ['.TypeLabeler::getPrettyTypeName($nextToken).'] while parsing order group.'
                        );
                    }

                    $subNodes = $nextToken->nodes;

                    if (count($subNodes) == 0) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EMPTY_DIRECTION_GROUP,
                            $nextToken,
                            'Unexpected empty [T_DIRECTION_GROUP]. Must have at least one order clause, and each property must have a direction specified.'
                        );
                    }

                    if ($subNodes[0] instanceof SemanticGroup) {
                        $subNodes = $subNodes[0]->nodes;
                    }

                    $orderClauses = $this->makeOrderGroup($subNodes);

                    if (count($orderClauses) == 0) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EMPTY_DIRECTION_GROUP,
                            $token,
                            'Unexpected empty [T_DIRECTION_GROUP]. Must have at least one order clause, and each property must have a direction specified.'
                        );
                    }

                    $orderGroup = new DirectionGroup();
                    $orderGroup->orderClauses = $orderClauses;
                    $orderGroup->startPosition = $orderClauses[0]->directionNode->startPosition;
                    $orderGroup->endPosition = $orderClauses[count($orderClauses) - 1]->directionNode->endPosition;

                    $newTokens[] = $token;
                    $newTokens[] = $orderGroup;
                    $i += 1;
                    continue;
                } elseif ($token->content == LanguageOperatorRegistry::ARR_GROUPBY) {
                    if ($i + 1 >= $tokenCount) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_GROUP_BY,
                            $token,
                            'Unexpected end of input while parsing group by clause.'
                        );
                    }

                    $nextToken = $tokens[$i + 1];

                    if ($nextToken instanceof AliasedScopeLogicGroup || $nextToken instanceof ScopedLogicGroup) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_GROUP_BY_SCOPED_GROUP_MUST_BE_ENCLOSED,
                            $token,
                            'Type ['.TypeLabeler::getPrettyTypeName($nextToken).'] must be enclosed with parenthesis to be used with groupby.'
                        );
                    }

                    if ($nextToken instanceof LogicGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_TOKEN_FOR_GROUP_BY,
                            $token,
                            'Unexpected ['.TypeLabeler::getPrettyTypeName($nextToken).'] while parsing group by.'
                        );
                    }

                    $subNodes = $nextToken->nodes;

                    if ($subNodes[0] instanceof SemanticGroup) {
                        $subNodes = $subNodes[0]->nodes;
                    }

                    $groupFields = $this->makeGroupByFields($subNodes);

                    $newTokens[] = $token;
                    $newTokens[] = $groupFields;

                    if ($i + 2 < $tokenCount && $i + 3 < $tokenCount) {
                        $peekOne = $tokens[$i + 2];
                        $peekTwo = $tokens[$i + 3];

                        if (NodeHelpers::isVariableMatching($peekOne, LanguageKeywords::ScopeAs)) {
                            if ($peekTwo instanceof StringValueNode) {
                                $groupFields->isNamedNode = true;
                                $groupFields->parsedName = $peekTwo;

                                $i += 3;
                                continue;
                            } else {
                                throw ErrorFactory::makeSyntaxError(
                                    AntlersErrorCodes::TYPE_UNEXPECTED_GROUP_BY_AS_ALIAS_TYPE,
                                    $token,
                                    'Expecting [T_STRING] for group by collection alias; got ['.TypeLabeler::getPrettyTypeName($peekTwo).'].'
                                );
                            }
                        }
                    }

                    $i += 1;
                    continue;
                } elseif ($token->content == LanguageOperatorRegistry::STRUCT_SWITCH) {
                    if ($i + 1 >= $tokenCount) {
                        if ($token->originalAbstractNode instanceof VariableNode == false) {
                            throw ErrorFactory::makeSyntaxError(
                                AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_SWITCH_GROUP,
                                $token,
                                'Unexpected end of input while parsing [T_SWITCH_GROUP].'
                            );
                        }

                        $newTokens[] = $token->originalAbstractNode;
                        continue;
                    }

                    /** @var ScopedLogicGroup $next */
                    $next = $tokens[$i + 1];

                    if ($next instanceof ScopedLogicGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_TOKEN_FOR_SWITCH_GROUP,
                            $token,
                            'Unexpected ['.TypeLabeler::getPrettyTypeName($next).'] while parsing [T_SWITCH_GROUP].'
                        );
                    }

                    if ($next->scope == null || $next->scope instanceof LogicGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_SWITCH_START_VALUE,
                            $token,
                            'Unexpected input while parsing [T_SWITCH_GROUP].'
                        );
                    }

                    if (empty($next->scope->nodes) || $next->scope->nodes[0] instanceof SemanticGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_VALUE,
                            $token,
                            'Unexpected input while parsing [T_SWITCH_GROUP].'
                        );
                    }

                    /** @var SemanticGroup $wrapperSemanticGroup */
                    $wrapperSemanticGroup = $next->scope->nodes[0];

                    if (empty($wrapperSemanticGroup->nodes) || $wrapperSemanticGroup->nodes[0] instanceof LogicGroup == false) {
                        $shouldError = true;

                        if (! empty($wrapperSemanticGroup->nodes)) {
                            $firstNode = $wrapperSemanticGroup->nodes[0];

                            if ($firstNode instanceof  ArrayNode && $firstNode->hasModifiers()) {
                                $shouldError = false;
                            } elseif ($firstNode instanceof VariableNode) {
                                $shouldError = false;
                            }
                        }

                        if ($shouldError) {
                            throw ErrorFactory::makeSyntaxError(
                                AntlersErrorCodes::TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_SEMANTIC_VALUE,
                                $token,
                                'Unexpected input while parsing [T_SWITCH_GROUP].'
                            );
                        }
                    }

                    $firstCondition = $wrapperSemanticGroup->nodes;

                    if (empty($next->nodes)) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_VALUE,
                            $token,
                            'Unexpected input while parsing [T_SWITCH_GROUP].'
                        );
                    }

                    $expressionNode = $next->nodes[0];

                    if ($expressionNode instanceof SemanticGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_SWITCH_START_VALUE_NO_VALUE,
                            $token,
                            'Unexpected input while parsing [T_SWITCH_GROUP].'
                        );
                    }

                    $subTokens = $expressionNode->nodes;

                    $switchGroup = new SwitchGroup();

                    $wrapperGroup = new LogicGroup();
                    $wrapperGroup->nodes = $firstCondition;
                    $wrapperGroup->startPosition = $wrapperGroup->nodes[0]->startPosition;
                    $wrapperGroup->endPosition = $wrapperGroup->nodes[count($wrapperGroup->nodes) - 1]->endPosition;

                    $firstCase = new SwitchCase();
                    $firstCase->condition = $wrapperGroup;

                    $expWrapper = new LogicGroup();
                    $expWrapper->nodes = [array_shift($subTokens)];
                    $expWrapper->startPosition = $expWrapper->nodes[0]->startPosition;
                    $expWrapper->endPosition = $expWrapper->nodes[count($expWrapper->nodes) - 1]->endPosition;

                    $firstCase->expression = $expWrapper;

                    $firstCase->startPosition = $firstCase->condition->startPosition;
                    $firstCase->endPosition = $firstCase->expression->nodes[count($firstCase->expression->nodes) - 1]->endPosition;

                    array_shift($subTokens);

                    $switchGroup->cases[] = $firstCase;
                    $subTokenCount = count($subTokens);

                    if ($subTokenCount > 0) {
                        if ($subTokens[0] instanceof LogicGroup == false) {
                            throw ErrorFactory::makeSyntaxError(
                                AntlersErrorCodes::TYPE_PARSER_INVALID_SWITCH_TOKEN,
                                $token,
                                'Invalid ['.TypeLabeler::getPrettyTypeName($subTokens[0]).'] while parsing case statement.'
                            );
                        }

                        for ($c = 0; $c < $subTokenCount; $c++) {
                            $thisToken = $subTokens[$c];
                            $next = null;

                            if ($thisToken instanceof ArgSeparator) {
                                continue;
                            }

                            if ($c + 1 < $subTokenCount) {
                                $next = $subTokens[$c + 1];
                            }

                            if ($next instanceof ScopeAssignmentOperator) {
                                $newCase = new SwitchCase();
                                $newCase->condition = $thisToken;

                                $expWrapper = new LogicGroup();
                                $expWrapper->nodes = [$subTokens[$c + 2]];
                                $expWrapper->startPosition = $expWrapper->nodes[0]->startPosition;
                                $expWrapper->endPosition = $expWrapper->nodes[count($expWrapper->nodes) - 1]->endPosition;

                                $newCase->expression = $expWrapper;

                                $newCase->startPosition = $newCase->condition->startPosition;
                                $newCase->endPosition = $newCase->expression->nodes[count($newCase->expression->nodes) - 1]->endPosition;

                                $switchGroup->cases[] = $newCase;

                                if ($c + 3 < $subTokenCount) {
                                    if ($subTokens[$c + 3] instanceof ArgSeparator == false) {
                                        throw ErrorFactory::makeSyntaxError(
                                            AntlersErrorCodes::TYPE_PARSER_INVALID_SWITCH_TOKEN,
                                            $subTokens[$c + 3],
                                            'Invalid ['.TypeLabeler::getPrettyTypeName($subTokens[$c + 3]).'] while parsing case statement; expecting [T_ARG_SEPARATOR].'
                                        );
                                    }
                                }

                                $c += 2;
                                continue;
                            }
                        }
                    }

                    $newTokens[] = new NullConstant();
                    $newTokens[] = $token;
                    $newTokens[] = $switchGroup;

                    $i += 1;
                    continue;
                } elseif ($token->content == LanguageOperatorRegistry::ARR_MAKE) {
                    if ($i + 1 >= $tokenCount) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ARR_MAKE_MISSING_TARGET,
                            $token,
                            'Missing target variable for arr operator.'
                        );
                    }

                    $nextToken = $tokens[$i + 1];

                    if ($nextToken instanceof LogicGroup == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ARR_MAKE_UNEXPECTED_TYPE,
                            $token,
                            'Unexpected ['.TypeLabeler::getPrettyTypeName($nextToken).'] while parsing array.'
                        );
                    }

                    $subNodes = $nextToken->nodes;

                    if (count($subNodes) > 0 && $subNodes[0] instanceof SemanticGroup) {
                        $subNodes = $subNodes[0]->nodes;
                    }

                    if ($nextToken instanceof  ScopedLogicGroup) {
                        array_unshift($subNodes, new ScopeAssignmentOperator());
                        array_unshift($subNodes, $nextToken->scope);
                    }

                    /** @var ListValueNode $values */
                    $values = $this->getArrayValues($subNodes);
                    $arrayNode = new ArrayNode();
                    $arrayNode->startPosition = $token->startPosition;

                    $valueNodeCount = count($values->values);

                    if ($valueNodeCount > 0) {
                        $arrayNode->endPosition = $values->values[$valueNodeCount - 1]->endPosition;
                    } else {
                        $arrayNode->endPosition = $token->endPosition;
                    }

                    $arrayNode->nodes = $values->values;

                    $newTokens[] = $arrayNode;

                    $i += 1;
                } else {
                    $newTokens[] = $token;
                }
            } else {
                $newTokens[] = $token;
            }
        }

        return $newTokens;
    }

    private function getArrayValues($nodes)
    {
        $valueNode = new ListValueNode();

        $nodeCount = count($nodes);
        $values = [];

        for ($i = 0; $i < $nodeCount; $i++) {
            /** @var AbstractNode $thisNode */
            $thisNode = $nodes[$i];

            if ($thisNode instanceof ArgSeparator) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_ARR_UNEXPECT_ARG_SEPARATOR,
                    $thisNode,
                    'Unexpected [T_ARG_SEPARATOR] while parsing array.'
                );
            }

            if ($thisNode instanceof ScopeAssignmentOperator) {
                if ($i == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_ARR_KEY_PAIR_MISSING_KEY,
                        $thisNode,
                        'Missing key for key/value pair while parsing array.'
                    );
                } else {
                    if ($nodes[$i - 1] instanceof ArgSeparator) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_ARR_KEY_PAIR_MISSING_KEY,
                            $thisNode,
                            'Missing key for key/value pair while parsing array.'
                        );
                    }
                }
            }

            $next = null;

            if ($i + 1 < $nodeCount) {
                $next = $nodes[$i + 1];
            }

            if ($next instanceof ScopeAssignmentOperator) {
                if ($i + 2 >= $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_ARR_MAKE_MISSING_ARR_KEY_PAIR_VALUE,
                        $next,
                        'Missing key/pair value while parsing array.'
                    );
                }

                /** @var AbstractNode $keyValue */
                $keyValue = $nodes[$i + 2];

                $namedValueNode = new NameValueNode();

                if (! $this->isValidArrayKeyNode($thisNode)) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_ARR_KEY_PAIR_INVALID_KEY_TYPE,
                        $thisNode,
                        'Invalid ['.TypeLabeler::getPrettyTypeName($thisNode).'] type for key/value key.'
                    );
                }

                $namedValueNode->name = $thisNode;
                $namedValueNode->value = $keyValue;
                $namedValueNode->startPosition = $thisNode->startPosition;
                $namedValueNode->endPosition = $keyValue->endPosition;

                $values[] = $namedValueNode;

                $i += 3;
                continue;
            }

            if ($next == null || $next instanceof ArgSeparator) {
                $namedValueNode = new NameValueNode();
                $namedValueNode->value = $thisNode;
                $namedValueNode->startPosition = $thisNode->startPosition;
                $namedValueNode->endPosition = $thisNode->endPosition;

                $values[] = $namedValueNode;

                $i += 1;
                continue;
            }
        }

        $valueNode->values = $values;

        return $valueNode;
    }

    /**
     * Tests if the node is a valid candidate for an array key.
     *
     * @param  AbstractNode  $node  The node to test.
     * @return bool
     */
    private function isValidArrayKeyNode($node)
    {
        if ($node instanceof NumberNode || $node instanceof StringValueNode) {
            return true;
        }

        return false;
    }

    private function getValues($nodes)
    {
        $valueNode = new ListValueNode();

        $nodeCount = count($nodes);
        $values = [];

        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $nodes[$i];

            $next = null;

            if ($i + 1 < $nodeCount) {
                $next = $nodes[$i + 1];
            }

            if ($next == null || $next instanceof ArgSeparator) {
                $values[] = $thisNode;
                $i += 1;
                continue;
            }
        }

        $valueNode->values = $values;

        return $valueNode;
    }

    private function makeGroupByFields($nodes)
    {
        $nodeCount = count($nodes);
        $fields = [];

        if ($nodeCount == 1 && $nodes[0] instanceof VariableNode) {
            $fieldNode = new GroupByField();
            $fieldNode->field = $nodes[0];

            $stringAlias = new StringValueNode();
            $stringAlias->value = $fieldNode->field->name;
            $stringAlias->startPosition = $fieldNode->field->startPosition;
            $stringAlias->endPosition = $fieldNode->field->endPosition;
            $fieldNode->alias = $stringAlias;

            $fields[] = $fieldNode;
        } else {
            for ($i = 0; $i < $nodeCount; $i++) {
                $thisNode = $nodes[$i];

                $next = null;

                if ($i + 1 < $nodeCount) {
                    $next = $nodes[$i + 1];
                }

                if ($next == null || $next instanceof ArgSeparator) {
                    $fieldNode = new GroupByField();
                    if ($i > 0) {
                        $fieldNode->field = $nodes[$i - 1];
                        $fieldNode->alias = $thisNode;
                    } else {
                        $fieldNode->field = $thisNode;
                        $fieldNode->alias = null;
                    }
                    if ($fieldNode->field instanceof ArgSeparator) {
                        $fieldNode->field = $fieldNode->alias;

                        if ($fieldNode->field instanceof VariableNode) {
                            $stringAlias = new StringValueNode();
                            $stringAlias->value = $fieldNode->field->name;
                            $stringAlias->startPosition = $fieldNode->field->startPosition;
                            $stringAlias->endPosition = $fieldNode->field->endPosition;
                            $fieldNode->alias = $stringAlias;
                        }
                    }

                    if ($fieldNode->alias instanceof StringValueNode == false) {
                        if ($fieldNode->alias == null && $fieldNode->field instanceof VariableNode) {
                            $stringAlias = new StringValueNode();
                            $stringAlias->value = $fieldNode->field->name;
                            $stringAlias->startPosition = $fieldNode->field->startPosition;
                            $stringAlias->endPosition = $fieldNode->field->endPosition;
                            $fieldNode->alias = $stringAlias;
                        }
                    }

                    $fieldNode->startPosition = $fieldNode->field->startPosition;
                    if ($fieldNode->alias != null) {
                        $fieldNode->endPosition = $fieldNode->alias->endPosition;
                    } else {
                        $fieldNode->endPosition = $fieldNode->field->endPosition;
                    }

                    $fields[] = $fieldNode;
                    $i += 1;
                    continue;
                }
            }
        }

        $fieldStructure = new FieldsNode();
        $fieldStructure->fields = $fields;

        return $fieldStructure;
    }

    /**
     * Parses the provided nodes into a list of ValueDirectionNode.
     *
     * @param  AbstractNode[]  $nodes  The nodes to parse.
     * @return ValueDirectionNode[]
     */
    private function makeOrderGroup($nodes)
    {
        $nodeCount = count($nodes);
        $orders = [];
        $orderCount = 0;

        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $nodes[$i];

            $next = null;

            if ($i + 1 < $nodeCount) {
                $next = $nodes[$i + 1];
            }

            if ($i > 0) {
                if ($next == null || $next instanceof ArgSeparator) {
                    $orderCount += 1;
                    $orderNode = new ValueDirectionNode();
                    $orderNode->order = $orderCount;
                    $orderNode->name = $nodes[$i - 1];
                    $orderNode->directionNode = $thisNode;

                    if ($this->isOperand($orderNode->name) == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_INVALID_ORDER_BY_NAME_VALUE,
                            $orderNode->name,
                            'Invalid value or expression supplied for order by name.'
                        );
                    }

                    if ($this->isOperand($orderNode->directionNode) == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_INVALID_ORDER_BY_SORT_VALUE,
                            $orderNode->directionNode,
                            'Invalid value or expression supplied for order by direction.'
                        );
                    }

                    $orderNode->startPosition = $orderNode->name->startPosition;
                    $orderNode->endPosition = $orderNode->directionNode->endPosition;

                    $orders[] = $orderNode;
                    $i += 1;
                    continue;
                }
            }
        }

        return $orders;
    }

    /**
     * @param  AbstractNode[]  $nodes  The nodes.
     * @return ArgumentGroup
     *
     * @throws SyntaxErrorException
     */
    private function makeArgGroup($nodes)
    {
        $argGroup = new ArgumentGroup();

        $nodeCount = count($nodes);
        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $nodes[$i];

            if ($this->isOperand($thisNode) == false) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_UNEXPECTED_TOKEN_WHILE_PARSING_METHOD,
                    $thisNode,
                    'Unexpected ['.TypeLabeler::getPrettyTypeName($thisNode).'] while parsing argument group.'
                );
            }

            $next = null;

            if ($i + 1 < $nodeCount) {
                $next = $nodes[$i + 1];
            }

            if ($next == null || $next instanceof ArgSeparator) {
                $argGroup->args[] = $nodes[$i];
                $i += 1;
                continue;
            } elseif ($next instanceof InlineBranchSeparator) {
                if ($i + 2 >= $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_ARG_UNEXPECTED_NAMED_ARGUMENT,
                        $thisNode,
                        'Unexpected end of input while parsing named argument.'
                    );
                }
                $valueNode = $nodes[$i + 2];

                $namedArgument = new NamedArgumentNode();
                $namedArgument->startPosition = $thisNode->startPosition;
                $namedArgument->endPosition = $valueNode->endPosition;
                $namedArgument->content = $thisNode->content.$valueNode->content;
                $namedArgument->name = $thisNode;
                $namedArgument->value = $valueNode;

                if ($thisNode instanceof VariableNode == false) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_INVALID_NAMED_ARG_IDENTIFIER,
                        $thisNode,
                        'Invalid type ['.TypeLabeler::getPrettyTypeName($thisNode).'] supplied for named argument name.'
                    );
                }

                $argGroup->hasNamedArguments = true;
                $argGroup->numberOfNamedArguments += 1;

                $argGroup->args[] = $namedArgument;

                if ($i + 3 < $nodeCount && $nodes[$i + 3] instanceof ArgSeparator) {
                    $i += 3;
                } else {
                    $i += 2;
                }

                continue;
            }
        }

        $remainderMustBeNamed = false;

        foreach ($argGroup->args as $arg) {
            if ($arg instanceof NamedArgumentNode) {
                $remainderMustBeNamed = true;
            } else {
                if ($remainderMustBeNamed) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_UNNAMED_METHOD_ARGUMENT,
                        $arg,
                        'Unnamed arguments are not allowed to appear after a named argument.'
                    );
                }
            }
        }

        return $argGroup;
    }

    /**
     * Tests that there are no instances of LogicGroupEnd that are not paired.
     *
     * @param  AbstractNode[]  $tokens  The tokens to test.
     *
     * @throws SyntaxErrorException
     */
    private function validateNoDanglingLogicGroupEnds($tokens)
    {
        foreach ($tokens as $token) {
            if ($token instanceof LogicGroupEnd) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_LOGIC_GROUP_NO_START,
                    $token,
                    'Unexpected [T_LOGIC_END] while parsing input text.'
                );
            }
        }
    }

    /**
     * @param  AbstractNode[]  $nodes  The nodes to apply operation order to.
     * @return AbstractNode[]
     *
     * @throws SyntaxErrorException
     */
    private function applyOperationOrder($nodes)
    {
        $nodes = $this->groupNodesByType($nodes, ExponentiationOperator::class);
        $nodes = $this->groupNodesByType($nodes, MultiplicationOperator::class);
        $nodes = $this->groupNodesByType($nodes, DivisionOperator::class);
        $nodes = $this->groupNodesByType($nodes, AdditionOperator::class);
        $nodes = $this->groupNodesByType($nodes, SubtractionOperator::class);
        $nodes = $this->groupNodesByType($nodes, ModulusOperator::class);

        return $nodes;
    }

    /**
     * Counts the types of tokens to the right of the start with the given type.
     *
     * This method will stop the moment it finds a non-matching token.
     *
     * @param  AbstractNode[]  $tokens  The tokens to iterate.
     * @param  int  $start  The start index.
     * @param  string  $type  The type of token to count.
     * @return int
     */
    private function countTypeRight($tokens, $start, $type)
    {
        $count = 0;

        for ($i = $start; $i < count($tokens); $i++) {
            if ($tokens[$i] instanceof $type) {
                $count += 1;
            } else {
                break;
            }
        }

        return $count;
    }

    private function groupNodesByType($nodes, $type)
    {
        $newNodes = [];

        $nodeCount = count($nodes);
        for ($i = 0; $i < $nodeCount; $i++) {
            $node = $nodes[$i];

            if ($i > 0 && $node instanceof $type) {
                $left = [];

                $left[] = array_pop($newNodes);

                $this->assertOperandRight($nodes, $i);

                if ($i + 1 >= $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_REDUCING_NEGATION_OPERATORS,
                        $node,
                        'Unexpected end of input while parsing input text.'
                    );
                }

                $right = $nodes[$i + 1];

                $logicGroup = new LogicGroup();
                $logicGroup->nodes = array_merge($left, []);
                $logicGroup->nodes[] = $node;
                $logicGroup->nodes[] = $right;

                $i += 1;
                $newNodes[] = $logicGroup;
                continue;
            } else {
                $newNodes[] = $node;
            }
        }

        return $newNodes;
    }

    private function createLogicGroupsToResolveOperatorAmbiguity($nodes)
    {
        $nodes = $this->groupNodesByType($nodes, GreaterThanEqualCompOperator::class);
        $nodes = $this->groupNodesByType($nodes, GreaterThanCompOperator::class);

        $nodes = $this->groupNodesByType($nodes, LessThanEqualCompOperator::class);
        $nodes = $this->groupNodesByType($nodes, LessThanCompOperator::class);

        $nodes = $this->groupNodesByType($nodes, StrictEqualCompOperator::class);
        $nodes = $this->groupNodesByType($nodes, EqualCompOperator::class);

        $nodes = $this->groupNodesByType($nodes, NotStrictEqualCompOperator::class);
        $nodes = $this->groupNodesByType($nodes, NotEqualCompOperator::class);

        $nodes = $this->groupNodesByType($nodes, SpaceshipCompOperator::class);
        $nodes = $this->groupNodesByType($nodes, OperatorNodeContract::class);

        return $nodes;
    }

    /**
     * Tests if the provided token is a operand.
     *
     * @param  AbstractNode  $token  The token node to test.
     * @return bool
     */
    private function isOperand($token)
    {
        return $token instanceof VariableNode || $token instanceof LogicGroup ||
            $token instanceof StringValueNode || $token instanceof NumberNode ||
            $token instanceof FalseConstant || $token instanceof NullConstant ||
            $token instanceof TrueConstant || $token instanceof DirectionGroup ||
            $token instanceof ListValueNode || $token instanceof SwitchGroup ||
            $token instanceof FieldsNode || $token instanceof ArrayNode;
    }

    private function isProperMethodChainTargetStrict($token)
    {
        return $token instanceof LogicGroup ||
            $token instanceof StringValueNode || $token instanceof NumberNode ||
            $token instanceof FalseConstant || $token instanceof NullConstant ||
            $token instanceof TrueConstant || $token instanceof DirectionGroup ||
            $token instanceof ListValueNode || $token instanceof SwitchGroup ||
            $token instanceof FieldsNode || $token instanceof ArrayNode;
    }

    /**
     * Asserts that a valid operand appears to the right of the provided index.
     *
     * @param  AbstractNode[]  $tokens  The token input token stream.
     * @param  int  $i  The start index.
     *
     * @throws SyntaxErrorException
     */
    private function assertOperandRight($tokens, $i)
    {
        if ($i + 1 > (count($tokens) - 1)) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_UNEXPECTED_END_OF_INPUT,
                $tokens[$i], 'Unexpected end of input; expecting operand for operator '.TypeLabeler::getPrettyTypeName($tokens[$i]).' near "'.LineRetriever::getNearText($tokens[$i]).'".');
        }

        $token = $tokens[$i + 1];

        if (! $this->isOperand($token)) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_EXPECTING_OPERAND,
                $tokens[$i],
                'Expecting operand, found '.TypeLabeler::getPrettyTypeName($token).' near "'.LineRetriever::getNearText($tokens[$i]).'".');
        }
    }

    private function resolveValueRight($nodes, $index)
    {
        $value = null;
        $negationCount = 0;
        $lastNegation = null;

        if (! empty($nodes)) {
            while (true) {
                $curNode = $nodes[$index];

                if ($curNode instanceof LogicalNegationOperator) {
                    $lastNegation = $curNode;
                    $negationCount += 1;
                    $index += 1;
                    continue;
                } elseif ($this->isOperand($curNode)) {
                    $value = $curNode;
                    break;
                } else {
                    break;
                }
            }
        }

        if ($negationCount % 2 != 0) {
            $logicGroup = new LogicGroup();
            $logicGroup->nodes = [$lastNegation, $value];

            return [$logicGroup, $negationCount];
        }

        return [$value, $negationCount];
    }

    private function correctTypes($nodes)
    {
        $newNodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof ModifierValueSeparator) {
                $branchSeparator = new InlineBranchSeparator();
                $branchSeparator->startPosition = $node->startPosition;
                $branchSeparator->endPosition = $node->endPosition;
                $newNodes[] = $branchSeparator;
                continue;
            } elseif ($node instanceof ModifierValueNode) {
                $varNode = new VariableNode();
                $varNode->name = $node->value;
                $varNode->startPosition = $node->startPosition;
                $varNode->endPosition = $node->endPosition;
                $varNode->modifierChain = $node->modifierChain;
                $newNodes[] = $varNode;
                continue;
            }

            $newNodes[] = $node;
        }

        return $newNodes;
    }

    /**
     * Combines consecutive variable nodes into single nodes, where appropriate.
     *
     * view:array[key] would become a single node instead of:
     *    - var
     *    - branch separator
     *    - var
     *
     * @param  AbstractNode[]  $tokens  The nodes.
     * @return array
     */
    private function combineVariablePaths($tokens)
    {
        $newNodes = [];

        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $node = $tokens[$i];

            $newNodeCount = count($newNodes);

            if ($node instanceof InlineBranchSeparator) {
                if ($newNodeCount == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_BRANCH_SEPARATOR_FOR_VARCOMBINE,
                        null,
                        'Unexpected [T_BRANCH_SEPARATOR] while parsing input text.'
                    );
                }

                if ($i + 1 >= $tokenCount) {
                    $lastNodeText = '';

                    if ($i > 0) {
                        $lastNode = $tokens[$i - 1];
                        $lastNodeText = LineRetriever::getNearText($lastNode);
                    }

                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_PARSING_BRANCH_GROUP,
                        $node,
                        'Unexpected end of input while parsing input text near "'.$lastNodeText.LineRetriever::getNearText($node).'".'
                    );
                }

                $left = $newNodes[$newNodeCount - 1];
                $right = $tokens[$i + 1];

                if ($right instanceof NumberNode && NodeHelpers::distance($left, $right) === 1) {
                    $right = $this->wrapNumberInVariable($right);
                }

                if (($right instanceof NullConstant || $right instanceof TrueConstant || $right instanceof FalseConstant) && NodeHelpers::distance($left, $right) === 1) {
                    $right = $this->wrapConstantInVariable($right);
                }

                if ($left instanceof NumberNode && $right instanceof VariableNode) {
                    $left = $this->wrapNumberInVariable($left);
                }

                if ($left instanceof VariableNode && $right instanceof VariableNode && NodeHelpers::distance($left, $right) === 1) {
                    // Note: It is important when we do this merge
                    // that we start from the right, and merge
                    // the closest left node and work back.
                    // The right-most node may have a
                    // valid modifier chain on it.

                    // Remove the var off the stack.
                    array_pop($newNodes);

                    // Converts the inline branch separator into a : literal, and prepends it to the right var.
                    NodeHelpers::mergeVarContentRight(':', $node, $right);
                    // Merges the two variable nodes.
                    NodeHelpers::mergeVarRight($left, $right);

                    $newNodes[] = $right;

                    // Skip over the adjusted right.
                    $i += 1;
                    continue;
                } else {
                    $newNodes[] = $node;
                    continue;
                }
            } elseif ($node instanceof StringValueNode && $newNodeCount > 0) {
                if (($i + 1) >= $tokenCount) {
                    $newNodes[] = $node;
                    continue;
                }

                $left = $newNodes[$newNodeCount - 1];
                $right = $tokens[$i + 1];

                if ($left instanceof VariableNode && $right instanceof VariableNode && NodeHelpers::distance($left, $node) <= 1 &&
                    NodeHelpers::distance($node, $right) <= 1) {
                    array_pop($newNodes);
                    NodeHelpers::mergeVarContentLeft($node->sourceTerminator.$node->value.$node->sourceTerminator, $node, $left);
                    NodeHelpers::mergeVarContentLeft($right->name, $right, $left);

                    $newNodes[] = $left;

                    $i += 1;
                    continue;
                } else {
                    $newNodes[] = $node;
                }
            } elseif ($node instanceof ImplicitArrayBegin && $newNodeCount > 0) {
                $left = $newNodes[$newNodeCount - 1];
                $right = $tokens[$i + 1];

                if ($left instanceof VariableNode && $right instanceof VariableNode && NodeHelpers::distance($left, $node) <= 1 &&
                    NodeHelpers::distance($node, $right) <= 1) {
                    array_pop($newNodes);

                    NodeHelpers::mergeVarContentLeft($node->content, $node, $left);
                    NodeHelpers::mergeVarContentLeft($right->name, $right, $left);

                    $newNodes[] = $left;

                    $i += 1;
                    continue;
                } elseif ($this->canMergeIntoVariablePath($right) && $left instanceof VariableNode) {
                    array_pop($newNodes);

                    NodeHelpers::mergeVarContentLeft($node->content, $node, $left);

                    $rightContent = $this->getMergeContent($right);

                    NodeHelpers::mergeVarContentLeft($rightContent, $right, $left);

                    $newNodes[] = $left;
                    $i += 1;
                    continue;
                } else {
                    $newNodes[] = $node;
                    continue;
                }
            } elseif ($node instanceof ImplicitArrayEnd && $newNodeCount > 0) {
                $left = $newNodes[$newNodeCount - 1];

                if ($left instanceof VariableNode && NodeHelpers::distance($left, $node) <= 1 && Str::contains($left->name, '[')) {
                    array_pop($newNodes);
                    NodeHelpers::mergeVarContentLeft($node->content, $node, $left);
                    $newNodes[] = $left;

                    continue;
                } else {
                    $newNodes[] = $node;
                }
            } elseif ($node instanceof VariableNode && $newNodeCount > 0) {
                $left = $newNodes[$newNodeCount - 1];

                if ($left instanceof VariableNode && NodeHelpers::distance($left, $node) < 1) {
                    array_pop($newNodes);
                    NodeHelpers::mergeVarContentLeft($node->getVariableContent(), $node, $left);
                    $newNodes[] = $left;
                } else {
                    $newNodes[] = $node;
                }
                continue;
            } elseif ($node instanceof SubtractionOperator && $newNodeCount > 0) {
                $left = $newNodes[$newNodeCount - 1];
                $right = $tokens[$i + 1];

                if ($left instanceof VariableNode && $right instanceof VariableNode) {
                    $lDistance = NodeHelpers::distance($left, $node);
                    $rDistance = NodeHelpers::distance($node, $right);

                    if ($lDistance <= 1 && $rDistance <= 1) {
                        NodeHelpers::mergeVarContentLeft('-', $node, $left);
                        NodeHelpers::mergeVarContentLeft($right->name, $right, $left);
                        $left->endPosition = $right->endPosition;
                        $i += 1;
                    } else {
                        $newNodes[] = $node;
                    }
                } else {
                    $newNodes[] = $node;
                }
            } else {
                $newNodes[] = $node;
            }
        }

        foreach ($newNodes as $node) {
            if ($node instanceof VariableNode) {
                try {
                    $node->variableReference = $this->pathParser->parse($node->name);
                } catch (AntlersException $antlersException) {
                    $antlersException->node = $node;

                    throw $antlersException;
                }
            }
        }

        return $newNodes;
    }

    private function createLogicGroupsAroundMethodCalls($nodes)
    {
        // Bail early if we have not created any method calls anyway :)
        if (! $this->createdMethods) {
            return $nodes;
        }

        $newNodes = [];
        $nodeLen = count($nodes);

        for ($i = 0; $i < $nodeLen; $i++) {
            $thisNode = $nodes[$i];

            if ($thisNode instanceof MethodInvocationNode) {
                // Check to see if we should continue.
                $doContinue = false;
                if ($i + 1 < $nodeLen) {
                    for ($j = $i + 1; $j < $nodeLen; $j++) {
                        $checkNode = $nodes[$j];

                        if ($checkNode instanceof ModifierSeparator) {
                            $doContinue = true;
                            break;
                        }
                    }
                }

                if (! $doContinue) {
                    $newNodes[] = $thisNode;
                    continue;
                }

                $targetNodes = [];
                $lastNode = array_pop($newNodes);
                $targetNodes[] = $lastNode;

                // Scan backwards to find the variable this references.
                while ($lastNode instanceof VariableNode == false) {
                    $lastNode = array_pop($newNodes);
                    $targetNodes[] = $lastNode;
                }

                // Add the current invocation.
                $targetNodes[] = $thisNode;

                // Scan forwards to collect all chained calls.
                if ($i + 1 < $nodeLen && $nodes[$i + 1] instanceof  MethodInvocationNode) {
                    $skipTo = $i;

                    for ($j = $i + 1; $j < $nodeLen; $j++) {
                        $chainedNode = $nodes[$j];

                        if ($chainedNode instanceof MethodInvocationNode) {
                            $targetNodes[] = $chainedNode;
                        } else {
                            $skipTo = $j;
                            break;
                        }
                    }

                    $i = $skipTo;
                }

                $wrapper = new LogicGroup();
                $wrapper->startPosition = $targetNodes[0]->startPosition;
                $wrapper->endPosition = $targetNodes[count($targetNodes) - 1]->endPosition;
                $wrapper->nodes = $targetNodes;

                $semanticWrapper = new SemanticGroup();
                $semanticWrapper->nodes[] = $wrapper;

                $newNodes[] = $semanticWrapper;
            } else {
                $newNodes[] = $thisNode;
            }
        }

        return $newNodes;
    }

    private function associateModifiers($tokens)
    {
        $newNodes = [];

        /** @var AbstractNode $applyModifiersToNode */
        $applyModifiersToNode = null;

        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $node = $tokens[$i];

            if ($node instanceof ModifierSeparator) {
                $newNodeCount = count($newNodes);

                if ($newNodeCount == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_MODIFIER_SEPARATOR,
                        $node,
                        'Unexpected [T_MODIFIER_SEPARATOR] while parsing input text.'
                    );
                }

                $applyModifiersToNode = $newNodes[$newNodeCount - 1];

                if ($applyModifiersToNode->modifierChain == null) {
                    $applyModifiersToNode->modifierChain = new ModifierChainNode();
                }

                if ($i + 1 >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_MODIFIER_DETAILS,
                        $node,
                        'Unexpected end of input while preparing to gather modifier details.'
                    );
                }

                try {
                    $results = $this->scanToEndOfModifier(array_slice($tokens, $i + 1));
                } catch (AntlersException $antlersException) {
                    $antlersException->node = $node;

                    throw $antlersException;
                }

                $resultCount = count($results);

                // Let's correct for some arithmetic operators that can be valid modifiers.
                if ($resultCount > 1) {
                    if ($results[1] instanceof InlineBranchSeparator) {
                        $firstToken = $results[0];

                        if ($firstToken instanceof AdditionOperator) {
                            $results[0] = $this->wrapArithmeticModifier($firstToken, 'add');
                        } elseif ($firstToken instanceof SubtractionOperator) {
                            $results[0] = $this->wrapArithmeticModifier($firstToken, 'subtract');
                        } elseif ($firstToken instanceof DivisionOperator) {
                            $results[0] = $this->wrapArithmeticModifier($firstToken, 'divide');
                        } elseif ($firstToken instanceof MultiplicationOperator) {
                            $results[0] = $this->wrapArithmeticModifier($firstToken, 'multiply');
                        } elseif ($firstToken instanceof ModulusOperator) {
                            $results[0] = $this->wrapArithmeticModifier($firstToken, 'mod');
                        }
                    }
                }

                if ($resultCount == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNSET_MODIFIER_DETAILS,
                        $node,
                        'Invalid or missing modifier details.'
                    );
                }

                if ($results[0] instanceof ModifierNameNode == false) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_MODIFIER_NAME_NOT_START_OF_DETAILS,
                        $node,
                        'Invalid ['.TypeLabeler::getPrettyTypeName($results[0]).']; expecting [T_MODIFIER_NAME]'
                    );
                }

                try {
                    $modifier = $this->createModifier($results);
                } catch (AntlersException $antlersException) {
                    $antlersException->node = $node;

                    throw $antlersException;
                }

                $i += $resultCount;

                $applyModifiersToNode->modifierChain->modifierChain[] = $modifier;
                continue;
            } else {
                $newNodes[] = $node;
            }
        }

        return $newNodes;
    }

    private function canMergeIntoVariablePath(AbstractNode $node)
    {
        if ($node instanceof ModifierValueNode ||
            $node instanceof VariableNode ||
            $node instanceof TrueConstant ||
            $node instanceof FalseConstant ||
            $node instanceof NullConstant ||
            $node instanceof NumberNode ||
            $node instanceof StringValueNode
        ) {
            return true;
        }

        return false;
    }

    private function getMergeContent(AbstractNode $node)
    {
        if ($node instanceof ModifierValueNode ||
            $node instanceof VariableNode) {
            return $node->content;
        }

        if ($node instanceof TrueConstant) {
            return LanguageKeywords::ConstTrue;
        }

        if ($node instanceof FalseConstant) {
            return LanguageKeywords::ConstFalse;
        }

        if ($node instanceof NullConstant) {
            return LanguageKeywords::ConstNull;
        }

        if ($node instanceof StringValueNode) {
            return $node->toValueString();
        }

        if ($node instanceof NumberNode) {
            return $node->value;
        }

        return $node->innerContent();
    }

    private function isValidModifierValue($node)
    {
        if ($node instanceof ModifierValueNode ||
            $node instanceof VariableNode ||
            $node instanceof TrueConstant ||
            $node instanceof FalseConstant ||
            $node instanceof NullConstant ||
            $node instanceof NumberNode ||
            $node instanceof StringValueNode
        ) {
            return true;
        }

        return false;
    }

    private function wrapNumberInVariable(NumberNode $node)
    {
        $variableNode = new VariableNode();
        $variableNode->startPosition = $node->startPosition;
        $variableNode->endPosition = $node->endPosition;
        $variableNode->name = strval($node->value);
        $variableNode->content = strval($node->value);
        $variableNode->originalAbstractNode = $node;
        $variableNode->refId = $node->refId;
        $variableNode->modifierChain = $node->modifierChain;
        $variableNode->index = $node->index;

        return $variableNode;
    }

    private function wrapConstantInVariable(AbstractNode $node)
    {
        $variableNode = new VariableNode();
        $variableNode->startPosition = $node->startPosition;
        $variableNode->endPosition = $node->endPosition;
        $variableNode->name = strval($node->content);
        $variableNode->content = strval($node->content);
        $variableNode->originalAbstractNode = $node;
        $variableNode->refId = $node->refId;
        $variableNode->modifierChain = $node->modifierChain;
        $variableNode->index = $node->index;

        return $variableNode;
    }

    /**
     * Wraps an arithmetic node in a ModifierNameNode.
     *
     * @param  AbstractNode  $operator  The operator node.
     * @param  string  $modifierName  The name of the target modifier.
     * @return ModifierNameNode
     */
    private function wrapArithmeticModifier(AbstractNode $operator, $modifierName)
    {
        $node = new ModifierNameNode();
        $node->startPosition = $operator->startPosition;
        $node->endPosition = $operator->endPosition;
        $node->originalAbstractNode = $operator;
        $node->content = $modifierName;
        $node->name = $modifierName;
        $node->index = $operator->index;

        return $node;
    }

    private function createModifier($tokens)
    {
        $modifierName = array_shift($tokens);
        $values = [];

        if (! empty($tokens) && $tokens[0] instanceof LogicGroup) {
            if (count($tokens) > 1) {
                throw ErrorFactory::makeSyntaxError(
                    AntlersErrorCodes::TYPE_MODIFIER_UNEXPECTED_TOKEN_METHOD_SYNTAX,
                    $tokens[1],
                    'Unexpected ['.TypeLabeler::getPrettyTypeName($tokens[1]).'] while parsing modifier argument group. Expecting [T_MODIFIER_SEPARATOR] or end of current expression.'
                );
            }

            $unwrapped = $this->unpack($tokens[0]->nodes);
            $tArgGroup = $this->makeArgGroup($unwrapped);
            $modifierNode = new ModifierNode();

            $modifierNode->nameNode = $modifierName;
            $modifierNode->methodStyleArguments = $tArgGroup;

            return $modifierNode;
        }

        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            if ($tokens[$i] instanceof ModifierValueSeparator || $tokens[$i] instanceof InlineBranchSeparator) {
                if ($i + 1 >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_MODIFIER_UNEXPECTED_END_OF_VALUE_LIST,
                        null,
                        'Unexpected end of modifier value list while parsing modifier.'
                    );
                }

                $next = $tokens[$i + 1];

                if ($this->isValidModifierValue($next)) {
                    if ($next instanceof VariableNode) {
                        if ($next->variableReference != null && count($next->variableReference->pathParts) > 1) {
                            // Unwind the combined variable and covert them to modifier parameters.
                            foreach ($next->variableReference->pathParts as $combinedPart) {
                                if ($combinedPart instanceof PathNode) {
                                    $modifierValue = new ModifierValueNode();
                                    $modifierValue->startPosition = $combinedPart->startPosition;
                                    $modifierValue->endPosition = $combinedPart->endPosition;
                                    $modifierValue->value = $combinedPart->name;
                                    $modifierValue->name = $combinedPart->name;

                                    $values[] = $modifierValue;
                                }
                            }
                        } else {
                            $modifierValue = new ModifierValueNode();
                            $modifierValue->startPosition = $next->startPosition;
                            $modifierValue->endPosition = $next->endPosition;
                            $modifierValue->value = $next->name;
                            $modifierValue->name = $next->name;

                            $values[] = $modifierValue;
                        }
                    } else {
                        $values[] = $next;
                    }

                    $i += 1;
                    continue;
                } else {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_MODIFIER_UNEXPECTED_VALUE,
                        null,
                        'Unexpected ['.TypeLabeler::getPrettyTypeName($next).'] while parsing modifier value.'
                    );
                }
            }
        }

        $modifierNode = new ModifierNode();
        $modifierNode->nameNode = $modifierName;
        $modifierNode->valueNodes = $values;

        return $modifierNode;
    }

    private function scanToEndOfModifier($tokens)
    {
        $subTokens = [];
        foreach ($tokens as $subToken) {
            if ($subToken instanceof ModifierValueSeparator) {
                $subTokenCount = count($subTokens);

                if ($subTokenCount == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_MODIFIER_VALUE,
                        null,
                        'Unexpected end of input while parsing modifier value.'
                    );
                }

                $last = $subTokens[$subTokenCount - 1];

                if (NodeHelpers::distance($last, $subToken) > 1) {
                    break;
                }
            }

            if ($subToken instanceof StringValueNode || $subToken instanceof VariableNode || $subToken instanceof NumberNode) {
                $subTokenCount = count($subTokens);

                if ($subTokenCount > 0) {
                    $last = $subTokens[$subTokenCount - 1];

                    if ($last instanceof LogicGroup) {
                        break;
                    }
                }
            }

            if ($subToken instanceof ModifierSeparator ||
                $subToken instanceof LogicGroupEnd ||
                $subToken instanceof LogicGroupBegin ||

                $subToken instanceof EqualCompOperator ||
                $subToken instanceof GreaterThanCompOperator ||
                $subToken instanceof GreaterThanEqualCompOperator ||
                $subToken instanceof LessThanCompOperator ||
                $subToken instanceof LessThanEqualCompOperator ||
                $subToken instanceof NotEqualCompOperator ||
                $subToken instanceof NotStrictEqualCompOperator ||
                $subToken instanceof SpaceshipCompOperator ||
                $subToken instanceof StrictEqualCompOperator ||

                $subToken instanceof LogicalAndOperator ||
                $subToken instanceof LogicalOrOperator ||
                $subToken instanceof LogicalXorOperator ||
                $subToken instanceof NullCoalesceOperator ||
                $subToken instanceof StringConcatenationOperator ||

                $subToken instanceof LanguageOperatorConstruct ||
                $subToken instanceof MethodInvocationNode ||
                $subToken instanceof LogicalNegationOperator) {
                break;
            } else {
                $subTokens[] = $subToken;
            }
        }

        return $subTokens;
    }

    private function insertAutomaticStatementSeparators($tokens)
    {
        $tokenCount = count($tokens);
        $adjustedTokens = [];

        for ($i = 0; $i < $tokenCount; $i++) {
            $thisToken = $tokens[$i];

            if ($thisToken instanceof AssignmentOperatorNodeContract) {
                if ($i + 2 < $tokenCount) {
                    $peek = $tokens[$i + 2];

                    if ($peek instanceof StatementSeparatorNode == false) {
                        $adjustedTokens[] = $thisToken;
                        $adjustedTokens[] = $tokens[$i + 1];
                        $adjustedTokens[] = new StatementSeparatorNode();
                        $i += 1;
                        continue;
                    } else {
                        $adjustedTokens[] = $thisToken;
                        $adjustedTokens[] = $tokens[$i + 1];
                        $adjustedTokens[] = $tokens[$i + 2];
                        $i += 2;
                        continue;
                    }
                } else {
                    $adjustedTokens[] = $thisToken;
                    $adjustedTokens[] = $tokens[$i + 1];
                    $adjustedTokens[] = new StatementSeparatorNode();
                    break;
                }
            } else {
                $adjustedTokens[] = $thisToken;
            }
        }

        return $adjustedTokens;
    }

    private function createSemanticGroups($tokens)
    {
        $groups = [];
        $tokenCount = count($tokens);

        $groupNodes = [];

        for ($i = 0; $i < $tokenCount; $i++) {
            if ($tokens[$i] instanceof StatementSeparatorNode) {
                $semanticGroup = new SemanticGroup();
                $semanticGroup->nodes = $groupNodes;
                $groups[] = $semanticGroup;
                $groupNodes = [];

                continue;
            } else {
                $groupNodes[] = $tokens[$i];

                if ($i + 1 >= $tokenCount) {
                    $semanticGroup = new SemanticGroup();
                    $semanticGroup->nodes = $groupNodes;
                    $groups[] = $semanticGroup;
                    break;
                }
            }
        }

        return $groups;
    }

    private function createNullCoalescenceGroups($tokens)
    {
        $newTokens = [];

        $tokenCount = count($tokens);
        for ($i = 0; $i < $tokenCount; $i++) {
            $node = $tokens[$i];

            if ($node instanceof NullCoalesceOperator) {
                $left = array_pop($newTokens);

                if ($i + 1 >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_NULL_COALESCENCE_GROUP,
                        $node,
                        'Unexpected end of input while parsing input text.'
                    );
                }

                $right = $tokens[$i + 1];

                $nullCoalescenceGroup = new NullCoalescenceGroup();
                $nullCoalescenceGroup->left = $left;
                $nullCoalescenceGroup->right = $right;
                $newTokens[] = $nullCoalescenceGroup;

                $i += 1;
                continue;
            } else {
                $newTokens[] = $node;
            }
        }

        return $newTokens;
    }

    private function collectUntil(&$tokens)
    {
        $len = count($tokens);
        $collectedTokens = [];

        for ($i = $len - 1; $i >= 0; $i--) {
            if ($this->isAssignmentOperator($tokens[$i])) {
                break;
            }

            $collectedTokens[] = array_pop($tokens);
        }

        $collectedTokens = array_reverse($collectedTokens);

        if (count($collectedTokens) >= 3) {
            $parser = new LanguageParser();
            $parser->setIsRoot(false);

            return $this->unpack($parser->parse($collectedTokens));
        }

        return $collectedTokens;
    }

    private function unpack($tokens)
    {
        if (count($tokens) == 0) {
            return [];
        }

        if ($tokens[0] instanceof SemanticGroup) {
            return $this->unpack($tokens[0]->nodes);
        }

        return $tokens;
    }

    private function createTernaryGroups($tokens)
    {
        $newTokens = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $node = $tokens[$i];

            if ($node instanceof InlineTernarySeparator) {
                $separator = $this->seek(InlineBranchSeparator::class, $i + 1);

                if ($separator == null) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_TERNARY_EXPECTING_BRANCH_SEPARATOR,
                        null,
                        'Unexpected end of input; expecting [T_BRANCH_SEPARATOR].'
                    );
                }

                $result = $this->collectUntil($newTokens);
                $condition = $result;

                if (! is_array($condition)) {
                    $condition = [$condition];
                }

                $targetTokenIndex = $separator[1] - $i - 1;

                if ($targetTokenIndex >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_TERNARY_GROUP,
                        $node,
                        'Unexpected end of input while parsing ternary group.'
                    );
                }

                $truthBranch = array_slice($tokens, $i + 1, $targetTokenIndex);

                if (count($truthBranch) > 1 || count($truthBranch) == 0) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_TERNARY_UNEXPECTED_EXPRESSION_LENGTH,
                        null,
                        'Unexpected number of operations within ternary truth branch.'
                    );
                }

                $truthBranch = $truthBranch[0];
                $falseBranchStart = $separator[1] + 1;

                if ($falseBranchStart >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_TERNARY_GROUP_FALSE_BRANCH,
                        $node,
                        'Unexpected end of input while parsing ternary false execution branch.'
                    );
                }

                $falseBranch = $tokens[$falseBranchStart];

                $ternaryStructure = new TernaryCondition();
                $ternaryStructure->head = $condition;
                $ternaryStructure->truthBranch = $truthBranch;
                $ternaryStructure->falseBranch = $falseBranch;

                $newTokens[] = $ternaryStructure;
                $targetJumpIndex = $separator[1] + 1;

                /*if ($targetTokenIndex >= $tokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_EXITING_TERNARY_GROUP,
                        $node,
                        'Unexpected end of input while parsing input text.'
                    );
                }*/

                $i = $targetJumpIndex;
                continue;
            } else {
                $newTokens[] = $node;
            }
        }

        return $newTokens;
    }

    private function seek($type, $startingAt)
    {
        for ($i = $startingAt; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i] instanceof $type) {
                return [$this->tokens[$i], $i];
            }
        }

        return null;
    }

    private function createLogicalGroups($tokens)
    {
        $negatedGroupedTokens = [];
        $groupedTokens = [];

        $tokenCount = count($tokens);
        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $tokens[$i];

            if ($token instanceof LogicalNegationOperator) {
                $negationCount = $this->countTypeRight($tokens, $i, LogicalNegationOperator::class);

                if (count($negatedGroupedTokens) == 0 && count($tokens) == $negationCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_LOGIC_NEGATION_OPERATOR,
                        $token,
                        'Unexpected [T_LOGIC_INVERSE] while parsing input text.'
                    );
                }

                if ($i > 0 && ! empty($negatedGroupedTokens)) {
                    $prev = $negatedGroupedTokens[count($negatedGroupedTokens) - 1];

                    if ($prev instanceof NumberNode || $prev instanceof LogicGroupEnd || $prev instanceof LogicGroup) {
                        if ($prev instanceof LogicGroup) {
                            if ($prev->start instanceof LogicalNegationOperator) {
                                throw ErrorFactory::makeSyntaxError(
                                    AntlersErrorCodes::TYPE_FACTORIAL_MATERIALIZED_BOOL_DETECTED,
                                    $token,
                                    '[T_AOP_FACTORIAL] operand will always materialize boolean type.'
                                );
                            }
                        }

                        $factorialOperator = new FactorialOperator();
                        $factorialOperator->startPosition = $token->startPosition;
                        $factorialOperator->endPosition = $token->endPosition;
                        $factorialOperator->content = str_repeat('!', $negationCount);
                        $factorialOperator->originalAbstractNode = $token;
                        $factorialOperator->repeat = $negationCount;

                        $negatedGroupedTokens[] = $factorialOperator;

                        if ($negationCount > 0) {
                            $i += $negationCount - 1;
                        }

                        continue;
                    }
                }

                // Just ignore these at the parser level.
                // An even number of negation operators are the same has having no negation operators.
                if ($negationCount % 2 == 0) {
                    $i += $negationCount - 1;
                    continue;
                }

                // We want to peek to the one after the last negation operator.
                $peek = $tokens[$i + $negationCount];

                if ($peek instanceof LogicGroupBegin) {
                    // Scan right to count the negations.

                    $targetSliceOffset = $i + $negationCount + 1;
                    if ($targetSliceOffset >= $tokenCount) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_NEGATION_OFFSET,
                            $token,
                            'Unexpected end of input while parsing input text.'
                        );
                    }

                    $groupResults = $this->findLogicalGroupEnd(
                        $tokens[$i + $negationCount],
                        array_slice($tokens, $targetSliceOffset)
                    );

                    /** @var LogicGroup $subGroup */
                    $subGroup = $groupResults[0];
                    // Let's now wrap this in it's own group to negate the entire thing.
                    $wrapperGroup = new LogicGroup();
                    $wrapperGroup->start = $token;
                    $wrapperGroup->end = $subGroup;
                    $wrapperGroup->nodes = [$token, $subGroup];
                    $wrapperGroup->startPosition = $token->startPosition;
                    $wrapperGroup->endPosition = $subGroup->endPosition;

                    $negatedGroupedTokens[] = $wrapperGroup;
                    $i += $groupResults[1] + $negationCount;

                    continue;
                }

                $results = $this->resolveValueRight($tokens, $i);

                $negatedGroupedTokens[] = $results[0];
                $i += $results[1];
            } else {
                $negatedGroupedTokens[] = $token;
            }
        }

        $negatedTokenCount = count($negatedGroupedTokens);

        for ($i = 0; $i < $negatedTokenCount; $i++) {
            $token = $negatedGroupedTokens[$i];

            if ($token instanceof LogicGroupBegin) {
                if ($i + 1 >= $negatedTokenCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_END_DUE_TO_NEGATION,
                        $token,
                        'Unexpected end of input while parsing input text.'
                    );
                }

                $group = $this->findLogicalGroupEnd($token, array_slice($negatedGroupedTokens, $i + 1));

                $groupedTokens[] = $group[0];
                $i += $group[1];
            } else {
                if ($token instanceof FactorialOperator) {
                    if (count($groupedTokens) == 0) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_FACTORIAL_WHILE_CREATING_GROUPS,
                            $token,
                            'Unexpected [T_AOP_FACTORIAL] while parsing input text.'
                        );
                    }

                    $prev = $groupedTokens[count($groupedTokens) - 1];

                    if ($prev instanceof LogicGroup == false &&
                        $prev instanceof NumberNode == false) {
                        throw ErrorFactory::makeSyntaxError(
                            AntlersErrorCodes::TYPE_UNEXPECTED_FACTORIAL_OPERAND,
                            $token,
                            'Unexpected left operand encountered for [T_AOP_FACTORIAL] while parsing input text.'
                        );
                    }

                    /** @var AbstractNode $prev */
                    $prev = array_pop($groupedTokens);
                    $wrapperGroup = new LogicGroup();
                    $wrapperGroup->startPosition = $prev->startPosition;
                    $wrapperGroup->endPosition = $token->endPosition;
                    $wrapperGroup->originalAbstractNode = $token;
                    $wrapperGroup->nodes = [
                        $prev, $token,
                    ];

                    $groupedTokens[] = $wrapperGroup;
                } else {
                    $groupedTokens[] = $token;
                }
            }
        }

        return $groupedTokens;
    }

    /**
     * @param  LogicGroupBegin  $root
     * @param  AbstractNode[]  $nodes
     * @return array
     */
    private function findLogicalGroupEnd($root, $nodes)
    {
        $subNodes = [];
        $end = null;
        $skipCount = 0;

        $nodeCount = count($nodes);
        for ($i = 0; $i < $nodeCount; $i++) {
            $node = $nodes[$i];
            $skipCount += 1;

            if ($node instanceof LogicGroupEnd) {
                $end = $node;
                break;
            } elseif ($node instanceof LogicGroupBegin) {
                if ($i + 1 >= $nodeCount) {
                    throw ErrorFactory::makeSyntaxError(
                        AntlersErrorCodes::TYPE_UNEXPECTED_EOI_WHILE_PARSING_LOGIC_GROUP_END,
                        $node,
                        'Unexpected end of input while parsing input text.'
                    );
                }

                $subGroup = $this->findLogicalGroupEnd($node, array_slice($nodes, $i + 1));
                $subNodes[] = $subGroup[0];
                $skipCount += $subGroup[1];
                $i += $subGroup[1];
                continue;
            } else {
                $subNodes[] = $node;
            }
        }

        if ($end == null) {
            throw ErrorFactory::makeSyntaxError(
                AntlersErrorCodes::TYPE_LOGIC_GROUP_NO_END,
                $root,
                'Unexpected end of input while parsing logic group.'
            );
        }

        $parser = new LanguageParser();
        $parser->setIsRoot(false);

        $logicalGroup = new LogicGroup();

        if (count($subNodes) >= 2 && $subNodes[1] instanceof ScopeAssignmentOperator) {
            $logicalGroup = new ScopedLogicGroup();

            if ($i + 2 < $nodeCount && $nodes[$i + 1] instanceof VariableNode && $nodes[$i + 2] instanceof StringValueNode) {
                /** @var VariableNode $candidateVarNode */
                $candidateVarNode = $nodes[$i + 1];

                if ($candidateVarNode->name == LanguageKeywords::ScopeAs) {
                    /** @var StringValueNode $aliasNode */
                    $aliasNode = $nodes[$i + 2];

                    $logicalGroup = new AliasedScopeLogicGroup();
                    $logicalGroup->alias = $aliasNode;
                    $skipCount += 2;
                }
            }

            $scopeNodes = array_splice($subNodes, 0, 2);
            $logicalGroup->scope = $scopeNodes[0];
        }

        $logicalGroup->nodes = $parser->parse($subNodes);
        $logicalGroup->start = $root;
        $logicalGroup->end = $end;
        $logicalGroup->startPosition = $root->startPosition;
        $logicalGroup->endPosition = $end->endPosition;

        if ($logicalGroup instanceof AliasedScopeLogicGroup) {
            $logicalGroup->endPosition = $logicalGroup->alias->endPosition;
        }

        return [$logicalGroup, $skipCount];
    }
}
