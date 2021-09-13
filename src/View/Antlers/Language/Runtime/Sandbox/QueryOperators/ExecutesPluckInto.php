<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\QueryOperators;

use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\TupleScopedLogicGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;

trait ExecutesPluckInto
{
    /**
     * Takes data from the right operand and inserts into the left operand.
     *
     * This operator will iterate each element of the array value on the left,
     * for each element in the left array, it will check each element in
     * the array on the right, and apply the rule predicate provided
     * by the developer. If the predicate evaluates to "true",
     * the matching element from the right will be added to
     * a new associative array value in the left array.
     *
     * @param  array  $data  The data to get items from.
     * @param  TupleScopedLogicGroup  $pluckInstructions  The retrieval instructions.
     * @param  AbstractNode  $pluckTarget  The target of the pluckInto operation.
     * @param  array  $context  The context data.
     * @return array
     *
     * @throws RuntimeException
     */
    protected function executePluckInto($data, TupleScopedLogicGroup $pluckInstructions, $pluckTarget, $context)
    {
        $targetVarName = $pluckInstructions->target->name;
        $dataTarget = $pluckInstructions->target;
        $item1Scope = '';
        $item2Scope = '';

        if ($pluckInstructions->isDynamicNames) {
            $item2Scope = $pluckInstructions->target->name;

            if ($pluckTarget instanceof VariableNode) {
                $item1Scope = $pluckTarget->name;
            } else {
                throw ErrorFactory::makeRuntimeError(
                    AntlersErrorCodes::TYPE_PLUCK_INTO_REFERENCE_TYPE_DYNAMIC,
                    $pluckTarget,
                    'Left operand must be of type [T_VAR] when using dynamic scopes.'
                );
            }
        } else {
            $item1Scope = $pluckInstructions->item1->name;
            $item2Scope = $pluckInstructions->item2->name;
        }

        if ($item2Scope == $item1Scope) {
            throw ErrorFactory::makeRuntimeError(
                AntlersErrorCodes::TYPE_PLUCK_INTO_REFERENCE_AMBIGUOUS,
                $pluckTarget,
                'Ambiguous "'.$item1Scope.'" variable scope encountered.'
            );
        }

        $processNodes = $pluckInstructions->nodes;

        $env = $this->makeEnvironment();
        $evalDataBase = array_merge([], $context);

        $dataManager = new PathDataManager();
        $queryTarget = $dataManager->getData($dataTarget->variableReference, $context);

        if ($pluckInstructions->name != null && $pluckInstructions->name instanceof StringValueNode) {
            $targetVarName = $pluckInstructions->name->value;
        }

        return collect($data)->map(function ($item) use ($evalDataBase, $env, $item1Scope,
            $item2Scope, $processNodes, $targetVarName, $queryTarget) {
            $tempVar = [];
            $evalDataBase[$item1Scope] = $item;

            foreach ($queryTarget as $target) {
                $evalDataBase[$item2Scope] = $target;
                $env->setData($evalDataBase);
                $result = $env->evaluate($processNodes);

                if ($result == true) {
                    $tempVar[] = $target;
                }
            }

            $item[$targetVarName] = $tempVar;
            $item[$targetVarName.'_count'] = count($tempVar);

            return $item;
        })->values()->toArray();
    }
}
