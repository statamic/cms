<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries;

use Countable;
use Illuminate\Support\Collection;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Errors\TypeLabeler;
use Statamic\View\Antlers\Language\Exceptions\LibraryCallException;
use Statamic\View\Antlers\Language\Nodes\ArgumentGroup;
use Statamic\View\Antlers\Language\Nodes\NamedArgumentNode;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;

abstract class RuntimeLibrary
{
    const KEY_NAME = 'name';
    const KEY_HAS_DEFAULT = 'has_default';
    const KEY_DEFAULT = 'default';
    const KEY_ACCEPTS = 'accepts';
    const KEY_ACCEPTS_MANY = 'accepts_many';

    const KEY_TYPE_ARRAY = 'array';
    const KEY_TYPE_OBJECT = 'object';
    const KEY_TYPE_NUMERIC = 'numeric';
    const KEY_TYPE_STRING = 'string';
    const KEY_TYPE_BOOL = 'bool';
    const KEY_TYPE_ARG_BY_REF = 'by_ref';

    protected $name = '';
    protected $isRuntimeProtected = false;

    /**
     * @var Environment|null
     */
    protected $activeEnvironment = null;
    /**
     * @var ArgumentGroup|null
     */
    private $activeArgs = null;
    private $activeMethodName = '';

    /**
     * A list of methods this library exposes.
     *
     * This will be utilized instead of calls to method_exists
     * so that there are no accidental method leaks/calls.
     *
     * @var array[]
     */
    protected $exposedMethods = [];

    /**
     * Returns a list of all exposed library methods.
     *
     * @return array[]
     */
    public function getMethods()
    {
        return $this->exposedMethods;
    }

    public function getName()
    {
        return $this->name;
    }

    public function canBeExecutedByValueType()
    {
        return ! $this->isRuntimeProtected;
    }

    public function hasMethod($name)
    {
        return array_key_exists($name, $this->exposedMethods);
    }

    /**
     * Attempts to invoke a runtime library method.
     *
     * @param  string  $name  The method name.
     * @param  ArgumentGroup  $argumentGroup  Runtime argument group.
     * @param  Environment  $host  The executing environment.
     * @return mixed
     *
     * @throws LibraryCallException
     */
    public function invokeMethod($name, ArgumentGroup $argumentGroup, Environment $host)
    {
        $guardMethod = 'guard_'.$name;
        $this->activeArgs = $argumentGroup;
        $this->activeMethodName = $name;

        if (method_exists($this, $guardMethod)) {
            call_user_func([$this, $guardMethod], $argumentGroup);
        }

        $this->activeEnvironment = $host;

        $libraryMethodDefinition = $this->exposedMethods[$name];

        $acceptsMany = false;
        $lastParamAcceptsMany = false;

        if (is_array($libraryMethodDefinition)) {
            $argsWithoutDefault = [];
            $argsWithDefault = [];
            $argsWithReferenceValue = [];
            $totalDefinedParameters = count($libraryMethodDefinition);
            $allValidNamedArguments = [];
            $argPositionMapping = [];

            if ($totalDefinedParameters == 1 &&
                array_key_exists(self::KEY_ACCEPTS_MANY, $libraryMethodDefinition[0]) &&
                $libraryMethodDefinition[0][self::KEY_ACCEPTS_MANY]) {
                $acceptsMany = true;
            } elseif ($totalDefinedParameters > 1) {
                $lastParam = $libraryMethodDefinition[$totalDefinedParameters - 1];

                if (array_key_exists(self::KEY_ACCEPTS_MANY, $lastParam) && $lastParam[self::KEY_ACCEPTS_MANY] == true) {
                    $lastParamAcceptsMany = true;
                }
            }

            if (! $acceptsMany) {
                if (! $lastParamAcceptsMany) {
                    $this->assertMaxArgCount($totalDefinedParameters);
                }

                $argPosition = 0;

                foreach ($libraryMethodDefinition as $methodDef) {
                    if (array_key_exists(self::KEY_HAS_DEFAULT, $methodDef) &&
                        $methodDef[self::KEY_HAS_DEFAULT] == true) {
                        $argsWithDefault[$methodDef[self::KEY_NAME]] = $methodDef;
                        $allValidNamedArguments[] = $methodDef[self::KEY_NAME];
                    } else {
                        $argsWithoutDefault[$methodDef[self::KEY_NAME]] = $methodDef;

                        if (array_key_exists(self::KEY_TYPE_ARG_BY_REF, $methodDef) && $methodDef[self::KEY_TYPE_ARG_BY_REF] == true) {
                            $argsWithReferenceValue[$methodDef[self::KEY_NAME]] = $argPosition;
                        }
                    }

                    $argPosition += 1;
                }

                $requiredArv = array_values($argsWithoutDefault);

                $incomingArgCount = count($argumentGroup->args);
                $incomingUnnamedArgCount = $incomingArgCount - $argumentGroup->numberOfNamedArguments;
                $requiredArgCount = count($argsWithoutDefault);

                $incomingNamedArgumentNames = [];

                $agPosition = 0;

                foreach ($argumentGroup->args as $arg) {
                    if ($arg instanceof NamedArgumentNode) {
                        if ($arg->name instanceof VariableNode) {
                            $incomingNamedArgumentNames[] = $arg->name->name;
                        }
                    }

                    $argPositionMapping[$argPosition] = $arg;
                    $agPosition += 1;
                }

                if (! empty($incomingNamedArgumentNames)) {
                    foreach ($incomingNamedArgumentNames as $namedArgName) {
                        if (! in_array($namedArgName, $allValidNamedArguments)) {
                            throw ErrorFactory::makeLibraryError(
                                AntlersErrorCodes::TYPE_LIBRARY_CALL_INVALID_ARGUMENT_NAME,
                                'Unknown parameter name provided: '.$namedArgName
                            );
                        }
                    }
                }

                if ($incomingUnnamedArgCount < count($argsWithoutDefault)) {
                    if ($incomingUnnamedArgCount == 0) {
                        $error = $name.' expects at least '.$requiredArgCount.' '.ErrorFactory::pluralParameters($requiredArgCount).' ; none provided.';

                        throw ErrorFactory::makeLibraryError(
                            AntlersErrorCodes::TYPE_LIBRARY_CALL_NO_ARGS_PROVIDED,
                            $error
                        );
                    }

                    $requiredArgumentNames = array_keys($argsWithoutDefault);
                    $missingArgumentNames = array_slice($requiredArgumentNames, 1);
                    $missingNamesToReport = [];

                    if ($incomingNamedArgumentNames == 0) {
                        $missingNamesToReport = $missingArgumentNames;
                    } else {
                        foreach ($missingArgumentNames as $argName) {
                            if (! in_array($argName, $incomingNamedArgumentNames)) {
                                $missingNamesToReport[] = $argName;
                            }
                        }
                    }

                    if (! empty($missingNamesToReport)) {
                        $error = $name.
                            ' requires at least '.count($argsWithoutDefault).' '.ErrorFactory::pluralParameters($requiredArgCount).' ; exactly '.$incomingArgCount.' provided. Missing arguments for formal '.ErrorFactory::pluralParameters(count($missingNamesToReport)).': '.
                            Str::makeSentenceList($missingNamesToReport).'.';

                        throw ErrorFactory::makeLibraryError(
                            AntlersErrorCodes::TYPE_LIBRARY_CALL_MISSING_REQUIRED_FORMAL_ARG,
                            $error
                        );
                    }
                }

                $hostArgs = $host->evaluateArgumentGroup($argumentGroup);
                $resolvedArgs = $hostArgs[LibraryManager::ARG_NORMAL];

                if ($lastParamAcceptsMany) {
                    $lastArg = array_splice($resolvedArgs, $totalDefinedParameters - 1);
                    $resolvedArgs[] = $lastArg;
                }

                if ($argumentGroup->numberOfNamedArguments == 0) {
                    if ($lastParamAcceptsMany) {
                        $incomingArgCount = count($resolvedArgs);
                    }
                    // In this trivial scenario, we append all default argument values to the resolved list.
                    if ($incomingArgCount == $requiredArgCount) {
                        for ($i = 0; $i < $requiredArgCount; $i++) {
                            $this->assertValidRuntimeValue($resolvedArgs[$i], $requiredArv[$i]);
                        }

                        foreach ($argsWithDefault as $arg) {
                            $resolvedArgs[] = $arg[self::KEY_DEFAULT];
                        }

                        $evalArgs = [];
                        $valuesToBubble = [];

                        for ($i = 0; $i < count($resolvedArgs); $i++) {
                            if (in_array($i, $argsWithReferenceValue)) {
                                $argRefName = $argumentGroup->args[$i];

                                $valuesToBubble[] = [$argRefName, &$resolvedArgs[$i]];
                                $evalArgs[] = &$resolvedArgs[$i];
                            } else {
                                $evalArgs[] = $resolvedArgs[$i];
                            }
                        }

                        $executionResult = call_user_func_array([$this, $name], $evalArgs);

                        if (! empty($valuesToBubble)) {
                            $host->pushReferenceAssignments($valuesToBubble);
                        }

                        return $executionResult;
                    } else {
                        // In this situation, we have trailing arguments with
                        // defaults that we need to tack onto the end.
                        $defaultsToAdd = array_slice($libraryMethodDefinition, count($resolvedArgs));

                        foreach ($defaultsToAdd as $paramDef) {
                            $resolvedArgs[] = $paramDef[self::KEY_DEFAULT];
                        }

                        if (count($resolvedArgs) != $totalDefinedParameters) {
                            throw ErrorFactory::makeLibraryError(
                                AntlersErrorCodes::TYPE_LIBRARY_CALL_UNEXPECTED_ARG_RESOLVE_FAULT,
                                'An unexpected runtime state was entered while preparing arguments for library method call: '.$name
                            );
                        }

                        $evalArgs = [];
                        $valuesToBubble = [];

                        for ($i = 0; $i < count($resolvedArgs); $i++) {
                            if (in_array($i, $argsWithReferenceValue)) {
                                $argRefName = $argumentGroup->args[$i];

                                $valuesToBubble[] = [$argRefName, &$resolvedArgs[$i]];
                                $evalArgs[] = &$resolvedArgs[$i];
                            } else {
                                $evalArgs[] = $resolvedArgs[$i];
                            }
                        }

                        $executionResult = call_user_func_array([$this, $name], $evalArgs);

                        if (! empty($valuesToBubble)) {
                            $host->pushReferenceAssignments($valuesToBubble);
                        }

                        return $executionResult;
                    }
                } else {
                    $resolvedNamedArgs = $hostArgs[LibraryManager::ARG_NAMED];

                    // Reorder the named arguments to be in the correct order.
                    foreach ($argsWithDefault as $argName => $details) {
                        if (array_key_exists($argName, $resolvedNamedArgs)) {
                            $resolvedArgs[] = $resolvedNamedArgs[$argName];
                        } else {
                            $resolvedArgs[] = $details[self::KEY_DEFAULT];
                        }
                    }

                    $evalArgs = [];
                    $valuesToBubble = [];

                    for ($i = 0; $i < count($resolvedArgs); $i++) {
                        if (in_array($i, $argsWithReferenceValue)) {
                            $argRefName = $argumentGroup->args[$i];

                            $valuesToBubble[] = [$argRefName, &$resolvedArgs[$i]];
                            $evalArgs[] = &$resolvedArgs[$i];
                        } else {
                            $evalArgs[] = $resolvedArgs[$i];
                        }
                    }

                    $executionResult = call_user_func_array([$this, $name], $evalArgs);

                    if (! empty($valuesToBubble)) {
                        $host->pushReferenceAssignments($valuesToBubble);
                    }

                    return $executionResult;
                }
            }
        }

        $runtimeArgs = $host->evaluateArgumentGroup($argumentGroup);
        $runtimeArgs = $runtimeArgs[LibraryManager::ARG_NORMAL];

        if ($acceptsMany) {
            $methodDef = $libraryMethodDefinition[0];

            foreach ($runtimeArgs as $arg) {
                $this->assertValidRuntimeValue($arg, $methodDef);
            }

            $runtimeArgs = [$runtimeArgs];
        }

        return call_user_func_array([$this, $name], $runtimeArgs);
    }

    private function assertValidRuntimeValue($value, $argDef)
    {
        if ($value === null) {
            return;
        }

        $accepts = $argDef[self::KEY_ACCEPTS];

        if (is_numeric($value) && in_array(self::KEY_TYPE_NUMERIC, $accepts)) {
            return;
        }
        if ((is_array($value) ||
                is_object($value) && ($value instanceof Countable || $value instanceof Collection)) &&
            in_array(self::KEY_TYPE_ARRAY, $accepts)) {
            return;
        }
        if ((is_string($value) || is_null($value)) && in_array(self::KEY_TYPE_STRING, $accepts)) {
            return;
        }
        if (is_object($value) && in_array(self::KEY_TYPE_OBJECT, $accepts)) {
            return;
        }
        if (is_bool($value) && in_array(self::KEY_TYPE_BOOL, $accepts)) {
            return;
        }

        $paramName = $argDef[self::KEY_NAME];

        throw ErrorFactory::makeLibraryError(
            AntlersErrorCodes::TYPE_LIBRARY_CALL_RUNTIME_TYPE_MISMATCH,
            'Parameter '.$paramName.' requires type: '.Str::makeSentenceList($accepts, ' or ').'; type '.TypeLabeler::getPrettyRuntimeTypeName($value).' provided.'
        );
    }

    /**
     * Throws an exception if the number of arguments does not match the provided count exactly.
     *
     * @param  int  $count  Desired count.
     *
     * @throws LibraryCallException
     */
    protected function assertArgumentCount($count)
    {
        if (count($this->activeArgs->args) != $count) {
            throw ErrorFactory::makeLibraryError(
                AntlersErrorCodes::TYPE_LIBRARY_CALL_TOO_MANY_ARGUMENTS,
                'Too many arguments provided for method call: '.$this->activeMethodName.'.'
            );
        }
    }

    /**
     * Throws an exception if the number of arguments exceeds the provided count.
     *
     * @param  int  $count  Max argument count.
     *
     * @throws LibraryCallException
     */
    protected function assertMaxArgCount($count)
    {
        if (count($this->activeArgs->args) > $count) {
            throw ErrorFactory::makeLibraryError(
                AntlersErrorCodes::TYPE_LIBRARY_CALL_TOO_MANY_ARGUMENTS,
                'Too many arguments provided for method call: '.$this->activeMethodName.'.'
            );
        }
    }

    protected function numericVar($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_NUMERIC]];
    }

    protected function numericVarWithDefault($varName, $default)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => true, self::KEY_DEFAULT => $default, self::KEY_ACCEPTS => [self::KEY_TYPE_NUMERIC]];
    }

    protected function stringVar($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_STRING]];
    }

    protected function stringVarWithDefault($varName, $default)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => true, self::KEY_DEFAULT => $default, self::KEY_ACCEPTS => [self::KEY_TYPE_STRING]];
    }

    protected function boolVar($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_BOOL]];
    }

    protected function boolVarWithDefault($varName, $default)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => true, self::KEY_DEFAULT => $default, self::KEY_ACCEPTS => [self::KEY_TYPE_BOOL]];
    }

    protected function arrayVar($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY]];
    }

    protected function arrayVarWithDefault($varName, $default)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => true, self::KEY_DEFAULT => $default, self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY]];
    }

    protected function strOrArrayVar($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_STRING, self::KEY_TYPE_ARRAY]];
    }

    protected function arrayVarReference($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_TYPE_ARG_BY_REF => true, self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY]];
    }

    protected function anyParam($varName)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => false, self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY, self::KEY_TYPE_OBJECT, self::KEY_TYPE_NUMERIC, self::KEY_TYPE_STRING, self::KEY_TYPE_BOOL]];
    }

    protected function anyWithDefault($varName, $default)
    {
        return [self::KEY_NAME => $varName, self::KEY_HAS_DEFAULT => true, self::KEY_DEFAULT => $default, self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY, self::KEY_TYPE_OBJECT, self::KEY_TYPE_NUMERIC, self::KEY_TYPE_STRING, self::KEY_TYPE_BOOL]];
    }

    protected function anyWithNullDefault($varName)
    {
        return $this->anyWithDefault($varName, null);
    }
}
