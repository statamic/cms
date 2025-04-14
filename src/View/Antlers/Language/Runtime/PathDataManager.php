<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder;
use Statamic\Contracts\Support\Boolable;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
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
use Statamic\View\Antlers\Language\Runtime\Sandbox\RuntimeValues;
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

    private $isReturningForConditions = false;

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
     * Indicates if the data manager encountered a Builder instance on the final part of a variable path.
     *
     * @var bool
     */
    private $encounteredBuilderOnFinalPart = false;

    private function lockData()
    {
        if ($this->nodeProcessor != null) {
            $this->nodeProcessor->createLockData();
        }
    }

    private function unlockData()
    {
        if ($this->nodeProcessor != null) {
            $this->nodeProcessor->restoreLockedData();
        }
    }

    /**
     * Reset state for things that should not persist
     * across repeated calls to getData and friends.
     *
     * @return void
     */
    private function resetInternalState()
    {
        $this->isReturningForConditions = false;
    }

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
     * @return $this
     */
    public function setNodeProcessor($processor)
    {
        $this->nodeProcessor = $processor;

        return $this;
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
     * @return $this
     */
    public function setInterpolations($interpolations)
    {
        $this->interpolations = $interpolations;

        return $this;
    }

    /**
     * Returns a value indicating if the PathDataManager will intercept values.
     *
     * @return bool
     */
    public function getShouldDoValueIntercept()
    {
        return $this->shouldDoValueIntercept;
    }

    /**
     * Sets whether the PathDataManager will intercept values or not.
     *
     * @param  bool  $shouldIntercept  Whether to intercept.
     * @return $this
     */
    public function setShouldDoValueIntercept($shouldIntercept)
    {
        $this->shouldDoValueIntercept = $shouldIntercept;

        return $this;
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

    public function setIsReturningForConditions($isCondition)
    {
        $this->isReturningForConditions = $isCondition;

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
        $result = $this->getDataWithExistence($path, $data);
        $lastPath = $this->lastPath();

        if ($result[0] === false && mb_strlen($lastPath) > 0) {
            $lastPath = $path->originalContent;
        }

        if (Str::contains($lastPath, '{method:')) {
            throw new VariableAccessException('Cannot set method value with path: "'.$path->originalContent.'"');
        }

        Arr::set($data, $lastPath, $value);
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
                'trace' => GlobalRuntimeState::$templateFileStack,
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
     * Gets if the data manager encountered a Builder instance on the last part of a variable path.
     *
     * @return bool
     */
    public function getEncounteredBuilderOnLastPath()
    {
        return $this->encounteredBuilderOnFinalPart;
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
            if ($pathItem->isFinal) {
                $this->encounteredBuilderOnFinalPart = true;
            }

            return;
        }

        $builderCheckValue = $this->reducedVar instanceof Value ? $this->reducedVar->value() : $this->reducedVar;

        if ($builderCheckValue instanceof Builder) {
            $nodeProcessor = $this->getNodeProcessor();

            if ($nodeProcessor != null) {
                $activeNode = $nodeProcessor->getActiveNode();

                if ($activeNode instanceof AntlersNode || $activeNode instanceof ConditionNode) {
                    $interceptResult = $nodeProcessor->evaluateDeferredNodeAsTag(
                        $activeNode,
                        'query',
                        'index', ['builder' => $builderCheckValue]
                    );

                    $this->reducedVar = $interceptResult;
                } else {
                    $this->collapseQueryBuilder($builderCheckValue);
                }
            } else {
                $this->collapseQueryBuilder($builderCheckValue);
            }
        }
    }

    private function collapseValues(bool $isFinal)
    {
        if (! $isFinal && $this->reducedVar instanceof Values) {
            $this->lockData();
            $this->reducedVar = self::reduce($this->reducedVar, true, $this->shouldDoValueIntercept);
            $this->unlockData();
        }
    }

    private function collapseQueryBuilder($builder)
    {
        $this->reducedVar = $builder->get();

        if ($this->reducedVar instanceof Collection) {
            $this->reducedVar = $this->reducedVar->all();
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
        $this->encounteredBuilderOnFinalPart = false;

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
                $this->resetInternalState();

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
                    $this->resetInternalState();

                    return 'void::'.GlobalRuntimeState::$environmentId;
                }

                if (array_key_exists($pathItem->name, $this->interpolations) && $this->environment != null) {
                    $nodeProcessor = $this->getNodeProcessor();

                    if ($nodeProcessor != null) {
                        $varResult = $nodeProcessor->cloneProcessor()
                            ->setData($data)->reduce($this->interpolations[$pathItem->name]);

                        $this->resolvedPath[] = $pathItem->name;

                        if ((is_string($varResult) || is_numeric($varResult)) && is_array($this->reducedVar) && array_key_exists($varResult, $this->reducedVar)) {
                            $this->reducedVar = $this->reducedVar[$varResult];
                        } else {
                            $this->reducedVar = $varResult;
                        }

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

                        if (count($path->pathParts) > 1 && $this->isPair == false && ! $this->reducedVar instanceof Model) {
                            // If we have more steps in the path to take, but we are
                            // not a tag pair, we need to reduce anyway so we
                            // can descend further into the nested values.
                            // We skip this step for Models to prevent
                            // some of the reflection stuff below.
                            $this->lockData();
                            $this->reducedVar = self::reduce($this->reducedVar, true, $this->shouldDoValueIntercept);
                            $this->unlockData();
                        }

                        $this->collapseValues($pathItem->isFinal);

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
                                        $this->lockData();
                                        $this->reducedVar = self::reduce($this->reducedVar, true, $this->shouldDoValueIntercept);
                                        $this->unlockData();
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

                $wasBuilderGoingIntoLast = false;

                if ($pathItem->isFinal && $this->reducedVar instanceof Builder) {
                    $wasBuilderGoingIntoLast = true;
                }

                if ($this->reducedVar instanceof Model) {
                    $this->reducedVar = $this->reducedVar->{$pathItem->name};
                } else {
                    $this->reduceVar($pathItem, $data);
                }

                $this->collapseValues($pathItem->isFinal);

                if ($pathItem->isFinal && $this->reducedVar instanceof Builder && ! $wasBuilderGoingIntoLast) {
                    $this->encounteredBuilderOnFinalPart = true;
                }

                if ($this->doBreak) {
                    break;
                }
            } elseif ($pathItem instanceof VariableReference) {
                if ($this->reducedVar instanceof Builder) {
                    $this->lockData();
                    GlobalRuntimeState::$requiresRuntimeIsolation = true;
                    try {
                        $this->reducedVar = $this->reducedVar->get()->all();
                    } catch (Exception $e) {
                        throw $e;
                    } finally {
                        GlobalRuntimeState::$requiresRuntimeIsolation = false;
                    }

                    $this->unlockData();
                }

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

                    $this->doBreak = false;

                    continue;
                } else {
                    $referencePath = null;
                    $processor = $this->getNodeProcessor();

                    if (count($pathItem->pathParts) == 1 && array_key_exists($pathItem->originalContent, $this->interpolations) && $processor != null) {
                        $referencePath = $processor->cloneProcessor()->setData($data)
                            ->setIsInterpolationProcessor(true)->reduce($this->interpolations[$pathItem->originalContent]);
                    } else {
                        $retriever = new PathDataManager();
                        $retriever->setInterpolations($this->interpolations);
                        $retriever->setEnvironment($this->environment);
                        $retriever->setIsPaired(false);
                        $referencePath = $retriever->getData($pathItem, $data, true);
                    }

                    if (is_numeric($referencePath) && array_key_exists($referencePath, $this->reducedVar)) {
                        $this->reducedVar = $this->reducedVar[$referencePath];
                    } else {
                        $this->reduceVar($referencePath, $data);
                    }

                    if ($this->doBreak) {
                        break;
                    }

                    if ($pathItem->isFinal == false || $this->reduceFinal) {
                        $this->compact(false);
                    }
                }
            }
        }

        if ($this->isPair && ! $this->reduceFinal) {
            $this->compact(true);
        }

        if ($this->reducedVar instanceof Model && $this->isPair) {
            $this->reducedVar = self::reduce($this->reducedVar, true, true, false);
        }

        $this->namedSlotsInScope = false;
        $this->resetInternalState();

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
     * @param  array  $processorData
     */
    private function reduceVar($path, $processorData = [])
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

        if (is_string($varPath) && array_key_exists($varPath, $this->interpolations) && $this->nodeProcessor != null) {
            $varPath = $this->nodeProcessor->cloneProcessor()->setData($processorData)
                ->setIsInterpolationProcessor(true)->reduce($this->interpolations[$varPath]);
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

        if ($this->reducedVar instanceof Augmentable) {
            $this->lockData();
            $this->reducedVar = self::reduce($this->reducedVar);
            $this->unlockData();
        }

        if (is_object($this->reducedVar) && method_exists($this->reducedVar, Str::camel($varPath))) {
            $this->reducedVar = call_user_func_array([$this->reducedVar, Str::camel($varPath)], []);
            $this->resolvedPath[] = '{method:'.$varPath.'}';

            if ($doCompact) {
                $this->compact($path->isFinal);
            }
        } elseif (is_object($this->reducedVar) && property_exists($this->reducedVar, Str::camel($varPath))) {
            $this->reducedVar = $this->reducedVar->{Str::camel($varPath)};
            $this->resolvedPath[] = '{method:'.$varPath.'}';

            if ($doCompact) {
                $this->compact($path->isFinal);
            }
        } elseif (is_array($this->reducedVar)) {
            if (is_numeric($varPath) && ! Arr::isAssoc($this->reducedVar) && $varPath < count($this->reducedVar) && array_key_exists($varPath, $this->reducedVar)) {
                $this->resolvedPath[] = $varPath;
                $this->reducedVar = $this->reducedVar[$varPath];

                if ($path instanceof PathNode) {
                    if ($path->isFinal) {
                        $this->doBreak = true;
                    } else {
                        $this->doBreak = false;
                    }
                } else {
                    $this->doBreak = true;
                }

                if (! $this->doBreak) {
                    $this->compact(false);
                }
            } elseif (array_key_exists($varPath, $this->reducedVar)) {
                $this->resolvedPath[] = $varPath;
                $this->reducedVar = $this->reducedVar[$varPath];

                if ($doCompact) {
                    if ($path != null && is_object($path)) {
                        $this->compact($path->isFinal);
                    } else {
                        $this->compact(false);
                    }
                }
                if ($path instanceof PathNode) {
                    if ($path->isFinal) {
                        $this->doBreak = true;
                    } else {
                        $this->doBreak = false;
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
        if (! $isFinal && $this->reducedVar instanceof Model) {
            return;
        }

        if ($this->isForArrayIndex && $isFinal && is_object($this->reducedVar) && method_exists($this->reducedVar, '__toString')) {
            $this->reducedVar = (string) $this->reducedVar;

            return;
        }

        if ($this->isReturningForConditions && $isFinal && $this->reducedVar instanceof Boolable) {
            $this->reducedVar = $this->reducedVar->toBool();

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
     * @param  bool  $leaveModelsAlone
     * @return array|string
     */
    public static function reduce($value, $isPair = true, $reduceBuildersAndAugmentables = true, $leaveModelsAlone = true)
    {
        $reductionStack = [$value];
        $returnValue = $value;

        if ($value instanceof Model && $leaveModelsAlone) {
            return $value;
        }

        while (! empty($reductionStack)) {
            $reductionValue = array_pop($reductionStack);

            if ($reductionValue instanceof Value) {
                GlobalRuntimeState::$isEvaluatingUserData = true;
                GlobalRuntimeState::$isEvaluatingData = true;
                $augmented = RuntimeValues::getValue($reductionValue);
                $augmented = self::guardRuntimeReturnValue($augmented);
                GlobalRuntimeState::$isEvaluatingUserData = false;
                GlobalRuntimeState::$isEvaluatingData = false;

                if (! $isPair) {
                    return $augmented;
                }

                $reductionStack[] = $augmented;

                continue;
            } elseif ($reductionValue instanceof Values) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = $reductionValue->toArray();
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof \Statamic\Entries\Collection) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = RuntimeValues::resolveWithRuntimeIsolation($reductionValue);
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof ArrayableString) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = $reductionValue->toArray();
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof Augmentable) {
                // Avoids resolving augmented data "too early".
                if ($reduceBuildersAndAugmentables) {
                    GlobalRuntimeState::$isEvaluatingUserData = true;
                    GlobalRuntimeState::$isEvaluatingData = true;
                    $augmented = RuntimeValues::resolveWithRuntimeIsolation($reductionValue);
                    $augmented = self::guardRuntimeReturnValue($augmented);
                    GlobalRuntimeState::$isEvaluatingUserData = false;
                    GlobalRuntimeState::$isEvaluatingData = false;
                    $reductionStack[] = $augmented;
                } else {
                    return $reductionValue;
                }

                continue;
            } elseif ($reductionValue instanceof Collection) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = $reductionValue->all();
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof Model) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $data = $reductionValue->toArray();

                foreach (get_class_methods($reductionValue) as $method) {
                    if ((new \ReflectionMethod($reductionValue, $method))->getReturnType()?->getName() === Attribute::class) {
                        $method = Str::snake($method);
                        $data[$method] = $reductionValue->$method;
                    }

                    if (Str::startsWith($method, 'get') && Str::endsWith($method, 'Attribute')) {
                        $method = Str::of($method)->after('get')->before('Attribute')->snake()->__toString();
                        $data[$method] = $reductionValue->getAttribute($method);
                    }
                }

                $reductionStack[] = $data;
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof Arrayable) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = $reductionValue->toArray();
                GlobalRuntimeState::$isEvaluatingData = false;

                continue;
            } elseif ($reductionValue instanceof Builder && $reduceBuildersAndAugmentables) {
                GlobalRuntimeState::$isEvaluatingData = true;
                $reductionStack[] = $reductionValue->get();
                GlobalRuntimeState::$isEvaluatingData = false;

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
        GlobalRuntimeState::$isEvaluatingData = true;

        if ($value instanceof Model) {
            return $value;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof Value) {
            GlobalRuntimeState::$isEvaluatingUserData = true;

            if (! $isPair) {
                $returnValue = $value->antlersValue($parser, $data);
            } else {
                $returnValue = self::reduce($value->antlersValue($parser, $data));
            }
            $returnValue = self::guardRuntimeReturnValue($returnValue);

            GlobalRuntimeState::$isEvaluatingUserData = false;
        } elseif ($value instanceof Values) {
            GlobalRuntimeState::$isEvaluatingUserData = true;
            $returnValue = $value->toArray();
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
        GlobalRuntimeState::$isEvaluatingData = false;

        return $returnValue;
    }
}
