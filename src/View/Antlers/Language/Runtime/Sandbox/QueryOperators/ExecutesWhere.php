<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators;

use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\GreaterThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\LessThanEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\NotStrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\Operators\Comparison\StrictEqualCompOperator;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\ScopedLogicGroup;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Antlers\Language\Utilities\NodeHelpers;

trait ExecutesWhere
{
    protected function executeWhere($data, $predicate, $rawNode, $context)
    {
        if ($this->canRunOptimized($predicate)) {
            $propertyName = NodeHelpers::getSimpleVarName($predicate[0]);
            $checkValue = NodeHelpers::getSimpleNodeValue($predicate[2]);
            $compOperator = $this->nodeToCollectionFilter($predicate[1]);

            return collect($data)->where($propertyName, $compOperator, $checkValue)->values()->toArray();
        }

        $env = $this->makeEnvironment();
        $values = [];
        $scopeName = null;

        if ($rawNode instanceof ScopedLogicGroup && $rawNode->scope != null) {
            $scopeName = $rawNode->scope->name;
            // We are doing this here since we know ahead of time
            // what the scope variable name will be, so we can
            // save ourselves calls to array_merge later.
            $evalData = array_merge([], $context);

            foreach ($data as $aVal) {
                $evalData[$scopeName] = $aVal;

                $env->setData($evalData);
                $res = $env->evaluateBool($predicate);

                if ($res == true) {
                    $values[] = $aVal;
                }
            }
        } else {
            foreach ($data as $aVal) {
                if (! is_array($aVal)) {
                    $aVal = PathDataManager::reduce($aVal);
                }

                $evalData = array_merge($context, $aVal);

                $env->setData($evalData);
                $res = $env->evaluateBool($predicate);

                if ($res == true) {
                    $values[] = $aVal;
                }
            }
        }

        return array_values($values);
    }

    protected function canRunOptimized($predicate)
    {
        if (count($predicate) != 3) {
            return false;
        }

        if ($predicate[0] instanceof StringValueNode) {
            return true;
        }

        return false;
    }

    protected function nodeToCollectionFilter($node)
    {
        if ($node instanceof StrictEqualCompOperator) {
            return '===';
        } elseif ($node instanceof NotStrictEqualCompOperator) {
            return '!==';
        } elseif ($node instanceof NotEqualCompOperator) {
            return '!=';
        } elseif ($node instanceof LessThanCompOperator) {
            return '<';
        } elseif ($node instanceof LessThanEqualCompOperator) {
            return '<=';
        } elseif ($node instanceof GreaterThanCompOperator) {
            return '>';
        } elseif ($node instanceof GreaterThanEqualCompOperator) {
            return '>=';
        }

        return '==';
    }
}
