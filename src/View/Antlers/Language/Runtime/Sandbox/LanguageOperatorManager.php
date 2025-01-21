<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Exception;
use Illuminate\Support\Arr;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Nodes\Constants\FalseConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\NullConstant;
use Statamic\View\Antlers\Language\Nodes\Constants\TrueConstant;
use Statamic\View\Antlers\Language\Nodes\NumberNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchCase;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators\ExecutesGroupBy;
use Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators\ExecutesOrderBy;
use Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators\ExecutesWhere;

class LanguageOperatorManager
{
    use ExecutesGroupBy, ExecutesOrderBy, ExecutesWhere;

    /**
     * @var NodeProcessor|null
     */
    protected $hostProcessor = null;

    /**
     * @var Environment|null
     */
    protected $environment = null;

    /**
     * @var PathDataManager|null
     */
    protected $dataManager = null;

    protected static $parsedVarCache = [];
    protected static $varParser = null;

    public function __construct()
    {
        $this->dataManager = new PathDataManager();
    }

    public function setNodeProcessor(NodeProcessor $processor)
    {
        $this->hostProcessor = $processor;

        return $this;
    }

    public function resetNodeProcessor()
    {
        $this->hostProcessor = null;

        return $this;
    }

    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    public function evaluateOperator($operator, $a, $b, $rawA, $rawB, $context)
    {
        $value = null;

        if ($operator == LanguageOperatorRegistry::ARR_PLUCK) {
            if ($a === null) {
                return [];
            }

            $tmpValue = Arr::pluck($a, $b);
            $value = [];

            foreach ($tmpValue as $pluckedItem) {
                $value[] = $pluckedItem;
            }
        } elseif ($operator == LanguageOperatorRegistry::ARR_TAKE) {
            $value = collect($a)->take($b)->toArray();
        } elseif ($operator == LanguageOperatorRegistry::ARR_SKIP) {
            $value = collect($a)->skip($b)->toArray();
        } elseif ($operator == LanguageOperatorRegistry::ARR_MERGE) {
            $value = array_merge($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::QUERY_WHERE) {
            $predicate = $this->unpackQueryLogicGroup($rawB);
            $value = $this->executeWhere($a, $predicate, $rawB, $context);
        } elseif ($operator == LanguageOperatorRegistry::ARR_ORDERBY) {
            $value = $this->executeOrderBy($a, $b, $context);
        } elseif ($operator == LanguageOperatorRegistry::ARR_GROUPBY) {
            $value = $this->executeGroupBy($a, $b, $context);
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_AND) {
            $value = $a & $b;
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_OR) {
            $value = $a | $b;
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_XOR) {
            $value = $a ^ $b;
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_NOT) {
            $value = ~$a;
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_SHIFT_LEFT) {
            $value = $a << $b;
        } elseif ($operator == LanguageOperatorRegistry::BITWISE_SHIFT_RIGHT) {
            $value = $a >> $b;
        } elseif ($operator == LanguageOperatorRegistry::STRUCT_SWITCH) {
            /** @var SwitchGroup $group */
            $group = $b;

            /** @var SwitchCase $caseStatement */
            $caseCount = count($group->cases);

            for ($i = 0; $i < $caseCount; $i++) {
                /** @var SwitchCase $caseStatement */
                $caseStatement = $group->cases[$i];

                // An empty group in the last position is valid as a "default".
                if ($i == $caseCount - 1) {
                    if ($caseStatement->condition instanceof LogicGroup && count($caseStatement->condition->nodes) == 0) {
                        $value = $this->hostProcessor->evaluateDeferredLogicGroup($caseStatement->expression);
                        break;
                    }
                }

                if ($caseStatement->condition instanceof LogicGroup && count($caseStatement->condition->nodes) == 0) {
                    throw ErrorFactory::makeRuntimeError(
                        AntlersErrorCodes::TYPE_SWITCH_DEFAULT_MUST_BE_LAST,
                        $group,
                        'Default case statement must appear as the last condition.'
                    );
                }

                $test = $this->hostProcessor->evaluateDeferredLogicGroup($caseStatement->condition);

                if ($test == true) {
                    $value = $this->hostProcessor->evaluateDeferredLogicGroup($caseStatement->expression);
                    break;
                }
            }
        }

        return $value;
    }

    protected function unpackQueryLogicGroup(LogicGroup $group)
    {
        return $group->nodes[0]->nodes[0]->nodes;
    }

    /**
     * @return Environment
     */
    private function makeEnvironment()
    {
        $env = new Environment();
        $env->setProcessor($this->hostProcessor);

        return $env;
    }

    protected function getVarNode($string)
    {
        if (array_key_exists($string, self::$parsedVarCache) == false) {
            if (self::$varParser == null) {
                self::$varParser = new PathParser();
            }

            try {
                self::$parsedVarCache[$string] = self::$varParser->parse($string);
            } catch (Exception $e) {
                self::$parsedVarCache[$string] = $string;
            }
        }

        return self::$parsedVarCache[$string];
    }

    protected function getQueryValue($node, $data, $originalValue = '')
    {
        if ($node instanceof VariableReference) {
            $queryVal = $this->dataManager->getDataWithExistence($node, $data);

            if ($queryVal[0]) {
                return $queryVal[1];
            }

            return $originalValue;
        } elseif ($node instanceof VariableNode) {
            if ($node->variableReference != null) {
                $queryVal = $this->dataManager->getDataWithExistence($node->variableReference, $data);
                if ($queryVal[0]) {
                    return $queryVal[1];
                }

                return $originalValue;
            }

            if (is_array($data)) {
                return Arr::get($data, $node->name);
            }

            return data_get($data, $node->name);
        } elseif ($node instanceof StringValueNode) {
            if (is_array($data)) {
                return Arr::get($data, $node->value);
            }

            return data_get($data, $node->value);
        } elseif ($node instanceof TrueConstant) {
            return true;
        } elseif ($node instanceof FalseConstant) {
            return false;
        } elseif ($node instanceof NullConstant) {
            return null;
        } elseif ($node instanceof NumberNode) {
            return $node->value;
        }

        if (is_string($node)) {
            if (is_array($data)) {
                return Arr::get($data, $node);
            }

            return data_get($data, $node);
        }

        if (is_bool($node)) {
            return $node;
        }

        throw ErrorFactory::makeRuntimeError(
            AntlersErrorCodes::TYPE_QUERY_UNSUPPORTED_VALUE_TYPE,
            $node,
            'Unsupported query value type encountered.'
        );
    }
}
