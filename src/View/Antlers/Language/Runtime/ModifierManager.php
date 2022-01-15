<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Illuminate\Support\Facades\Log;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modify;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Nodes\Modifiers\ModifierChainNode;
use Statamic\View\Antlers\Language\Nodes\Parameters\ParameterNode;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;

class ModifierManager
{
    public static $statamicModifiers = null;
    const METHOD_MODIFIER = '{__method_args}';

    public static function isModifier(ParameterNode $node)
    {
        if (self::$statamicModifiers == null) {
            self::$statamicModifiers = app('statamic.modifiers')->all();
        }

        return array_key_exists($node->name, self::$statamicModifiers);
    }

    public static function guardRuntimeModifier($modifierName)
    {
        if (GlobalRuntimeState::$isEvaluatingUserData) {
            $guardList = GlobalRuntimeState::$bannedContentModifierPaths;
        } else {
            $guardList = GlobalRuntimeState::$bannedModifierPaths;
        }

        if (empty($guardList)) {
            return true;
        }

        if (Str::is($guardList, $modifierName)) {
            Log::warning('Runtime Access Violation: '.$modifierName, [
                'modifier' => $modifierName,
                'file' => GlobalRuntimeState::$currentExecutionFile,
                'trace' =>  GlobalRuntimeState::$templateFileStack,
            ]);

            if (GlobalRuntimeState::$throwErrorOnAccessViolation) {
                throw ErrorFactory::makeRuntimeError(
                    AntlersErrorCodes::RUNTIME_PROTECTED_MODIFIER_ACCESS,
                    null,
                    'Protected tag access.'
                );
            }

            return  false;
        }

        return true;
    }

    public static function evaluate($value, Environment $env, ModifierChainNode $modifierChain, $context)
    {
        if (count($modifierChain->modifierChain) == 0) {
            return $value;
        }

        $returnValue = $value;

        foreach ($modifierChain->modifierChain as $chain) {
            $modifierName = $chain->nameNode->name;

            if (! self::guardRuntimeModifier($modifierName)) {
                continue;
            }

            if ($modifierName === 'raw') {
                if ($returnValue instanceof Value) {
                    $returnValue = $returnValue->raw();
                }

                continue;
            }

            if ($modifierName === 'noparse') {
                if ($returnValue instanceof Value) {
                    $returnValue = $returnValue->value();
                }

                continue;
            }

            $parameters = [];

            if ($chain->methodStyleArguments != null) {
                $parameters = $env->evaluateArgumentGroup($chain->methodStyleArguments);
                $context[self::METHOD_MODIFIER] = true;
            } else {
                if (! empty($chain->valueNodes)) {
                    foreach ($chain->valueNodes as $node) {
                        $parameters[] = $env->getValue($node);
                    }
                }
            }

            if ($returnValue instanceof Value) {
                $returnValue = $value->value();
            }

            $returnValue = Modify::value($returnValue)
                ->context($context)->$modifierName($parameters)->fetch();
        }

        return $returnValue;
    }
}
