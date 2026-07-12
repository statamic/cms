<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators;

use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\Structures\DirectionGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ScopedLogicGroup;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;

trait ExecutesOrderBy
{
    /**
     * Groups the provided $data according to the order instructions in $orderGroup.
     *
     * @param  array  $data  The target data.
     * @param  DirectionGroup  $orderGroup  The orders to apply.
     * @param  array  $context  The context data.
     * @return array
     */
    protected function executeOrderBy($data, DirectionGroup $orderGroup, $context)
    {
        $env = $this->makeEnvironment();
        $env->setData($context);

        $evaluatedGroups = $env->evaluateDirectionGroup($orderGroup);

        if (count($evaluatedGroups) == 1) {
            $firstClause = $evaluatedGroups[0];
            $expression = $firstClause['var'];
            $isAsc = $firstClause['asc'];

            $scopeName = null;

            if ($expression instanceof ScopedLogicGroup) {
                $scopedDetails = $expression->extract();
                $scopeName = $scopedDetails[0];
                $expression = $scopedDetails[1];
            }

            if (is_array($expression) == false) {
                $expression = [$expression];
            }

            if ($isAsc) {
                $data = collect($data)->sortBy(function ($item) use ($expression, $scopeName, $env, $context) {
                    if (! is_array($item)) {
                        $item = PathDataManager::reduce($item);
                    }

                    $evalData = [];

                    if ($scopeName != null) {
                        $evalData = array_merge([], $context);
                        $evalData[$scopeName] = $item;
                    } else {
                        $evalData = array_merge($context, $item);
                    }

                    $env->setData($evalData);

                    $evalValue = $env->evaluate($expression);

                    if (is_string($evalValue)) {
                        $tempVResult = $this->getVarNode($evalValue);

                        if ($tempVResult instanceof VariableReference) {
                            return $this->getQueryValue($tempVResult, $item, $evalValue);
                        }
                    }

                    return $evalValue;
                })->all();
            } else {
                $data = collect($data)->sortByDesc(function ($item) use ($expression, $scopeName, $env, $context) {
                    if (! is_array($item)) {
                        $item = PathDataManager::reduce($item);
                    }

                    $evalData = [];

                    if ($scopeName != null) {
                        $evalData = array_merge([], $context);
                        $evalData[$scopeName] = $item;
                    } else {
                        $evalData = array_merge($context, $item);
                    }

                    $env->setData($evalData);

                    $evalValue = $env->evaluate($expression);

                    if (is_string($evalValue)) {
                        $tempVResult = $this->getVarNode($evalValue);

                        if ($tempVResult instanceof VariableReference) {
                            return $this->getQueryValue($tempVResult, $item, $evalValue);
                        }
                    }

                    return $evalValue;
                })->all();
            }
        } elseif (count($evaluatedGroups) > 1) {
            $data = collect($data)->sort(function ($a, $b) use ($evaluatedGroups, $context, $env) {
                $aData = array_merge([], $context);
                $bData = array_merge([], $context);

                foreach ($evaluatedGroups as $valGroup) {
                    $expression = $valGroup['var'];
                    $isAsc = $valGroup['asc'];

                    if ($expression instanceof ScopedLogicGroup) {
                        $scopedDetails = $expression->extract();
                        $scopeName = $scopedDetails[0];
                        $expression = $scopedDetails[1];

                        $aData[$scopeName] = $a;
                        $bData[$scopeName] = $b;
                    } else {
                        $aData = array_merge($a, $aData);
                        $bData = array_merge($b, $bData);
                    }

                    if (is_array($expression) == false) {
                        $expression = [$expression];
                    }

                    $env->setData($aData);
                    $cmpA = $env->evaluate($expression);
                    $env->setData($bData);
                    $cmpB = $env->evaluate($expression);

                    if (is_string($cmpA)) {
                        $tempVResult = $this->getVarNode($cmpA);

                        if ($tempVResult instanceof VariableReference) {
                            $cmpA = $this->getQueryValue($tempVResult, $a, $cmpA);
                        }
                    }

                    if (is_string($cmpB)) {
                        $tempVResult = $this->getVarNode($cmpB);

                        if ($tempVResult instanceof VariableReference) {
                            $cmpB = $this->getQueryValue($tempVResult, $b, $cmpB);
                        }
                    }

                    if ($isAsc) {
                        $x = ($cmpA <=> $cmpB);
                    } else {
                        $x = ($cmpB <=> $cmpA);
                    }

                    if ($x != 0) {
                        return $x;
                    }
                }

                return 0;
            })->toArray();
        }

        return array_values($data);
    }
}
