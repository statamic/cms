<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use ArrayAccess;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Statamic\Contracts\Query\Builder;
use Statamic\Contracts\Support\Boolable;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Value;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Errors\LineRetriever;
use Statamic\View\Antlers\Language\Errors\TypeLabeler;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Exceptions\VariableAccessException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\ArgumentGroup;
use Statamic\View\Antlers\Language\Nodes\ArithmeticNodeContract;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\MethodInvocationNode;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierChainNode;
use Statamic\View\Antlers\Language\Nodes\ModifierValueNode;
use Statamic\View\Antlers\Language\Nodes\NamedArgumentNode;
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
use Statamic\View\Antlers\Language\Nodes\Operators\ConditionalVariableFallbackOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LanguageOperatorConstruct;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalAndOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalNegationOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalOrOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\LogicalXorOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\StringConcatenationOperator;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ArrayNode;
use Statamic\View\Antlers\Language\Nodes\Structures\DirectionGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\FieldsNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ListValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroupEnd;
use Statamic\View\Antlers\Language\Nodes\Structures\NullCoalescenceGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\TernaryCondition;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Cascade;

class Environment
{
    protected $dataRetriever = null;
    protected $pathParser = null;
    protected $isEvaluatingTruthValue = false;
    protected $interpolationReplacements = [];
    protected $interpolationKeys = [];
    protected $assignments = [];
    protected $dataManagerInterpolations = [];
    private $protectedScopes = ['view'];

    /**
     * @var LanguageOperatorManager|null
     */
    protected $operatorManager = null;

    /**
     * @var Parser|null;
     */
    protected $antlersParser = null;

    protected $data = [];

    /**
     * @var Cascade|null
     */
    private $cascade = null;

    /**
     * @var AbstractNode|null
     */
    private $lastNode = null;

    /**
     * @var NodeProcessor|null
     */
    protected $nodeProcessor = null;

    public function __construct()
    {
        $this->pathParser = new PathParser();
        $this->dataRetriever = new PathDataManager();
        $this->dataRetriever->setEnvironment($this);
        $this->dataRetriever->setIsPaired(false);
        $this->operatorManager = new LanguageOperatorManager();

        $this->operatorManager->setEnvironment($this);
    }

    public function setDataManagerInterpolations($interpolations)
    {
        $this->dataManagerInterpolations = $interpolations;
        $this->dataRetriever->setInterpolations($interpolations);

        return $this;
    }

    public function setIsPaired($paired)
    {
        $this->dataRetriever->setIsPaired($paired);

        return $this;
    }

    public function getIsPaired()
    {
        return $this->dataRetriever->getIsPaired();
    }

    /**
     * Returns all triggered assignments from the environment session.
     *
     * @return array
     */
    public function getAssignments()
    {
        return $this->assignments;
    }

    /**
     * Sets whether the final return value is collapsed to a string, augmented, etc.
     *
     * @param  bool  $reduceFinal  Whether to reduce.
     */
    public function setReduceFinal($reduceFinal)
    {
        $this->dataRetriever->setReduceFinal($reduceFinal);
    }

    /**
     * Sets the internal NodeProcessors instance.
     *
     * @param  NodeProcessor  $processor  The instance.
     * @return $this
     */
    public function setProcessor(NodeProcessor $processor)
    {
        $this->nodeProcessor = $processor;

        $this->cascade($processor->getCascade());
        $this->operatorManager->setNodeProcessor($this->nodeProcessor);

        return $this;
    }

    public function _getNodeProcessor()
    {
        return $this->nodeProcessor;
    }

    /***
     * Clears the internal NodeProcessor instance.
     *
     * @return $this
     */
    public function resetNodeProcessor()
    {
        $this->operatorManager->resetNodeProcessor();

        $this->nodeProcessor = null;

        return $this;
    }

    /**
     * Sets the Cascade instance.
     *
     * @param  Cascade|null  $cascade  The Cascade instance.
     * @return $this
     */
    public function cascade($cascade)
    {
        $this->cascade = $cascade;

        $this->dataRetriever->cascade($this->cascade);

        return $this;
    }

    /**
     * Sets the interpolation replacements.
     *
     * @param  array  $replacements  The replacements.
     */
    public function setInterpolationReplacements($replacements)
    {
        $this->interpolationReplacements = $replacements;
        $this->interpolationKeys = array_keys($this->interpolationReplacements);
    }

    /**
     * Returns the current data within the Environment.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the internal Parser instance.
     *
     * @param  Parser  $parser  The parser instance.
     */
    public function setParserInstance(Parser $parser)
    {
        $this->antlersParser = $parser;
        $this->dataRetriever->setAntlersParser($this->antlersParser);
    }

    /**
     * Resets the internal parser instance.
     */
    public function resetParser()
    {
        $this->antlersParser = null;
        $this->dataRetriever->resetParser();
    }

    /**
     * Sets the internal data instance.
     *
     * @param  array|mixed  $data  The data to set.
     */
    public function setData($data)
    {
        $this->data = $data;

        if (is_array($this->data) && count($this->data) == 1 && Arr::isAssoc($this->data) == false) {
            $this->data = $this->data[0];
        }
    }

    /**
     * @param  SemanticGroup[]  $statements
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function processStatements($statements)
    {
        $lastStackVal = null;

        foreach ($statements as $statement) {
            if (count($statement->nodes) == 0) {
                continue;
            }

            $lastStackVal = $this->process($statement->nodes);
        }

        return $lastStackVal;
    }

    /**
     * Evaluates the provided runtime nodes.
     *
     * @param  AbstractNode[]  $nodes  The runtime nodes.
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function evaluate($nodes)
    {
        if (count($nodes) == 0) {
            return null;
        }

        if ($nodes[0] instanceof SemanticGroup) {
            return $this->processStatements($nodes);
        }

        return $this->process($nodes);
    }

    private function lock()
    {
        if ($this->nodeProcessor != null) {
            $this->nodeProcessor->createLockData();
        }
    }

    private function unlock()
    {
        if ($this->nodeProcessor != null) {
            $this->nodeProcessor->restoreLockedData();
        }
    }

    /**
     * Evaluates the provided nodes as a boolean expression.
     *
     * @param  AbstractNode[]|AbstractNode  $nodes  The runtime nodes.
     * @return bool|mixed|null
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function evaluateBool($nodes)
    {
        $this->lock();
        $this->isEvaluatingTruthValue = true;
        $result = $this->getValue($this->evaluate($nodes));
        $this->isEvaluatingTruthValue = false;

        if (is_object($result)) {
            if ($result instanceof Boolable) {
                $value = $this->getTruthValue($result->toBool());
                $this->unlock();

                return $value;
            } elseif ($result instanceof Builder) {
                $builderResults = $result->count();
                $this->unlock();

                return $builderResults > 0;
            } elseif ($result instanceof Collection) {
                $value = $result->count() > 0;
                $this->unlock();

                return $value;
            }

            $this->unlock();

            return true;
        }

        if (is_bool($result)) {
            $this->unlock();

            return $result;
        }

        if (is_numeric($result)) {
            // Updated to be != 0 to be consistent with PHP behavior.
            $value = $result != 0;
            $this->unlock();

            return $value;
        }

        $this->unlock();

        return null;
    }

    /**
     * Evaluates the provided value and returns a boolean equivalent.
     *
     * @param  mixed  $value  The value to evaluate.
     * @return bool|mixed
     */
    private function getTruthValue($value)
    {
        if ($value == null) {
            return false;
        }

        if (is_string($value) && mb_strlen($value) > 0) {
            return true;
        }

        return $value;
    }

    /**
     * Tests if the provided value is numeric.
     *
     * @param  mixed  $value  The value to test.
     *
     * @throws RuntimeException
     */
    private function assertNumericValue($value)
    {
        if (! is_numeric($value)) {
            throw ErrorFactory::makeRuntimeError(
                AntlersErrorCodes::TYPE_RUNTIME_TYPE_MISMATCH,
                $this->lastNode,
                'Expected numeric type; got ['.TypeLabeler::getPrettyRuntimeTypeName($value).'] near "'.LineRetriever::getNearText($this->lastNode).'".'
            );
        }
    }

    /**
     * Tests the provided value is a safe divisor.
     *
     * @param  mixed  $value  The value to test.
     *
     * @throws RuntimeException
     */
    private function assertNonZeroForDivisor($value)
    {
        if ($value == 0) {
            throw ErrorFactory::makeRuntimeError(
                AntlersErrorCodes::TYPE_RUNTIME_DIVIDE_BY_ZERO,
                $this->lastNode,
                'Cannot divide by zero; encountered near "'.LineRetriever::getNearText($this->lastNode).'".'
            );
        }
    }

    /**
     * Resolves the provided value to be used within a comparison operation.
     *
     * @param  mixed  $value  The value to resolve.
     * @return mixed|string
     */
    private function getComparisonValue($value)
    {
        if (is_object($value)) {
            $lockData = $this->nodeProcessor->getAllData();
            if ($value instanceof Value) {
                $value = $value->value();
            } elseif ($value instanceof ArrayableString) {
                $value = (string) $value;
            }
            $this->nodeProcessor->swapData($lockData);
        }

        return $value;
    }

    /**
     * Evaluates the provided nodes and returns the result.
     *
     * @param  AbstractNode[]  $nodes  The runtime nodes.
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function process($nodes)
    {
        if (count($nodes) == 1 && $nodes[0] instanceof LogicGroup) {
            $tmpNodes = $nodes[0]->nodes;

            // We will move the modifier chain to the unwrapped node.
            if ($nodes[0]->modifierChain != null && ! empty($nodes[0]->modifierChain->modifierChain)) {
                if (count($tmpNodes) === 1) {
                    $tmpNodes[0]->modifierChain = $nodes[0]->modifierChain;
                    $nodes = $tmpNodes;
                }
            } elseif ($nodes[0]->modifierChain === null) {
                $nodes = $tmpNodes;
            }
        }

        $stack = [];

        for ($i = 0; $i < count($nodes); $i++) {
            $currentNode = $nodes[$i];
            $this->lastNode = $currentNode;

            if ($currentNode instanceof AdditionOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftVal = $this->getValue($left);
                $rightVal = $this->getValue($right);

                if (is_string($leftVal) || is_string($rightVal)) {
                    $stack[] = $leftVal.$rightVal;
                } else {
                    $this->assertNumericValue($leftVal);
                    $this->assertNumericValue($rightVal);
                    $stack[] = ($leftVal + $rightVal);
                }

                $i += 1;

                continue;
            } elseif ($currentNode instanceof StringConcatenationOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                if ($left instanceof VariableNode) {
                    $varName = $this->nameOf($left);

                    $leftVal = (string) $this->getValue($left);
                    $rightVal = (string) $this->getValue($right);

                    $newValue = $leftVal.$rightVal;
                    $this->dataRetriever->setRuntimeValue($varName, $this->data, $newValue);
                    $this->assignments[$this->dataRetriever->lastPath()] = $newValue;
                } else {
                    // Fall back to stack behavior when left is not a variable.
                    $leftVal = (string) $this->getValue($left);
                    $rightVal = (string) $this->getValue($right);

                    $stack[] = $leftVal.$rightVal;
                }

                $i += 1;

                continue;
            } elseif ($currentNode instanceof FactorialOperator) {
                $left = array_pop($stack);
                $leftValue = TypeCoercion::coerceType($this->getValue($left));

                $this->assertNumericValue($leftValue);

                if ($currentNode->repeat > 1) {
                    $stack[] = RuntimeHelpers::iterativeFactorial($leftValue, $currentNode->repeat);
                } else {
                    $stack[] = RuntimeHelpers::factorial($leftValue);
                }

                continue;
            } elseif ($currentNode instanceof DivisionOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftValue = TypeCoercion::coerceType($this->getValue($left));
                $rightValue = TypeCoercion::coerceType($this->getValue($right));

                $this->assertNumericValue($leftValue);
                $this->assertNumericValue($rightValue);
                $this->assertNonZeroForDivisor($rightValue);

                $stack[] = ($leftValue / $rightValue);
                $i += 1;

                continue;
            } elseif ($currentNode instanceof ExponentiationOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftValue = TypeCoercion::coerceType($this->getValue($left));
                $rightValue = TypeCoercion::coerceType($this->getValue($right));

                $this->assertNumericValue($leftValue);
                $this->assertNumericValue($rightValue);

                $stack[] = pow($leftValue, $rightValue);
                $i += 1;

                continue;
            } elseif ($currentNode instanceof ModulusOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftValue = TypeCoercion::coerceType($this->getValue($left));
                $rightValue = TypeCoercion::coerceType($this->getValue($right));

                $this->assertNumericValue($leftValue);
                $this->assertNumericValue($rightValue);

                $stack[] = ($leftValue % $rightValue);

                $i += 1;

                continue;
            } elseif ($currentNode instanceof MultiplicationOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftValue = TypeCoercion::coerceType($this->getValue($left));
                $rightValue = TypeCoercion::coerceType($this->getValue($right));

                $this->assertNumericValue($leftValue);
                $this->assertNumericValue($rightValue);

                $stack[] = ($leftValue * $rightValue);
                $i += 1;

                continue;
            } elseif ($currentNode instanceof SubtractionOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $leftValue = TypeCoercion::coerceType($this->getValue($left));
                $rightValue = TypeCoercion::coerceType($this->getValue($right));

                $this->assertNumericValue($leftValue);
                $this->assertNumericValue($rightValue);

                $stack[] = ($leftValue - $rightValue);
                $i += 1;

                continue;
            } elseif ($currentNode instanceof EqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                if ($restore) {
                    $restoreRf = $this->dataRetriever->getReduceFinal();
                    $this->dataRetriever->setReduceFinal(false);
                    $this->isEvaluatingTruthValue = false;
                    $left = $this->getComparisonValue($this->getValue($left));
                    $this->isEvaluatingTruthValue = true;

                    $this->dataRetriever->setReduceFinal($restoreRf);
                } else {
                    $right = $this->getComparisonValue($this->getValue($right));
                    $left = $this->getComparisonValue($this->getValue($left));
                }

                $this->isEvaluatingTruthValue = false;

                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left == $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof GreaterThanCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left > $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof GreaterThanEqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left >= $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof LessThanCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left < $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof LessThanEqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left <= $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof NotEqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left != $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof NotStrictEqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left !== $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof SpaceshipCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left <=> $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof StrictEqualCompOperator) {
                $left = array_pop($stack);
                $right = $nodes[$i + 1];

                $restore = $this->isEvaluatingTruthValue;
                $this->isEvaluatingTruthValue = false;
                $left = $this->getComparisonValue($this->getValue($left));
                $right = $this->getComparisonValue($this->getValue($right));
                $this->isEvaluatingTruthValue = $restore;

                $stack[] = $left === $right;

                $i += 1;

                continue;
            } elseif ($currentNode instanceof LogicalOrOperator) {
                $left = $this->getValue(array_pop($stack));
                $right = $this->getValue($nodes[$i + 1]);

                $left = $this->getComparisonValue($left);
                $right = $this->getComparisonValue($right);

                if ($this->isEvaluatingTruthValue) {
                    $stack[] = ($left || $right);
                } else {
                    if ($left) {
                        $stack[] = $left;
                    } else {
                        $stack[] = $right;
                    }
                }

                $i += 1;

                continue;
            } elseif ($currentNode instanceof LogicalAndOperator) {
                $left = $this->getValue(array_pop($stack));
                $right = $this->getValue($nodes[$i + 1]);

                $stack[] = ($this->getComparisonValue($left) && $this->getComparisonValue($right));
                $i += 1;

                continue;
            } elseif ($currentNode instanceof LogicalXorOperator) {
                $left = $this->getValue(array_pop($stack));
                $right = $this->getValue($nodes[$i + 1]);

                $stack[] = ($this->getComparisonValue($left) xor $this->getComparisonValue($right));

                $i += 1;

                continue;
            } elseif ($currentNode instanceof LogicGroup) {
                $stack[] = $currentNode;

                continue;
            } elseif ($currentNode instanceof NullCoalescenceGroup) {
                $stack[] = $this->adjustValue($this->evaluateNullCoalescence($currentNode), $currentNode);

                continue;
            } elseif ($currentNode instanceof TernaryCondition) {
                $stack[] = $this->adjustValue($this->evaluateTernaryGroup($currentNode), $currentNode);

                continue;
            } elseif ($currentNode instanceof LogicalNegationOperator) {
                $right = $this->getTruthValue($this->getComparisonValue($this->getValue($nodes[$i + 1])));

                $stack[] = $right == false;

                // Need to skip over the value node.
                $i += 1;

                continue;
            } elseif ($currentNode instanceof LanguageOperatorConstruct) {
                if (! array_key_exists($currentNode->content, LanguageOperatorRegistry::$operators)) {
                    throw ErrorFactory::makeRuntimeError(
                        AntlersErrorCodes::TYPE_RUNTIME_UNKNOWN_LANG_OPERATOR,
                        $currentNode,
                        'Illegal language operator "'.$currentNode->content.'"'
                    );
                }

                if (array_key_exists($currentNode->content, LanguageOperatorRegistry::$getsArgsFromRight)) {
                    $right = $nodes[$i + 1];

                    $restore = $this->isEvaluatingTruthValue;
                    $this->isEvaluatingTruthValue = false;
                    $right = $this->getValue($right);
                    $this->isEvaluatingTruthValue = $restore;

                    $seekLeft = $i - 1;

                    if ($i == 0) {
                        $seekLeft = $i;
                    }
                    $stack[] = $this->operatorManager->evaluateOperator(
                        $currentNode->content, $right, null,
                        $nodes[$seekLeft], $nodes[$i + 1], $this->data);
                } else {
                    $leftNode = array_pop($stack);
                    $right = $nodes[$i + 1];

                    $restore = $this->isEvaluatingTruthValue;
                    $this->isEvaluatingTruthValue = false;
                    $left = $this->getValue($leftNode);
                    $right = $this->getValue($right);
                    $this->isEvaluatingTruthValue = $restore;

                    $seekLeft = $i - 1;

                    if ($i == 0) {
                        $seekLeft = $i;
                    }

                    $result = $this->operatorManager->evaluateOperator(
                        $currentNode->content,
                        $left, $right,
                        $nodes[$seekLeft],
                        $nodes[$i + 1], $this->data);

                    $stack[] = $result;
                }

                $i += 3;

                continue;
            } elseif ($currentNode instanceof MethodInvocationNode) {
                $leftNode = array_pop($stack);

                if ($leftNode == null) {
                    $stack[] = null;

                    continue;
                }
                $leftVal = $this->getValue($leftNode);

                if ($leftVal == null) {
                    $stack[] = null;

                    continue;
                }

                try {
                    $args = $this->evaluateArgumentGroup($currentNode->args);

                    if ($currentNode->method instanceof LanguageOperatorConstruct) {
                        $methodName = $currentNode->method->content;
                    } elseif ($currentNode->method instanceof VariableNode) {
                        $methodName = $currentNode->method->name;
                    } else {
                        throw ErrorFactory::makeRuntimeError(
                            AntlersErrorCodes::TYPE_RUNTIME_BAD_METHOD_CALL,
                            $currentNode,
                            'Cannot resolve target method name.'
                        );
                    }

                    $callRes = call_user_func([$leftVal, $methodName], ...$args);

                    $stack[] = $callRes;
                } catch (Exception $exception) {
                    throw ErrorFactory::makeRuntimeError(
                        AntlersErrorCodes::TYPE_RUNTIME_BAD_METHOD_CALL,
                        $currentNode,
                        $exception->getMessage()
                    );
                }

                continue;
            }

            $stack[] = $currentNode;
        }

        // Treats an empty stack as an implicit "null".
        // This happens when everything inside the
        // node was purely operation/assignments.
        if (count($stack) == 0) {
            return null;
        }

        if (count($stack) == 1) {
            return $this->getValue($stack[0]);
        }

        if (count($stack) == 3) {
            $left = $stack[0];
            $rightNode = $stack[2];
            $operand = $stack[1];

            if ($operand instanceof LeftAssignmentOperator) {
                $varName = $this->nameOf($left);

                $right = $this->checkForFieldValue($this->getValue($rightNode));

                $this->dataRetriever->setRuntimeValue($varName, $this->data, $right);
                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $right = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $right;

                    if (array_key_exists($lastPath, GlobalRuntimeState::$tracedRuntimeAssignments)) {
                        GlobalRuntimeState::$tracedRuntimeAssignments[$lastPath] = $right;
                    }
                }

                return null;
            } elseif ($operand instanceof AdditionAssignmentOperator) {
                $varName = $this->nameOf($left);
                $curVal = $this->checkForFieldValue($this->scopeValue($varName));
                $right = $this->checkForFieldValue($this->getValue($rightNode));

                if (is_string($curVal) && is_string($right)) {
                    // Allows for addition assignment to act
                    // like string concatenation when both
                    // the left and right are string types.
                    $newVal = $curVal.$right;
                } elseif (is_array($curVal)) {
                    $newVal = $curVal;
                    $newVal[] = $right;
                } else {
                    $curVal = $this->convertToRuntimeNumeric($curVal, $varName);

                    // Handle numeric case.
                    $right = floatval($right);

                    $this->assertNumericValue($curVal);
                    $this->assertNumericValue($right);

                    $newVal = $curVal + $right;
                }

                $this->dataRetriever->setRuntimeValue(
                    $varName,
                    $this->data,
                    $newVal
                );

                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $newVal = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $newVal;
                }

                return null;
            } elseif ($operand instanceof DivisionAssignmentOperator) {
                $varName = $this->nameOf($left);
                $curVal = $this->checkForFieldValue($this->numericScopeValue($varName));
                $right = $this->checkForFieldValue($this->getValue($rightNode));

                $this->assertNumericValue($curVal);
                $this->assertNumericValue($right);
                $this->assertNonZeroForDivisor($right);

                $assignValue = $curVal / $right;

                $this->dataRetriever->setRuntimeValue(
                    $varName,
                    $this->data,
                    $assignValue
                );

                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $assignValue = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $assignValue;
                }

                return null;
            } elseif ($operand instanceof ModulusAssignmentOperator) {
                $varName = $this->nameOf($left);
                $curVal = $this->checkForFieldValue($this->numericScopeValue($varName));
                $right = $this->checkForFieldValue($this->getValue($rightNode));

                $this->assertNumericValue($curVal);
                $this->assertNumericValue($right);

                $assignValue = $curVal % $right;

                $this->dataRetriever->setRuntimeValue(
                    $varName,
                    $this->data,
                    $assignValue
                );

                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $assignValue = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $assignValue;
                }

                return null;
            } elseif ($operand instanceof MultiplicationAssignmentOperator) {
                $varName = $this->nameOf($left);
                $curVal = $this->checkForFieldValue($this->numericScopeValue($varName));
                $right = $this->checkForFieldValue($this->getValue($rightNode));

                $this->assertNumericValue($curVal);
                $this->assertNumericValue($right);

                $assignValue = $curVal * $right;

                $this->dataRetriever->setRuntimeValue(
                    $varName,
                    $this->data,
                    $assignValue
                );

                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $assignValue = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $assignValue;
                }

                return null;
            } elseif ($operand instanceof SubtractionAssignmentOperator) {
                $varName = $this->nameOf($left);
                $curVal = $this->checkForFieldValue($this->numericScopeValue($varName));
                $right = $this->checkForFieldValue($this->getValue($rightNode));

                $this->assertNumericValue($curVal);
                $this->assertNumericValue($right);

                $assignValue = $curVal - $right;

                $this->dataRetriever->setRuntimeValue(
                    $varName,
                    $this->data,
                    $assignValue
                );

                $lastPath = $this->dataRetriever->lastPath();

                if (count($varName->pathParts) > 1) {
                    $assignValue = $this->dataRetriever->getData($varName->getRoot(), $this->data);
                    $lastPath = $varName->pathParts[0]->name;
                }

                if (! in_array($lastPath, $this->protectedScopes)) {
                    $this->assignments[$lastPath] = $assignValue;
                }

                return null;
            } elseif ($operand instanceof ConditionalVariableFallbackOperator) {
                $leftValue = $this->getFallbackComparisonValue($left);

                if ($leftValue instanceof ArrayableString) {
                    $leftValue = $leftValue->value();
                }

                if ($leftValue != false) {
                    return $this->getValue($rightNode);
                } else {
                    return null;
                }
            }
        } else {
            throw ErrorFactory::makeRuntimeError(
                AntlersErrorCodes::TYPE_RUNTIME_UNEXPECTED_STACK_CONDITION,
                $this->lastNode,
                'Unexpected stack condition encountered.'
            );
        }

        return $stack;
    }

    /**
     * Evaluates the provided null coalescence group.
     *
     * @return mixed|DirectionGroup|ListValueNode|string
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    private function evaluateNullCoalescence(NullCoalescenceGroup $group)
    {
        $leftVal = $this->getValue($group->left);

        if ($leftVal instanceof ArrayableString) {
            $leftVal = $leftVal->value();
        }

        if ($leftVal != null) {
            return $leftVal;
        }

        return $this->getValue($group->right);
    }

    /**
     * Evaluates the provided ternary condition group.
     *
     * @param  TernaryCondition  $condition  The ternary expression.
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    private function evaluateTernaryGroup(TernaryCondition $condition)
    {
        $env = new Environment();
        $env->setProcessor($this->nodeProcessor);
        $env->setData($this->data);
        $headValue = $env->evaluateBool($condition->head);

        if ($headValue == true) {
            return $this->getValue($condition->truthBranch);
        }

        return $this->getValue($condition->falseBranch);
    }

    /**
     * Returns the numeric value associated with the provided variable name.
     *
     * @param  string|VariableReference  $name  The variable name.
     * @return array|ArrayAccess|int|mixed|string
     *
     * @throws RuntimeException
     */
    private function numericScopeValue($name)
    {
        $value = $this->scopeValue($name);

        return $this->convertToRuntimeNumeric($value, $name);
    }

    private function convertToRuntimeNumeric($value, $name)
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($value == null) {
            if ($name instanceof VariableReference) {
                return 0;
            }

            $this->data[$name] = 0;

            return 0;
        }

        return $value;
    }

    /**
     * Returns the current value associated with the provided variable name.
     *
     * @param  string|VariableReference  $name  The variable name.
     * @param  AbstractNode|null  $originalNode  The original node, if available.
     * @return array|ArrayAccess|mixed|string|null
     *
     * @throws RuntimeException
     */
    private function scopeValue($name, $originalNode = null)
    {
        ModifierManager::clearModifierState();

        if (! empty(GlobalRuntimeState::$prefixState)) {
            $this->dataRetriever->setHandlePrefixes(array_reverse(GlobalRuntimeState::$prefixState));
        }

        if ($name instanceof VariableReference) {
            if (! $this->isEvaluatingTruthValue) {
                $this->dataRetriever->setReduceFinal(false);
            } else {
                $this->dataRetriever->setIsReturningForConditions(true);
            }

            if ($originalNode != null && $originalNode->hasModifiers()) {
                $doIntercept = $this->dataRetriever->getShouldDoValueIntercept();

                $this->dataRetriever->setShouldDoValueIntercept(false);
                $value = $this->dataRetriever->getData($name, $this->data);
                $this->dataRetriever->setShouldDoValueIntercept($doIntercept);

                return $value;
            }

            return $this->dataRetriever->getData($name, $this->data);
        }

        return Arr::get($this->data, $name);
    }

    /**
     * Returns the variable name associated with the provided reference.
     *
     * @param  AbstractNode  $node  The variable node.
     * @return mixed|VariableReference|string|null
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     */
    private function nameOf($node)
    {
        if ($node instanceof VariableNode) {
            if ($node->variableReference == null) {
                $node->variableReference = $this->pathParser->parse($node->name);
            }

            if ($node->variableReference != null) {
                return $node->variableReference;
            }

            return $node->name;
        }

        throw ErrorFactory::makeRuntimeError(
            AntlersErrorCodes::TYPE_RUNTIME_ASSIGNMENT_TO_NON_VAR,
            $node,
            'Cannot assign value to type ['.TypeLabeler::getPrettyTypeName($node).'].'
        );
    }

    /**
     * Resolves the value appropriately for a variable fallback expression.
     *
     * @param  AbstractNode|mixed  $val  The value to resolve.
     * @return bool|float|int|mixed|DirectionGroup|ListValueNode|string|null
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    private function getFallbackComparisonValue($val)
    {
        $checkVal = $val;

        if ($checkVal === false) {
            return null;
        }

        return $this->getValue($checkVal);
    }

    /**
     * Applies a modifier chain to a value.
     *
     * @param  AbstractNode|VariableReference|mixed  $value  The variable.
     * @param  ModifierChainNode  $modifierChain  The modifier chain.
     * @return mixed
     */
    private function applyModifiers($value, ModifierChainNode $modifierChain)
    {
        return ModifierManager::evaluate($value, $this, $modifierChain, $this->data);
    }

    /**
     * Adjusts the value based on the current environment state.
     *
     * @param  mixed  $value  The value to adjust.
     * @param  AbstractNode  $originalNode  The original node.
     * @return mixed|string
     */
    private function adjustValue($value, $originalNode)
    {
        if ($originalNode instanceof AbstractNode && $originalNode->modifierChain != null) {
            if (! empty($originalNode->modifierChain->modifierChain)) {
                $value = $this->checkForFieldValue($value, true, $originalNode->modifierChain->modifierChain);

                return $this->applyModifiers($value, $originalNode->modifierChain);
            }
        }

        if (! empty($this->interpolationReplacements) && is_string($value)) {
            if (Str::contains($value, $this->interpolationKeys)) {
                $value = strtr($value, $this->interpolationReplacements);
            }
        }

        return $this->checkForFieldValue($value);
    }

    private function checkForFieldValue($value, $hasModifiers = false, $modifierChain = null)
    {
        if ($value instanceof Value) {
            GlobalRuntimeState::$isEvaluatingUserData = true;
            if ($value->shouldParseAntlers()) {
                if (! $hasModifiers || ($modifierChain != null && $modifierChain[0]->nameNode->name != 'raw')) {
                    GlobalRuntimeState::$userContentEvalState = [
                        $value,
                        $this->nodeProcessor->getActiveNode(),
                    ];
                    $value = $value->antlersValue($this->nodeProcessor->getAntlersParser(), $this->data);
                    GlobalRuntimeState::$userContentEvalState = null;
                }
            } else {
                if (! $hasModifiers) {
                    $value = $value->value();
                }
            }
            GlobalRuntimeState::$isEvaluatingUserData = false;
        }

        return $value;
    }

    /**
     * Tests if the provided node represents an ascending direction instruction.
     *
     * @param  AbstractNode  $node  The node to evaluate.
     * @return bool
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    private function getIsAscendingDirection($node)
    {
        if ($node instanceof StringValueNode) {
            return $node->value == 'asc';
        } elseif ($node instanceof TrueConstant) {
            return true;
        } elseif ($node instanceof FalseConstant) {
            return false;
        } elseif ($node instanceof NullConstant) {
            return false;
        } elseif ($node instanceof NumberNode) {
            return $node->value >= 1;
        }

        $env = new Environment();
        $env->setProcessor($this->nodeProcessor);
        $env->setData($this->data);
        $envResult = $env->evaluate([$node]);

        if (is_string($envResult)) {
            if ($envResult == 'asc') {
                return true;
            }

            return false;
        } elseif (is_bool($envResult)) {
            return $envResult;
        }

        throw ErrorFactory::makeRuntimeError(
            AntlersErrorCodes::TYPE_UNEXPECTED_RUNTIME_RESULT_FOR_ORDER_BY_CLAUSE,
            $node,
            'Unexpected return value ['.TypeLabeler::getPrettyRuntimeTypeName($envResult).'] when evaluating order by direction.'
        );
    }

    /**
     * Evaluates the provided direction group.
     *
     * @param  DirectionGroup  $group  The group.
     * @return array
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function evaluateDirectionGroup(DirectionGroup $group)
    {
        $orders = [];

        foreach ($group->orderClauses as $clause) {
            $orders[] = [
                'var' => $clause->name,
                'asc' => $this->getIsAscendingDirection($clause->directionNode),
            ];
        }

        return $orders;
    }

    /**
     * Evaluates the provided argument group's inner values.
     *
     * @param  ArgumentGroup  $argumentGroup  The argument group.
     * @return array
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function evaluateArgumentGroup(ArgumentGroup $argumentGroup)
    {
        $env = new Environment();
        $env->setProcessor($this->nodeProcessor);
        $env->setData($this->data);

        $normalArgs = [];

        foreach ($argumentGroup->args as $arg) {
            if ($arg instanceof NamedArgumentNode) {
                // if ($arg->name instanceof VariableNode) {
                // TODO: Determine if this system is still useful in other areas.
                // $namedArgs[$arg->name->name] = $env->evaluate([$arg->value]);
                // }
            } else {
                $normalArgs[] = $env->evaluate([$arg]);
            }
        }

        return $normalArgs;
    }

    /**
     * Converts an array node to its actual values.
     *
     * @param  ArrayNode  $array  The array representation.
     * @return array|bool[]|mixed|string[]
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    private function resolveArrayValue(ArrayNode $array)
    {
        $runtimeArray = [];

        foreach ($array->nodes as $node) {
            if ($node instanceof ArrayNode) {
                $runtimeArray[] = $this->resolveArrayValue($node);
            } else {
                if ($node->name == null) {
                    $runtimeArray[] = $this->getValue($node->value);
                } else {
                    $key = $this->getValue($node->name);
                    $value = $this->getValue($node->value);

                    $runtimeArray = $runtimeArray + [$key => $value];
                }
            }
        }

        return $runtimeArray;
    }

    /**
     * Retrieves the runtime value for the provided value.
     *
     * @param  mixed  $val  The value.
     * @return array|bool|float|int|mixed|DirectionGroup|ListValueNode|string
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws VariableAccessException
     */
    public function getValue($val)
    {
        if ($val instanceof DirectionGroup || $val instanceof ListValueNode || $val instanceof FieldsNode) {
            return $val;
        }

        if ($val instanceof SwitchGroup) {
            return $val;
        }

        if ($val instanceof OperatorNodeContract ||
            $val instanceof LogicalNegationOperator || $val instanceof LogicGroupEnd) {
            throw ErrorFactory::makeRuntimeError(
                AntlersErrorCodes::TYPE_RUNTIME_PARSE_VALUE_VIOLATION,
                $this->lastNode,
                'Unexpected parser type ['.get_class($val).'] encountered.'
            );
        }

        $returnVal = $val;

        if ($val instanceof NumberNode) {
            $returnVal = $val->value;
        } elseif ($val instanceof LogicGroup) {
            $condRestore = $this->isEvaluatingTruthValue;
            $this->isEvaluatingTruthValue = false;
            $returnVal = $this->process($val->nodes);
            $this->isEvaluatingTruthValue = $condRestore;
        } elseif ($val instanceof SemanticGroup) {
            $condRestore = $this->isEvaluatingTruthValue;
            $this->isEvaluatingTruthValue = false;
            $returnVal = $this->process($val->nodes);
            $this->isEvaluatingTruthValue = $condRestore;
        } elseif ($val instanceof VariableNode) {
            $varName = $this->nameOf($val);

            if ($val->isInterpolationReference) {
                if (array_key_exists($varName->normalizedReference, $this->data)) {
                    $interpolationValue = $this->adjustValue($this->data[$varName->normalizedReference], $val);
                } else {
                    $interpolationValue = $this->adjustValue($this->nodeProcessor->reduceInterpolatedVariable($val), $val);
                }

                // If the currently active node is an instance of ArithmeticNodeContract,
                // we will ask the runtime type coercion to convert whatever value
                // comes from the interpolation result into its most likely
                // data type; as values from interpolation are strings.
                if ($this->lastNode instanceof ArithmeticNodeContract) {
                    $interpolationValue = TypeCoercion::coerceType($interpolationValue);
                }

                return $interpolationValue;
            }

            $scopeValue = $this->scopeValue($varName, $val);

            if ($scopeValue instanceof Collection && ! $val->hasModifiers()) {
                $scopeValue = $scopeValue->all();
            }

            return $this->adjustValue($scopeValue, $val);
        } elseif ($val instanceof TrueConstant) {
            $returnVal = true;
        } elseif ($val instanceof FalseConstant) {
            $returnVal = false;
        } elseif ($val instanceof NullConstant) {
            $returnVal = null;
        } elseif ($val instanceof StringValueNode) {
            if (Str::contains($val->value, GlobalRuntimeState::$interpolatedVariables)) {
                $stringValue = $val->value;

                foreach ($this->dataManagerInterpolations as $regionName => $region) {
                    if (Str::contains($val->value, $regionName)) {
                        $tempValue = $this->nodeProcessor->evaluateDeferredInterpolation(trim($regionName));
                        if (is_string($tempValue) || (is_object($tempValue) && method_exists($tempValue, '__toString'))) {
                            $stringValue = str_replace($regionName, (string) $tempValue, $stringValue);
                        }
                    }
                }

                $returnVal = $stringValue;
            } else {
                $returnVal = DocumentParser::applyEscapeSequences($val->value);
            }
        } elseif ($val instanceof NullCoalescenceGroup) {
            $returnVal = $this->evaluateNullCoalescence($val);
        } elseif ($val instanceof TernaryCondition) {
            $returnVal = $this->evaluateTernaryGroup($val);
        } elseif ($val instanceof ModifierValueNode) {
            if (is_string($val->value) && in_array(trim($val->value), GlobalRuntimeState::$interpolatedVariables)) {
                return DocumentParser::applyEscapeSequences($this->nodeProcessor->evaluateDeferredInterpolation(trim($val->value)));
            }

            $returnVal = DocumentParser::applyEscapeSequences($val->value);
        } elseif ($val instanceof ArrayNode) {
            $returnVal = $this->resolveArrayValue($val);
        }

        if ($val instanceof ViewErrorBag) {
            if ($this->isEvaluatingTruthValue) {
                return (bool) $val->getBags();
            } else {
                return $val->toArray();
            }
        }

        if ($val instanceof MessageBag) {
            if ($this->isEvaluatingTruthValue) {
                return $val->count() > 0;
            } else {
                $val->toArray();
            }
        }

        if (is_array($val) && $this->isEvaluatingTruthValue) {
            return ! empty($val);
        }

        if (is_string($val) && $this->isEvaluatingTruthValue) {
            return mb_strlen($val) > 0;
        }

        if ($returnVal instanceof AbstractNode) {
            return $this->getValue($returnVal);
        }

        return $this->adjustValue($returnVal, $val);
    }
}
