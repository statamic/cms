<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox;

use Exception;
use Illuminate\Support\Arr;
use Statamic\Support\Str;
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
use Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators\ExecutesPluckInto;
use Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators\ExecutesWhere;

class LanguageOperatorManager
{
    use ExecutesPluckInto, ExecutesOrderBy,
        ExecutesGroupBy, ExecutesWhere;

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

    private function unwrapValueArrays($array)
    {
        if ($array == null) {
            return [];
        }

        if (count($array) == 0) {
            return $array;
        }

        if (is_array($array[0])) {
            if (array_key_exists('value', $array[0])) {
                $values = [];

                foreach ($array as $item) {
                    $values[] = $item['value'];
                }

                return $values;
            }
        }

        return $array;
    }

    public function evaluateOperator($operator, $a, $b, $rawA, $rawB, $context)
    {
        $value = null;

        if ($operator == LanguageOperatorRegistry::STR_STARTS_WITH) {
            $value = Str::startsWith($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_ENDS_WITH) {
            $value = Str::endsWith($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_IS) {
            $value = Str::is($b, $a);
        } elseif ($operator == LanguageOperatorRegistry::STR_IS_URL) {
            $value = Str::isUrl($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_CONTAINS) {
            $value = Str::contains($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_CONTAINS_ALL) {
            $value = Str::containsAll($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::ARR_HAS_ANY) {
            $value = Arr::hasAny(self::unwrapValueArrays($a), $b);
        } elseif ($operator == LanguageOperatorRegistry::ARR_HAS) {
            $value = Arr::has(self::unwrapValueArrays($a), $b);
        } elseif ($operator == LanguageOperatorRegistry::ARR_CONTAINS) {
            $value = in_array($b, self::unwrapValueArrays($a));
        } elseif ($operator == LanguageOperatorRegistry::ARR_CONTAINS_ANY) {
            if ($a === null) {
                $value = false;
            } else {
                $a = self::unwrapValueArrays($a);

                $value = ! empty(array_intersect($a, $b));
            }
        } elseif ($operator == LanguageOperatorRegistry::ARR_PLUCK) {
            $tmpValue = Arr::pluck($a, $b);
            $value = [];

            foreach ($tmpValue as $pluckedItem) {
                $value[] = ['value' => $pluckedItem];
            }
        } elseif ($operator == LanguageOperatorRegistry::ARR_GET) {
            $value = Arr::pluck($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::ARR_SORT) {
            $value = Arr::sort($a);
        } elseif ($operator == LanguageOperatorRegistry::ARR_TAKE) {
            $value = collect($a)->take($b)->toArray();
        } elseif ($operator == LanguageOperatorRegistry::ARR_RECURSIVE_SORT) {
            $value = Arr::sortRecursive($a);
        } elseif ($operator == LanguageOperatorRegistry::ARR_WRAP) {
            $value = Arr::wrap($a);
        } elseif ($operator == LanguageOperatorRegistry::ARR_MERGE) {
            $value = array_merge($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::ARR_CONCAT) {
            $value = $a + $b;
        } elseif ($operator == LanguageOperatorRegistry::DATA_GET) {
            $value = data_get($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_AFTER) {
            $value = Str::after($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_AFTER_LAST) {
            $value = Str::after($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_ASCII) {
            $value = Str::ascii($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_BEFORE_LAST) {
            $value = Str::beforeLast($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_BEFORE) {
            $value = Str::before($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_CAMEL) {
            $value = Str::camel($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_FINISH) {
            $value = Str::finish($a, $b);
        } elseif ($operator == LanguageOperatorRegistry::STR_KEBAB) {
            $value = Str::kebab($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_LENGTH) {
            $value = Str::length($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_LOWER) {
            $value = Str::lower($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_UPPER) {
            $value = Str::upper($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_SNAKE) {
            $value = Str::snake($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_STUDLY) {
            $value = Str::studly($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_TITLE) {
            $value = Str::title($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_UCFIRST) {
            $value = Str::ucfirst($a);
        } elseif ($operator == LanguageOperatorRegistry::STR_WORD_COUNT) {
            $value = \str_word_count($a);
        } elseif ($operator == LanguageOperatorRegistry::ARR_PLUCK_INTO) {
            $value = $this->executePluckInto($a, $rawB, $rawA, $context);
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
                return array_get($data, $node->name);
            }

            return data_get($data, $node->name);
        } elseif ($node instanceof StringValueNode) {
            if (is_array($data)) {
                return array_get($data, $node->value);
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
                return array_get($data, $node);
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
