<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Value;
use Statamic\View\Antlers\AntlersString;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\VariableAccessException;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Parser\LanguageKeywords;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;
use Statamic\View\Antlers\Language\Runtime\Sandbox\RuntimeValueCache;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;

class PathDataManager
{
    /**
     * Indicates if the data manager should continue
     * descending through the variable path.
     *
     * @var bool
     */
    private $doBreak = false;

    /**
     * The current reduced value discovered by the data manager.
     *
     * @var mixed|null
     */
    private $reducedVar = null;

    /**
     * Indicates if the data manager was able to locate the requested data.
     *
     * @var bool
     */
    private $didFind = true;

    /**
     * A list of all path elements observed while finding data.
     *
     * @var array
     */
    private $resolvedPath = [];

    /**
     * Indicates if the variable reference belongs to a node pair.
     *
     * @var bool
     */
    private $isPair = true;

    /**
     * Indicates if the path manager has already set its source data instance.
     *
     * @var bool
     */
    private $didSetSourceData = false;

    /**
     * The data to search.
     *
     * @var array|null
     */
    private $data = null;

    /**
     * Indicates if the final path element should be reduced.
     *
     * @var bool
     */
    private $reduceFinal = true;

    /**
     * @var Parser|null
     */
    private $antlersParser = null;

    /**
     * Indicates if named slots were detected in scope.
     *
     * @var bool
     */
    private $namedSlotsInScope = false;

    /**
     * @var Cascade|null
     */
    private $cascade = null;

    /**
     * @var Environment|null
     */
    private $environment = null;

    /**
     * @var NodeProcessor|null
     */
    private $nodeProcessor = null;

    /**
     * A collection of internal interpolation references.
     *
     * @var array
     */
    private $interpolations = [];

    /**
     * Indicates if the data manager should check for special values and intercept them.
     *
     * @var bool
     */
    private $shouldDoValueIntercept = true;

    /**
     * A list of prefixes to check for first.
     *
     * @var string[]
     */
    private $handlePrefixes = [];

    /**
     * Indicates if the data manager should preferentially cast value objects to a string, for array lookups.
     *
     * @var bool
     */
    private $isForArrayIndex = false;

    /**
     * Sets the internal environment reference.
     *
     * @param  Environment  $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Sets a list of handle prefixes that should be checked.
     *
     * @param  string[]  $prefixes  The handle prefixes.
     */
    public function setHandlePrefixes($prefixes)
    {
        $this->handlePrefixes = $prefixes;
    }

    /**
     * Sets the internal node processor reference.
     *
     * @param  NodeProcessor  $processor
     */
    public function setNodeProcessor($processor)
    {
        $this->nodeProcessor = $processor;
    }

    /**
     * Returns a NodeProcessor reference based on the current configuration.
     *
     * @return NodeProcessor|null
     */
    private function getNodeProcessor()
    {
        if ($this->environment != null) {
            return $this->environment->_getNodeProcessor();
        }

        return $this->nodeProcessor;
    }

    /**
     * Sets the data manager's internal interpolation reference.
     *
     * @param  array  $interpolations
     */
    public function setInterpolations($interpolations)
    {
        $this->interpolations = $interpolations;
    }

    /**
     * Attempts to locate a value within the provided data.
     *
     * The first element of the return value indicates if the data was located.
     * The second element is the retrieved value.
     *
     * @param  VariableReference  $path  The variable path.
     * @param  array  $data  The data to search.
     * @param  bool  $disableIntercept  Indicates if variable interception should be disabled.
     * @return array
     *
     * @throws RuntimeException
     */
    public function getDataWithExistence(VariableReference $path, $data, $disableIntercept = false)
    {
        $currentValue = $this->shouldDoValueIntercept;
        if ($disableIntercept) {
            $this->shouldDoValueIntercept = false;
        }

        $value = $this->getData($path, $data);
        $this->shouldDoValueIntercept = $currentValue;

        return [$this->didFind, $value];
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

        return $this;
    }

    /**
     * Sets whether the final path value is reduced.
     *
     * @param  bool  $reduceFinal  The value to set.
     * @return $this
     */
    public function setReduceFinal($reduceFinal)
    {
        $this->reduceFinal = $reduceFinal;

        return $this;
    }

    public function getReduceFinal()
    {
        return $this->reduceFinal;
    }

    /**
     * Attempts to locate a value within the provided data.
     *
     * Alias of getData()
     *
     * @param  VariableReference  $path  The variable path.
     * @param  array  $data  The data to search.
     * @return mixed|null
     *
     * @throws RuntimeException
     */
    public function getRuntimeValue(VariableReference $path, $data)
    {
        return $this->getData($path, $data);
    }

    /**
     * Sets whether the outer node belongs to a node pair.
     *
     * @param  bool  $isPair  Whether the parent node is paired.
     * @return $this
     */
    public function setIsPaired($isPair)
    {
        $this->isPair = $isPair;

        return $this;
    }

    /**
     * Returns whether the outer node belongs to a node pair.
     *
     * @return bool
     */
    public function getIsPaired()
    {
        return $this->isPair;
    }

    /**
     * Attempts to set a runtime value.
     *
     * @param  VariableReference  $path  The variable path.
     * @param  array  $data  The data to update.
     * @param  mixed  $value  The value to set.
     *
     * @throws VariableAccessException|RuntimeException
     */
    public function setRuntimeValue(VariableReference $path, &$data, $value)
    {
        // Run through the getData routine to build up the dynamic path.
        $this->getData($path, $data);
        $lastPath = $this->lastPath();

        if (Str::contains($lastPath, '{method:')) {
            throw new VariableAccessException('Cannot set method value with path: "'.$path->originalContent.'"');
        }

        Arr::set($data, $this->lastPath(), $value);
    }

    /**
     * Returns the condensed last resolved path.
     *
     * @return string
     */
    public function lastPath()
    {
        return implode('.', $this->resolvedPath);
    }

    /**
     * Consults the runtime configuration to check
     * if the variable should be evaluated.
     *
     * @param  string  $normalizedReference  The normalized path to check.
     * @return bool
     *
     * @throws RuntimeException
     */
    private function guardRuntimeAccess($normalizedReference)
    {
        if (GlobalRuntimeState::$isEvaluatingUserData) {
            $guardList = GlobalRuntimeState::$bannedContentVarPaths;
        } else {
            $guardList = GlobalRuntimeState::$bannedVarPaths;
        }

        if (empty($guardList)) {
            return true;
        }

        if (Str::is($guardList, $normalizedReference)) {
            Log::warning('Runtime Access Violation: '.$normalizedReference, [
                'variable' => $normalizedReference,
                'file' => GlobalRuntimeState::$currentExecutionFile,
                'trace' =>  GlobalRuntimeState::$templateFileStack,
            ]);

            if (GlobalRuntimeState::$throwErrorOnAccessViolation) {
                throw ErrorFactory::makeRuntimeError(
                    AntlersErrorCodes::RUNTIME_PROTECTED_VAR_ACCESS,
                    null,
                    'Protected variable access.'
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Checks the current path item and data to see if the resolved value should be intercepted.
     *
     * Builder instances will be sent to the Query tag to be resolved when they are encountered.
     *
     * @param  PathNode  $pathItem
     *
     * @throws RuntimeException
     */
    private function checkForValueIntercept($pathItem)
    {
        if (! $this->shouldDoValueIntercept) {
            return;
        }

        $builderCheckValue = $this->reducedVar instanceof Value ? $this->reducedVar->value() : $this->reducedVar;

        if ($builderCheckValue instanceof Builder) {
            $nodeProcessor = $this->getNodeProcessor();
            $activeNode = $nodeProcessor->getActiveNode();

            if ($activeNode instanceof AntlersNode || $activeNode instanceof ConditionNode) {
                $interceptResult = $nodeProcessor->evaluateDeferredNodeAsTag(
                    $activeNode,
                    'query',
                    $pathItem->name, ['builder' => $builderCheckValue]
                );

                $this->reducedVar = $interceptResult;
            } else {
                throw new RuntimeException('Not enough context to resolve: '.$pathItem->name);
            }
        }
    }

    /**
     * Attempts to locate a value within the provided data.
     *
     * @param  VariableReference  $path  The variable path.
     * @param  array  $data  The data to search.
     * @param  bool  $isForArrayIndex  Indicates if the resolved value will be used for array lookups.
     * @return mixed|null
     *
     * @throws RuntimeException
     */
    public function getData(VariableReference $path, $data, $isForArrayIndex = false)
    {
        if (! $this->guardRuntimeAccess($path->normalizedReference)) {
            return null;
        }

        $this->isForArrayIndex = $isForArrayIndex;

        if ($path->isVariableVariable) {
            $pathCopy = $path->clone();
            $pathCopy->isVariableVariable = false;
            $dynamicPath = (string) $this->getData($pathCopy, $data);

            $tempPathParser = new PathParser();
            $path = $tempPathParser->parse($dynamicPath);

            if (! $this->guardRuntimeAccess($path->normalizedReference)) {
                return null;
            }
        }

        if ($this->didSetSourceData == false) {
            $this->data = $data;
            $this->didSetSourceData = true;
        }

        $this->resolvedPath = [];
        $this->didFind = true;
        $this->reducedVar = null;
        $didScanSourceData = false;

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $this->namedSlotsInScope = array_key_exists(
            GlobalRuntimeState::createIndicatorVariable(GlobalRuntimeState::INDICATOR_NAMED_SLOTS_AVAILABLE),
            $data
        );

        foreach ($path->pathParts as $pathItem) {
            if ($pathItem instanceof PathNode) {
                if ($pathItem->isStringVar) {
                    $this->reducedVar = $pathItem->name;
                    continue;
                }

                if ($pathItem->name == 'void' && count($path->pathParts) == 1) {
                    return 'void::'.GlobalRuntimeState::$environmentId;
                }

                if (array_key_exists($pathItem->name, $this->interpolations) && $this->environment != null) {
                    $nodeProcessor = $this->getNodeProcessor();

                    if ($nodeProcessor != null) {
                        $varResult = $nodeProcessor->reduce($this->interpolations[$pathItem->name]);
                        $this->resolvedPath[] = $pathItem->name;
                        $this->reducedVar = $varResult;

                        $this->compact($pathItem->isFinal);
                        continue;
                    }
                }

                if ($didScanSourceData == false) {
                    if ($this->namedSlotsInScope && $pathItem->name == 'slot' &&
                        $path->originalContent != 'slot' &&
                        array_key_exists($path->originalContent, $data)) {
                        $this->reducedVar = $data[$path->originalContent];
                        break;
                    }

                    $nameToUse = $pathItem->name;

                    if (! empty($this->handlePrefixes)) {
                        foreach ($this->handlePrefixes as $prefix) {
                            $prefixedVar = $prefix.$nameToUse;

                            if (array_key_exists($prefixedVar, $data)) {
                                $nameToUse = $prefixedVar;
                                break;
                            }
                        }
                    }

                    if (array_key_exists($nameToUse, $data)) {
                        $this->resolvedPath[] = $nameToUse;
                        $this->reducedVar = $data[$nameToUse];

                        $this->checkForValueIntercept($pathItem);

                        $didScanSourceData = true;

                        if ($pathItem->isFinal == false || $this->reduceFinal) {
                            if (! $pathItem->isFinal) {
                                $this->compact(false);
                            } else {
                                $this->compact(true);
                            }
                        }

                        if (count($path->pathParts) > 1 && $this->isPair == false) {

                            // If we have more steps in the path to take, but we are
                            // not a tag pair, we need to reduce anyway so we
                            // can descend further into the nested values.
                            $this->reducedVar = self::reduce($this->reducedVar, true, $this->shouldDoValueIntercept);
                        }

                        continue;
                    } else {
                        if ($this->cascade != null) {
                            // Attempt to locate the data in the cascade.
                            $cascadeData = $this->cascade->get($pathItem->name);

                            if ($cascadeData != null) {
                                $this->reducedVar = $cascadeData;
                                $didScanSourceData = true;

                                if ($pathItem->isFinal == false || $this->reduceFinal) {
                                    $this->compact(false);
                                }

                                if (count($path->pathParts) > 1 && $this->isPair == false) {

                                    // If we have more steps in the path to take, but we are
                                    // not a tag pair, we need to reduce anyway so we
                                    // can descend further into the nested values.
                                    if (! $pathItem->isFinal) {
                                        $this->reducedVar = self::reduce($this->reducedVar, true, $this->shouldDoValueIntercept);
                                    }
                                }

                                $this->didFind = true;
                                continue;
                            }
                        }
                        $this->resolvedPath[] = $pathItem->name;
                        $this->didFind = false;
                        break;
                    }
                }

                $this->reduceVar($pathItem);

                if ($this->doBreak) {
                    break;
                }
            } elseif ($pathItem instanceof VariableReference) {
                if (count($pathItem->pathParts) == 1 && is_numeric($pathItem->originalContent) &&
                    intval($pathItem->originalContent) == $pathItem->originalContent) {
                    $numericIndex = intval($pathItem->originalContent);

                    if (array_key_exists($numericIndex, $this->reducedVar)) {
                        $this->reducedVar = $this->reducedVar[$numericIndex];
                    } else {
                        $this->reducedVar = null;
                        $this->didFind = false;
                    }

                    if ($pathItem->isFinal == false || $this->reduceFinal) {
                        $this->compact(false);
                    }

                    continue;
                } else {
                    $retriever = new PathDataManager();
                    $retriever->setInterpolations($this->interpolations);
                    $retriever->setEnvironment($this->environment);
                    $retriever->setIsPaired(false);
                    $referencePath = $retriever->getData($pathItem, $data, true);

                    $this->reduceVar($referencePath);

                    if ($this->doBreak) {
                        break;
                    }
                }
            }
        }

        if ($this->isPair && ! $this->reduceFinal) {
            $this->compact(true);
        }

        $this->namedSlotsInScope = false;

        return $this->reducedVar;
    }

    /**
     * Sets the parser instance to use when reducing content values.
     *
     * @param  Parser  $parser  The parser instance.
     */
    public function setAntlersParser(Parser $parser)
    {
        $this->antlersParser = $parser;
    }

    /**
     * Resets the internal parser instance.
     */
    public function resetParser()
    {
        $this->antlersParser = null;
    }

    /**
     * Reduces the provided path data element.
     *
     * @param  PathNode|string  $path  The path element.
     */
    private function reduceVar($path)
    {
        if ($path === null) {
            $this->reducedVar = null;

            return;
        }

        $varPath = '';
        $doCompact = true;

        if (is_string($path)) {
            $varPath = $path;
            $doCompact = true;
        } else {
            if ($path instanceof PathNode) {
                $doCompact = (! $path->isFinal || $this->reduceFinal);
                $varPath = $path->name;
            }
        }

        // Handles some edge-case type values.
        if ($path instanceof PathNode) {
            if ($path->name == LanguageKeywords::ConstTrue && isset($this->reducedVar[1])) {
                $varPath = 1;
            } elseif ($path->name == LanguageKeywords::ConstFalse && isset($this->reducedVar[0])) {
                $varPath = 0;
            } elseif ($path->name == LanguageKeywords::ConstNull && isset($this->reducedVar[''])) {
                $varPath = '';
            }
        }

        if (is_object($this->reducedVar) && method_exists($this->reducedVar, Str::camel($varPath))) {
            $this->reducedVar = call_user_func_array([$this->reducedVar, Str::camel($varPath)], []);
            $this->resolvedPath[] = '{method:'.$varPath.'}';

            if ($doCompact) {
                $this->compact($path->isFinal);
            }
        } elseif (is_array($this->reducedVar)) {
            if (array_key_exists($varPath, $this->reducedVar)) {
                $this->resolvedPath[] = $varPath;
                $this->reducedVar = $this->reducedVar[$varPath];

                if ($doCompact) {
                    if ($path != null && is_object($path)) {
                        $this->compact($path->isFinal);
                    } else {
                        $this->compact(false);
                    }
                }
            } else {
                $this->reducedVar = null;
                $this->didFind = false;
                $this->doBreak = true;
            }
        } elseif (is_string($this->reducedVar)) {
            $this->reducedVar = null;
            $this->didFind = false;
            $this->doBreak = true;
        }
    }

    /**
     * Compacts the last resolved variable.
     *
     * @param  bool  $isFinal  Indicates if the current value is the final value in a path.
     */
    private function compact($isFinal)
    {
        if ($this->isForArrayIndex && $isFinal && is_object($this->reducedVar) && method_exists($this->reducedVar, '__toString')) {
            $this->reducedVar = (string) $this->reducedVar;

            return;
        }

        if ($this->antlersParser == null) {
            $this->reducedVar = self::reduce($this->reducedVar, $this->isPair, $this->shouldDoValueIntercept);
        } else {
            $isActualPair = $this->isPair;

            if (! $isFinal) {
                $isActualPair = true;
            }

            $this->reducedVar = self::reduceForAntlers($this->reducedVar, $this->antlersParser, $this->data, $isActualPair);
        }
    }

    protected static function guardRuntimeReturnValue($value)
    {
        if ((is_string($value) || $value instanceof AntlersString) && ! GlobalRuntimeState::$allowPhpInContent) {
            $value = (string) $value;

            $value = StringUtilities::sanitizePhp($value);
        }

        return $value;
    }

    /**
     * Reduces a runtime value.
     *
     * @param  mixed  $value  The value to reduce.
     * @param  bool  $isPair  Indicates if the path belongs to a node pair.
     * @param  bool  $reduceBuildersAndAugmentables  Indicates if Builder and Augmentable instances should be resolved.
     * @return array|string
     */
    public static function reduce($value, $isPair = true, $reduceBuildersAndAugmentables = true)
    {
        $reductionStack = [$value];
        $returnValue = $value;

        while (! empty($reductionStack)) {
            $reductionValue = array_pop($reductionStack);

            if ($reductionValue instanceof Value) {
                GlobalRuntimeState::$isEvaluatingUserData = true;
                $augmented = RuntimeValueCache::getValue($reductionValue);
                $augmented = self::guardRuntimeReturnValue($augmented);
                GlobalRuntimeState::$isEvaluatingUserData = false;

                if (! $isPair) {
                    return $augmented;
                }

                $reductionStack[] = $augmented;
                continue;
            } elseif ($reductionValue instanceof \Statamic\Entries\Collection) {
                $reductionStack[] = $reductionValue->toAugmentedArray();
                continue;
            } elseif ($reductionValue instanceof ArrayableString) {
                $reductionStack[] = $reductionValue->toArray();
                continue;
            } elseif ($reductionValue instanceof Augmentable) {
                // Avoids resolving augmented data "too early".
                if ($reduceBuildersAndAugmentables) {
                    GlobalRuntimeState::$isEvaluatingUserData = true;
                    $augmented = RuntimeValueCache::getAugmentableValue($reductionValue);
                    $augmented = self::guardRuntimeReturnValue($augmented);
                    GlobalRuntimeState::$isEvaluatingUserData = false;
                    $reductionStack[] = $augmented;
                } else {
                    return $reductionValue;
                }

                continue;
            } elseif ($reductionValue instanceof Collection) {
                $reductionStack[] = $reductionValue->all();
                continue;
            } elseif ($reductionValue instanceof Arrayable) {
                $reductionStack[] = $reductionValue->toArray();
                continue;
            } elseif ($reductionValue instanceof Builder && $reduceBuildersAndAugmentables) {
                $reductionStack[] = $reductionValue->get();
                continue;
            }

            $returnValue = $reductionValue;
        }

        return $returnValue;
    }

    /**
     * Reduces an Antlers content value.
     *
     * @param  mixed  $value  The value to reduce.
     * @param  Parser  $parser  The parser instance.
     * @param  array  $data  The contextual data.
     * @param  bool  $isPair  Indicates if the path belongs to a node pair.
     * @return array|AntlersString|string
     */
    public static function reduceForAntlers($value, Parser $parser, $data, $isPair = true)
    {
        GlobalRuntimeState::$isEvaluatingUserData = true;

        if ($value instanceof Value) {
            GlobalRuntimeState::$isEvaluatingUserData = true;

            if (! $isPair) {
                $returnValue = $value->antlersValue($parser, $data);
            } else {
                $returnValue = self::reduce(($value->antlersValue($parser, $data)));
            }
            $returnValue = self::guardRuntimeReturnValue($returnValue);

            GlobalRuntimeState::$isEvaluatingUserData = false;
        } else {
            if (! $isPair) {
                if (is_array($value)) {
                    $returnValue = $value;
                } elseif (is_object($value) && method_exists($value, '__toString')) {
                    $returnValue = (string) $value;
                } else {
                    $returnValue = self::reduce($value);
                }
            } else {
                $returnValue = self::reduce($value);
            }
        }

        GlobalRuntimeState::$isEvaluatingUserData = false;

        return $returnValue;
    }
}
