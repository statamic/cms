<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators;

use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\Structures\AliasedScopeLogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ListValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ScopedLogicGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

trait ExecutesGroupyBy
{
    /**
     * Groups the provided $data according to the rules specified in $groups.
     *
     * @param  array  $data  The data to group.
     * @param  ListValueNode  $groups  The grouping instructions.
     * @param  array  $context  The context data.
     * @return array
     */
    protected function executeGroupBy($data, ListValueNode $groups, $context)
    {
        $valuesName = 'values';
        $valueCountName = 'values_count';

        if ($groups->isNamedNode) {
            $valuesName = $groups->parsedName->value;
            $valueCountName = $valuesName.'_count';
        }

        $env = $this->makeEnvironment();
        $env->setData($context);

        if (count($groups->values) == 1) {
            $expression = $groups->values[0];
            $keyName = 'key';
            $scopeName = null;

            if ($expression instanceof AliasedScopeLogicGroup) {
                $keyName = $expression->alias->value;
                $scopeName = $expression->scope->name;
            }

            if ($expression  instanceof ScopedLogicGroup) {
                $scopeName = $expression->scope->name;
            }

            if ($expression instanceof LogicGroup || $expression instanceof ScopedLogicGroup || $expression instanceof AliasedScopeLogicGroup) {
                $expression = $expression->nodes;
            }

            if (! is_array($expression)) {
                $expression = [$expression];
            }

            $dataGroups = collect($data)->groupBy(function ($item) use ($env, $context, $expression, $scopeName) {
                if (! is_array($item)) {
                    $item = PathDataManager::reduce($item);
                }

                if ($scopeName == null) {
                    $evalData = array_merge($context, $item);
                } else {
                    $evalData = array_merge([], $context);
                    $evalData[$scopeName] = $item;
                }

                $env->setData($evalData);
                $groupVal = $env->evaluate($expression);

                if (is_string($groupVal)) {
                    $tempVResult = $this->getVarNode($groupVal);

                    if ($tempVResult instanceof VariableReference) {
                        $groupVal = $this->getQueryValue($tempVResult, $item, $groupVal);
                    }
                }

                return $groupVal;
            })->all();

            $returnValues = [];

            foreach ($dataGroups as $key => $group) {
                $groupValues = collect($group)->values()->all();
                $returnValues[] = [
                    $keyName => $key,
                    $valuesName => $groupValues,
                    $valueCountName => count($groupValues),
                ];
            }

            return $returnValues;
        } else {
            $groupProps = $groups->values;

            $keyValues = [];
            $returnValues = [];


            $multiGrouped = collect($data)->groupBy(function ($item) use ($groupProps, &$keyValues, $env, $context) {
                if (! is_array($item)) {
                    $item = PathDataManager::reduce($item);
                }

                $nonScopedData = array_merge($context, $item);
                $evalData = array_merge([], $context);

                $itemSlug = '';
                $values = [];
                $dynamicKeyCount = 0;

                foreach ($groupProps as $prop) {
                    $propName = '';
                    $expression = [];
                    $scopeName = null;

                    if ($prop instanceof VariableNode && $prop->variableReference != null) {
                        $propName = $prop->name;
                        $expression = [$prop];
                    } elseif ($prop instanceof AliasedScopeLogicGroup) {
                        $propName = $prop->alias->value;
                        $scopedDetails = $prop->extract();
                        $expression = $scopedDetails[1];
                        $scopeName = $scopedDetails[0];
                    } elseif ($prop instanceof ScopedLogicGroup) {
                        $scopedDetails = $prop->extract();
                        $expression = $scopedDetails[1];
                        $scopeName = $scopedDetails[0];

                        if (count($expression) == 1 && $expression[0] instanceof VariableNode) {
                            $varNode = $expression[0];

                            if ($varNode->variableReference != null && $varNode->variableReference->pathParts[0]->name == $scopedDetails[0]) {
                                $refName = PathParser::normalizePath(StringUtilities::substr($varNode->name, mb_strlen($scopedDetails[0]) + 1));

                                $propName = $refName;
                            } else {
                                $dynamicKeyCount += 1;
                                $propName = 'key_'.$dynamicKeyCount;
                            }
                        } else {
                            $dynamicKeyCount += 1;
                            $propName = 'key_'.$dynamicKeyCount;
                        }
                    } else {
                        $dynamicKeyCount += 1;
                        $propName = 'key_'.$dynamicKeyCount;
                    }

                    if ($scopeName == null) {
                        $env->setData($nonScopedData);
                    } else {
                        $scopeData = $evalData;
                        $scopeData[$scopeName] = $item;
                        $env->setData($scopeData);
                    }

                    $groupVal = $env->evaluate($expression);

                    if (is_string($groupVal)) {
                        $tempVResult = $this->getVarNode($groupVal);

                        if ($tempVResult instanceof VariableReference) {
                            $groupVal = $this->getQueryValue($tempVResult, $item, $groupVal);
                        }
                    }

                    $values[$propName] = $groupVal;
                    $itemSlug .= (string) $groupVal;
                }

                $keyValues[$itemSlug] = $values;

                return $itemSlug;
            })->all();

            foreach ($multiGrouped as $key => $group) {
                $groupValues = collect($group)->values()->all();

                $returnValues[] = [
                    'key' => $keyValues[$key],
                    $valuesName => $groupValues,
                    $valueCountName => count($groupValues),
                ];
            }

            return $returnValues;
        }
    }
}
