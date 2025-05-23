<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ParseError;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Modifiers\ModifierException;
use Statamic\Modifiers\ModifierNotFoundException;
use Statamic\Modifiers\Modify;
use Statamic\Support\Arr;
use Statamic\Tags\Loader;
use Statamic\Tags\TagNotFoundException;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\AntlersString;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ExecutionBranch;
use Statamic\View\Antlers\Language\Nodes\EscapedContentNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Nodes\Operators\Assignment\LeftAssignmentOperator;
use Statamic\View\Antlers\Language\Nodes\RecursiveNode;
use Statamic\View\Antlers\Language\Nodes\Structures\DirectionGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\ListValueNode;
use Statamic\View\Antlers\Language\Nodes\Structures\LogicGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\PhpExecutionNode;
use Statamic\View\Antlers\Language\Nodes\Structures\SemanticGroup;
use Statamic\View\Antlers\Language\Nodes\Structures\StatementSeparatorNode;
use Statamic\View\Antlers\Language\Nodes\Structures\SwitchGroup;
use Statamic\View\Antlers\Language\Nodes\VariableNode;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;
use Statamic\View\Antlers\Language\Runtime\Sandbox\RuntimeValues;
use Statamic\View\Antlers\Language\Runtime\Sandbox\TypeCoercion;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Antlers\SyntaxError;
use Statamic\View\Cascade;
use Statamic\View\State\CachesOutput;
use Throwable;

class NodeProcessor
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * Represents all historical stack frames.
     *
     * @var array
     */
    private $data = [];

    /**
     * A collection of node identifiers that will pop the current scope stack.
     *
     * @var array
     */
    private $popScopes = [];

    /**
     * The current environment details.
     *
     * @var EnvironmentDetails|null
     */
    protected $envDetails = null;

    /**
     * Indicates if PHP code evaluation is enabled.
     *
     * @var bool
     */
    protected $allowPhp = false;

    /**
     * A list of all buffer overwrites, determined by interpolation results.
     *
     * @var array
     */
    protected $bufferOverwrites = [];

    /**
     * Indicates if the current runtime instance is evaluating an interpolated document.
     *
     * @var bool
     */
    protected $isInterpolationProcessor = false;

    /**
     * Indicates if the processor is providing results for a parameter.
     *
     * @var bool
     */
    protected $isProvidingParameterContent = false;

    /**
     * Indicates if the processor is helping to evaluate conditions.
     *
     * @var bool
     */
    protected $isConditionalProcessor = false;

    /**
     * @var RuntimeConfiguration|null
     */
    protected $runtimeConfiguration = null;

    /**
     * @var Cascade|null
     */
    private $cascade = null;

    /**
     * The Antlers parser instance to supply values, and other internal systems.
     *
     * @var Parser|null
     */
    private $antlersParser = null;

    /**
     * A list of a previously observed runtime assignments.
     *
     * @var array
     */
    protected $previousAssignments = [];

    /**
     * A list of all actively managed runtime assignments.
     *
     * @var array
     */
    protected $runtimeAssignments = [];

    /**
     * The shared PathDataManager instance.
     *
     * @var PathDataManager
     */
    private $pathDataManager = null;

    /**
     * A runtime reference of all interpolated document regions observed by this instance.
     *
     * @var array
     */
    protected $canHandleInterpolations = [];

    /**
     * The shared LanguageParser instance.
     *
     * @var LanguageParser
     */
    private $languageParser = null;

    /**
     * Maintains a cache of all evaluated interpolation regions.
     *
     * @var array
     */
    protected $interpolationCache = [];

    /**
     * @var ConditionProcessor
     */
    private $conditionProcessor = null;

    /**
     * Indicates if the runtime resolved a Builder instance.
     *
     * @var bool
     */
    private $encounteredBuilder = false;
    private $builderNodeId = null;

    /**
     * A reference to the last resolved Builder instance.
     *
     * @var Builder|null
     */
    private $resolvedBuilder = null;

    /**
     * @var AbstractNode|AntlersNode
     */
    private $activeNode = null;

    /**
     * A list of all valid PHP opening tags.
     *
     * @var string[]
     */
    private $validPhpOpenTags = ['<?php'];

    private $lockedData = [];

    private $doStackIntercept = true;

    /**
     * The current tag name being profiled.
     *
     * @var string|null
     */
    private $profilingTagName = null;

    private $scopeAdjustingParams = ['as'];

    public function __construct(Loader $loader, EnvironmentDetails $envDetails)
    {
        $this->loader = $loader;
        $this->envDetails = $envDetails;
        $this->pathDataManager = new PathDataManager();
        $this->pathDataManager->setNodeProcessor($this);
        $this->languageParser = new LanguageParser();
        $this->conditionProcessor = new ConditionProcessor();
        $this->conditionProcessor->setProcessor($this);

        if (ini_get('short_open_tag')) {
            $this->validPhpOpenTags[] = '<?';
        }
    }

    public function createLockData()
    {
        $this->lockedData = $this->data;
    }

    public function restoreLockedData()
    {
        $this->data = $this->lockedData;
        $this->lockedData = [];
    }

    /**
     * Returns the current active node.
     *
     * @return AbstractNode|AntlersNode|null
     */
    public function getActiveNode()
    {
        return $this->activeNode;
    }

    public function getPathDataManager()
    {
        return $this->pathDataManager;
    }

    public function registerInterpolations(AntlersNode $node)
    {
        if (! empty($node->processedInterpolationRegions)) {
            foreach ($node->processedInterpolationRegions as $region => $regionNodes) {
                $this->canHandleInterpolations[$region] = $regionNodes;
            }
        }
    }

    /**
     * Sets whether the NodeProcessor is processing interpolation regions.
     *
     * @param  bool  $isInterpolation  The value.
     * @return $this
     */
    public function setIsInterpolationProcessor($isInterpolation)
    {
        $this->isInterpolationProcessor = $isInterpolation;

        return $this;
    }

    /**
     * Sets whether the NodeProcessor is processing conditions.
     *
     * @param  bool  $isCondition  The value.
     * @return $this
     */
    public function setIsConditionProcessor($isCondition)
    {
        $this->isConditionalProcessor = $isCondition;

        return $this;
    }

    public function getIsConditionProcessor()
    {
        return $this->isConditionalProcessor;
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

    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * Sets whether the NodeProcessor should evaluate PHP code.
     *
     * @param  bool  $allow  The value.
     * @return $this
     */
    public function allowPhp($allow = true)
    {
        $this->allowPhp = $allow;

        return $this;
    }

    /**
     * Sets the processor's RuntimeConfiguration instance.
     *
     * @param  RuntimeConfiguration  $runtimeConfiguration  The configuration.
     * @return $this
     */
    public function setRuntimeConfiguration(RuntimeConfiguration $runtimeConfiguration)
    {
        $this->runtimeConfiguration = $runtimeConfiguration;

        return $this;
    }

    /**
     * Removes the active RuntimeConfiguration instance from the processor.
     *
     * @return $this
     */
    public function resetRuntimeConfiguration()
    {
        $this->runtimeConfiguration = null;

        return $this;
    }

    /**
     * Sets whether the processor is returning results for a parameter.
     *
     * @param  bool  $isParameter  The value.
     * @return $this
     */
    public function setIsProvidingParameterContent($isParameter)
    {
        $this->isProvidingParameterContent = $isParameter;

        return $this;
    }

    /**
     * Triggers a runtime trace complete interrupt.
     */
    public function triggerRenderComplete()
    {
        if ($this->isTracingEnabled()) {
            $this->runtimeConfiguration->traceManager->traceRenderComplete();
        }
    }

    /**
     * Sets whether the processor will treat "stack" as a tag or variable.
     *
     * @param  bool  $doIntercept
     * @return $this
     */
    public function setDoStackIntercept($doIntercept = true)
    {
        $this->doStackIntercept = $doIntercept;

        return $this;
    }

    private function startMeasuringTag($tagName, AntlersNode $node)
    {
        $this->profilingTagName = 'tag_'.$tagName.microtime();
        debugbar()->startMeasure($this->profilingTagName, $tagName);
    }

    private function stopMeasuringTag()
    {
        if ($this->profilingTagName == null) {
            return;
        }

        debugbar()->stopMeasure($this->profilingTagName);
        $this->profilingTagName = null;
    }

    /**
     * Tests if the runtime configuration supports tracing.
     *
     * @return bool
     */
    public function isTracingEnabled()
    {
        if ($this->runtimeConfiguration == null) {
            return false;
        }

        if ($this->runtimeConfiguration->isTracingEnabled) {
            return $this->runtimeConfiguration->traceManager != null;
        }

        return false;
    }

    public function getRuntimeConfiguration()
    {
        return $this->runtimeConfiguration;
    }

    /**
     * Updates the current scope with any new variable assignments.
     *
     * @param  array  $assignments  The assignments.
     */
    private function processAssignments($assignments)
    {
        $this->clearInterpolationCache();

        foreach ($assignments as $path => $value) {
            if (array_key_exists($path, $this->previousAssignments) == false) {
                $this->previousAssignments[$path] = count($this->data) - 1;
                Arr::set($this->data[count($this->data) - 1], $path, $value);
            } else {
                $start = $this->previousAssignments[$path];

                for ($i = $start; $i < count($this->data); $i++) {
                    Arr::set($this->data[$i], $path, $value);
                }

                if ($start >= count($this->data)) {
                    $targetIndex = count($this->data) - 1;
                    Arr::set($this->data[$targetIndex], $path, $value);
                    $this->previousAssignments[$path] = $targetIndex;
                }
            }

            $this->runtimeAssignments[$path] = $value;
        }

        if (GlobalRuntimeState::$traceTagAssignments) {
            GlobalRuntimeState::mergeTagRuntimeAssignments($assignments);
        }
    }

    private function replaceData($data)
    {
        $this->data = [$data];

        return $this;
    }

    /**
     * Overrides all data in the processor instance with the provided data.
     *
     * @param  array  $data  The data to set.
     * @return $this
     */
    public function swapData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the active scope data.
     *
     * @param  array  $data  The data to set.
     * @return $this
     */
    public function setData($data)
    {
        $this->data = [];
        $this->previousAssignments = [];
        $this->runtimeAssignments = [];
        $this->canHandleInterpolations = [];
        $this->interpolationCache = [];
        $this->lockedData = [];

        if ((is_array($data) == false && (is_object($data) && ($data instanceof Arrayable) == false)) ||
            is_string($data)) {
            throw new InvalidArgumentException(sprintf(
                'Expecting array or object implementing Arrayable. Encountered [%s]',
                ($type = gettype($data)) === 'object' ? get_class($data) : $type
            ));
        }

        if (is_array($data) && ! empty($data) && ! Arr::isAssoc($data)) {
            throw new InvalidArgumentException('Expecting an associative array');
        }

        if (GlobalRuntimeState::$traceTagAssignments) {
            if (! empty(GlobalRuntimeState::$tracedRuntimeAssignments)) {
                $data = array_merge(GlobalRuntimeState::$tracedRuntimeAssignments, $data);
            }
        }

        $this->data[] = $data;

        return $this;
    }

    /**
     * Adds a new scope frame to the processor's scope.
     *
     * @param  AntlersNode  $node  The introducing node.
     * @param  array  $data  The data.
     */
    protected function pushScope(AntlersNode $node, $data)
    {
        $this->popScopes[$node->refId] = 1;
        $this->data[] = $data;
    }

    /**
     * Removes the last scope frame added to the processor scope.
     *
     * @param  string  $id  The scope reference identifier.
     */
    protected function popScope($id)
    {
        if (array_key_exists($id, $this->popScopes)) {
            unset($this->popScopes[$id]);
        }

        array_pop($this->data);
    }

    /**
     * Decides if a node should be processed as a tag, or not.
     *
     * @param  AntlersNode  $node  The node.
     * @return bool
     *
     * @throws RuntimeException
     */
    private function shouldProcessAsTag(AntlersNode $node)
    {
        if ($node->pathReference == null && $node->name != null && Str::startsWith($node->name->name, '[')) {
            return false;
        }

        if ($node->pathReference != null) {
            if ($node->pathReference->isStrictTagReference) {
                return true;
            }
        }

        if ($node->name->name == 'assets' && $node->name->methodPart == 'assets') {
            return true;
        }

        // The third argument "true" disables the data managers value interception mechanism.
        // This is to prevent it from resolving expensive items like Builders too many
        // times when all we are interested in is if the data value actually exists.
        $cur = $this->pathDataManager->getIsPaired();
        $curReduce = $this->pathDataManager->getReduceFinal();

        $this->pathDataManager->setIsPaired(false);
        $this->pathDataManager->setReduceFinal(false);

        $currentData = $this->getActiveData();

        $managerResults = $this->pathDataManager->cascade($this->cascade)
            ->setInterpolations($node->processedInterpolationRegions)
            ->setNodeProcessor($this->cloneProcessor()->setData($currentData))
            ->getDataWithExistence($node->pathReference, $currentData, true);
        $this->pathDataManager->setIsPaired($cur);
        $this->pathDataManager->setReduceFinal($curReduce);

        $resolvedValue = null;

        if ($managerResults[0] === true) {
            $value = $managerResults[1];
            $this->createLockData();
            $resolvedValue = $value instanceof Value ? $value->value() : $value;
            $this->restoreLockedData();

            if ($resolvedValue instanceof Builder && $node->isClosedBy != null && $node->isSelfClosing == false) {
                $this->encounteredBuilder = true;
                $this->resolvedBuilder = $resolvedValue;
                $this->builderNodeId = $node->refId;

                // If the path reference has more than one part,
                // it is something like {{ products.0.name }}
                if ($node->pathReference != null && $node->pathReference->isComplex()) {
                    if ($this->pathDataManager->getEncounteredBuilderOnLastPath()) {
                        return true;
                    }

                    return false;
                }

                return true;
            }
        }

        if ($node->pathReference != null) {
            if ($node->pathReference->isStrictVariableReference) {
                return false;
            }
        }

        if ($managerResults[0] === true) {
            if ($node->isPaired() && ! $this->isLoopable($resolvedValue)) {
                // Safe to do this since there is no ambiguity here.
                return $node->isTagNode;
            }

            return false;
        }

        return $node->isTagNode;
    }

    /**
     * Sets an Antlers ParserContract implementation to be
     * used when evaluating the results of content values.
     *
     * @param  Parser  $parser  The parser instance.
     * @return $this
     */
    public function setAntlersParserInstance(Parser $parser)
    {
        $this->antlersParser = $parser;

        return $this;
    }

    public function getAntlersParser()
    {
        return $this->antlersParser;
    }

    /**
     * Gets the last scope frame added to the processor.
     *
     * @return array
     */
    public function getActiveData()
    {
        $dataLen = count($this->data);

        if ($dataLen == 0) {
            return [];
        }

        return $this->data[$dataLen - 1];
    }

    /**
     * Returns all scope frames.
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->data;
    }

    /**
     * Attempts to remove the last scope frame after leaving an iteration.
     *
     * This method will not reduce the scope frames to zero if
     * the scope frame length started with a non-zero count.
     */
    private function popLoopScope()
    {
        if (count($this->data) > 1) {
            array_pop($this->data);
        }
    }

    /**
     * Updates the active scope frame.
     *
     * @param  array  $data  The scope data.
     */
    private function updateCurrentScope($data)
    {
        $dataLen = count($this->data);

        if ($dataLen == 0) {
            $this->data[] = $data;
        } else {
            $this->data[$dataLen - 1] = $data;
        }
    }

    /**
     * Applies any evaluated interpolation variables to the buffer text.
     *
     * @param  string  $text  The buffer content.
     * @return string
     */
    private function modifyBufferAppend($text)
    {
        if (count($this->bufferOverwrites) == 0) {
            return $text;
        }

        foreach ($this->bufferOverwrites as $name => $value) {
            if (! is_array($value)) {
                $text = str_replace($name, $value, $text);
            }
        }

        return $text;
    }

    /**
     * Processes all nodes and returns the evaluated buffer contents.
     *
     * @param  AbstractNode[]  $nodes  The nodes to process.
     * @return array|bool|false|Collection|mixed|AntlersString|DirectionGroup|ListValueNode|SwitchGroup|string|string[]|null
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws TagNotFoundException
     * @throws Throwable
     */
    public function render($nodes)
    {
        $bufferContent = $this->reduce($nodes);

        if ($this->isInterpolationProcessor == false) {
            $bufferContent = strtr($bufferContent, NoParseManager::regions());
        }

        return $bufferContent;
    }

    private function getErrorLogContext(AntlersNode $node)
    {
        $line = null;

        if ($node->startPosition != null) {
            $line = $node->startPosition->line;
        }

        return [
            'line' => $line,
            'file' => GlobalRuntimeState::$currentExecutionFile,
        ];
    }

    /**
     * Tests if the provided node is internally treated like a tag.
     *
     * @param  AntlersNode  $node  The node.
     * @return bool
     */
    protected function isInternalTagLike(AntlersNode $node)
    {
        $nodeName = $node->name->name;

        if ($nodeName == 'slot' || $nodeName == 'push' || $nodeName == 'prepend' ||
            $nodeName == 'stack' || $nodeName == 'once') {
            return true;
        }

        return false;
    }

    private function shouldReportArrayToStringWarning(): bool
    {
        if (ModifierManager::$lastModifierName === 'ray') {
            return false;
        }

        return true;
    }

    /**
     * Tests if the runtime should continue processing the node/value combination.
     *
     * This method is responsible for ensuring that developers are not
     * attempting to loop over a string value, among other things.
     *
     * @param  AntlersNode  $node  The reference node.
     * @param  mixed  $value  The runtime value.
     * @return bool
     *
     * @throws RuntimeException
     */
    private function guardRuntime(AntlersNode $node, $value)
    {
        if ($node->isClosedBy != null && $this->isLoopable($value) == false) {
            if (! $this->isInternalTagLike($node)) {
                $varName = $node->name->getContent();
                Log::debug("Cannot loop over non-loopable variable: {{ {$varName} }}", $this->getErrorLogContext($node));
            }

            return false;
        } elseif ($this->isInterpolationProcessor == false && $this->isLoopable($value) && $node->isClosedBy == null) {
            if ($this->shouldReportArrayToStringWarning()) {
                $varName = $node->name->getContent();
                Log::debug("Cannot render an array variable as a string: {{ {$varName} }}", $this->getErrorLogContext($node));
            }

            return false;
        } elseif (is_object($value) && $node->isClosedBy == null) {
            if ($value instanceof ArrayableString) {
                return true;
            }

            if (method_exists($value, '__toString')) {
                return true;
            }

            $varName = $node->name->getContent();

            if ($this->runtimeConfiguration != null && $this->runtimeConfiguration->fatalErrorOnStringObject) {
                throw ErrorFactory::makeRuntimeError(
                    AntlersErrorCodes::TYPE_RUNTIME_ATTEMPTING_TO_RENDER_OBJECT_AS_STRING,
                    $node,
                    'Fatal Error: Attempting to render object as string.'
                );
            }

            Log::debug("Cannot render an object variable as a string: {{ {$varName} }}", $this->getErrorLogContext($node));

            return false;
        }

        return true;
    }

    /**
     * Gets all aggregated runtime assignments.
     *
     * @return array
     */
    public function getRuntimeAssignments()
    {
        return $this->runtimeAssignments;
    }

    public function setRuntimeAssignments($assignments)
    {
        $this->runtimeAssignments = $assignments;
    }

    public function mergeRuntimeAssignments($assignments)
    {
        $this->runtimeAssignments = array_merge($this->runtimeAssignments, $assignments);
    }

    /**
     * Tests if the node sequence represents an iterable assignment.
     *
     * @param  AbstractNode[]  $nodes  The nodes to check.
     * @return bool
     */
    private function shouldProceedWithLoopValue($nodes)
    {
        $nodeCount = count($nodes);
        for ($i = 0; $i < $nodeCount; $i++) {
            $thisNode = $nodes[$i];

            if ($thisNode instanceof StatementSeparatorNode) {
                if ($i + 1 >= $nodeCount) {
                    return true;
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Creates a new NodeProcessor instance with the current configuration.
     *
     * @return NodeProcessor
     */
    public function cloneProcessor()
    {
        $processor = new NodeProcessor($this->loader, $this->envDetails);
        $processor->allowPhp($this->allowPhp);
        $processor->setRuntimeAssignments($this->runtimeAssignments);
        $processor->setIsConditionProcessor($this->isConditionalProcessor);

        if ($this->antlersParser != null) {
            $processor->setAntlersParserInstance($this->antlersParser);
        }

        $processor->cascade($this->cascade)->setDoStackIntercept($this->doStackIntercept);

        if ($this->runtimeConfiguration != null) {
            $processor->setRuntimeConfiguration($this->runtimeConfiguration);
        }

        return $processor;
    }

    /**
     * Triggers a runtime trace when enabled, and returns the content.
     *
     * @param  AntlersNode  $referenceNode  The reference node.
     * @param  string  $content  The content.
     * @return string
     */
    protected function measureBufferAppend(AntlersNode $referenceNode, $content)
    {
        if ($this->isTracingEnabled()) {
            $this->runtimeConfiguration->traceManager->traceOnExit($referenceNode, $content);
        }

        return $content;
    }

    /**
     * Evaluates an interpolated variable, and caches the result.
     *
     * @param  VariableNode  $node  The interpolated variable.
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     */
    public function reduceInterpolatedVariable(VariableNode $node)
    {
        if (! array_key_exists($node->name, $this->interpolationCache)) {
            $interpolationScope = $this->getActiveData();
            $interpolationValue = $this->cloneProcessor()
                ->setIsInterpolationProcessor(true)
                ->setData($interpolationScope)->reduce($node->interpolationNodes);

            $this->interpolationCache[$node->name] = $interpolationValue;
        }

        return $this->interpolationCache[$node->name];
    }

    /**
     * Executes the requested tag within the context of the current processor and provided node.
     *
     * @param  AntlersNode|ConditionNode  $node  The node.
     * @param  string  $tagName  The tag name.
     * @param  string  $tagMethod  The tag method to invoke.
     * @param  array  $additionalData  Any additional data to be supplied to the tag.
     * @return false|mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws TagNotFoundException
     * @throws Throwable
     */
    public function evaluateDeferredNodeAsTag($node, $tagName, $tagMethod, $additionalData = [])
    {
        $tagData = array_merge($this->getActiveData(), $additionalData);

        $parameters = [];
        $runtimeContent = '';

        if ($node instanceof AntlersNode) {
            $parameters = $node->getParameterValues($this, $this->getActiveData());
            $runtimeContent = $node->runtimeContent;
        }

        $parameters = array_merge($parameters, $additionalData);

        /** @var Tags $tag */
        $tag = $this->loader->load($tagName, [
            'parser' => $this->antlersParser,
            'params' => $parameters,
            'content' => $runtimeContent,
            'context' => $tagData,
            'tag' => $tagName,
            'tag_method' => $tagMethod,
        ]);

        return call_user_func([$tag, $tagMethod]);
    }

    /**
     * Lazily evaluates an interpolation region, by name.
     *
     * @param  string  $regionName  The interpolation region name.
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     */
    public function evaluateDeferredInterpolation($regionName)
    {
        if (! array_key_exists($regionName, $this->canHandleInterpolations)) {
            return $regionName;
        }

        if (! array_key_exists($regionName, $this->interpolationCache)) {
            $interpolationScope = $this->getActiveData();

            $interpolationValue = $this->cloneProcessor()
                ->setIsInterpolationProcessor(true)
                ->setData($interpolationScope)->reduce($this->canHandleInterpolations[$regionName]);

            $this->interpolationCache[$regionName] = $interpolationValue;
        }

        return $this->interpolationCache[$regionName];
    }

    /**
     * Evaluates a deferred logic group and returns the result.
     *
     * Deferred logic groups most often come from the processing
     * of language-level operator constructs, such as switch.
     *
     * @param  LogicGroup  $group  The group to evaluate.
     * @param  array  $additionalContext  Additional context when evaluating the deferred group.
     * @return array|bool|mixed|DirectionGroup|ListValueNode|SwitchGroup|null
     */
    public function evaluateDeferredLogicGroup(LogicGroup $group, array $additionalContext = [])
    {
        $scope = $this->getActiveData();

        if (! empty($additionalContext)) {
            $scope = $additionalContext + $scope;
        }

        $environment = new Environment();
        $environment->setProcessor($this);
        $environment->cascade($this->cascade)->setData($scope);

        return $environment->evaluate([$group]);
    }

    public function evaluateDeferredVariable(AbstractNode $deferredNode)
    {
        $environment = new Environment();
        $environment->setProcessor($this);
        $environment->cascade($this->cascade)->setData($this->getActiveData());

        return $environment->evaluate([$deferredNode]);
    }

    /**
     * Consults the runtime configuration to check
     * if the tag path should be evaluated.
     *
     * @param  string  $tagCheck  The tag to check.
     * @return bool
     *
     * @throws RuntimeException
     */
    public function guardRuntimeTag($tagCheck)
    {
        if (GlobalRuntimeState::$isEvaluatingUserData) {
            $guardList = GlobalRuntimeState::$bannedContentTagPaths;
        } else {
            $guardList = GlobalRuntimeState::$bannedTagPaths;
        }

        if (empty($guardList)) {
            return true;
        }

        if (Str::is($guardList, $tagCheck)) {
            Log::warning('Runtime Access Violation: '.$tagCheck, [
                'tag' => $tagCheck,
                'file' => GlobalRuntimeState::$currentExecutionFile,
                'trace' => GlobalRuntimeState::$templateFileStack,
            ]);

            if (GlobalRuntimeState::$throwErrorOnAccessViolation) {
                throw ErrorFactory::makeRuntimeError(
                    AntlersErrorCodes::RUNTIME_PROTECTED_TAG_ACCESS,
                    null,
                    'Protected tag access.'
                );
            }

            return false;
        }

        return true;
    }

    protected function checkPartialForNamedSlots(AntlersNode $node)
    {
        $namedSlots = [];

        foreach ($node->children as $child) {
            if ($child instanceof AntlersNode && ! $child->isComment && $child->name->name == 'slot') {
                $namedSlots[$child->name->methodPart] = $child;
            }
        }

        if (empty($namedSlots)) {
            return [false];
        }

        return [true, $namedSlots];
    }

    /**
     * Resets the internal interpolation cache.
     */
    private function clearInterpolationCache()
    {
        $this->interpolationCache = [];
    }

    /**
     * Processes all nodes and returns the reduced runtime value.
     *
     * This method typically returns a string, but can return
     * non-string values depending on the execution context,
     * such as returning values from interpolations.
     *
     * @param  AbstractNode[]  $processNodes  The nodes to process.
     * @return array|bool|false|Collection|mixed|AntlersString|DirectionGroup|ListValueNode|SwitchGroup|string|string[]|null
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws Throwable
     * @throws TagNotFoundException
     */
    public function reduce($processNodes)
    {
        $buffer = '';
        $processStack = [[$processNodes, 0]];

        while (! empty($processStack)) {
            $this->clearInterpolationCache();

            $details = array_pop($processStack);
            $nodes = $details[0];
            $startIndex = $details[1];
            $nodeCount = count($nodes);

            for ($i = $startIndex; $i < $nodeCount; $i += 1) {
                // Stop measuring any active tag at this point.
                // We will do it here instead of tracking
                // down every possible exit point below.
                $this->stopMeasuringTag();

                $node = $nodes[$i];

                $this->activeNode = $node;

                if ($this->isTracingEnabled()) {
                    $this->runtimeConfiguration->traceManager->traceOnEnter($node);
                }

                if ($node instanceof AntlersNode) {
                    if ($node->name != null) {
                        if ($node->name->name == 'elseif' || $node->name->name == 'if') {
                            continue;
                        }

                        if ($node->name->name == '___internal_debug' && $node->name->methodPart == 'peek' && ! empty(GlobalRuntimeState::$peekCallbacks)) {
                            foreach (GlobalRuntimeState::$peekCallbacks as $callback) {
                                if (is_callable($callback)) {
                                    $callback($this);
                                }
                            }

                            continue;
                        }
                    }

                    if ($this->encounteredBuilder && $this->builderNodeId != $node->refId) {
                        $this->encounteredBuilder = false;
                        $this->builderNodeId = null;
                    }

                    GlobalRuntimeState::$lastNode = $node;

                    if (GlobalDebugManager::$isConnected && GlobalDebugManager::$activeSessionLocator != null) {
                        GlobalDebugManager::checkNodeForLocatorBreakpoint($node, $this);
                    }

                    if ($node->isComment) {
                        continue;
                    }
                } elseif ($node instanceof ConditionNode) {
                    if (GlobalDebugManager::$isConnected && GlobalDebugManager::$activeSessionLocator != null) {
                        GlobalDebugManager::checkNodeForLocatorBreakpoint($node, $this);
                    }
                }

                if ($node instanceof PhpExecutionNode) {
                    if (GlobalRuntimeState::$isEvaluatingUserData && ! GlobalRuntimeState::$allowPhpInContent) {
                        if (GlobalRuntimeState::$throwErrorOnAccessViolation) {
                            throw ErrorFactory::makeRuntimeError(
                                AntlersErrorCodes::RUNTIME_PHP_NODE_USER_CONTENT_TAG,
                                $node,
                                'User content Antlers PHP tag.'
                            );
                        } else {
                            $logContent = $node->rawStart.$node->innerContent().$node->rawEnd;

                            Log::warning('PHP Node evaluated in user content: '.$logContent, [
                                'file' => GlobalRuntimeState::$currentExecutionFile,
                                'trace' => GlobalRuntimeState::$templateFileStack,
                                'content' => $node->innerContent(),
                            ]);
                        }

                        continue;
                    }

                    $buffer .= $this->evaluateAntlersPhpNode($node);

                    continue;
                }

                if ($node instanceof LiteralNode) {
                    $literalContent = $node->innerContent();

                    $buffer .= $literalContent;

                    if ($this->isTracingEnabled()) {
                        $this->runtimeConfiguration->traceManager->traceOnExit($node, $literalContent);
                    }

                    continue;
                }

                if ($node instanceof EscapedContentNode) {
                    $escapedContent = $node->innerContent();

                    $buffer .= $escapedContent;

                    if ($this->isTracingEnabled()) {
                        $this->runtimeConfiguration->traceManager->traceOnExit($node, $escapedContent);
                    }

                    continue;
                }

                if ($node instanceof AntlersNode) {
                    if ($node->isAbandoned()) {
                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                        }

                        continue;
                    }

                    if ($this->doStackIntercept && $node->name->name == 'stack') {
                        if ($node->isClosingTag) {
                            continue;
                        }

                        if ($node->isSelfClosing || $node->isClosedBy == null) {
                            $registeredStack = StackReplacementManager::registerStack($node->name->methodPart);
                        } else {
                            $registeredStack = StackReplacementManager::registerArrayStack(
                                $node->name->methodPart,
                                $node,
                                $this->cloneProcessor()->setData($this->getActiveData())->setDoStackIntercept(false)
                            );
                        }

                        $buffer .= $registeredStack;

                        continue;
                    } elseif (($node->name->name == 'push' || $node->name->name == 'prepend') && $node->isClosedBy != null) {
                        $currentData = $this->getActiveData();

                        $trimContentWhitespace = TypeCoercion::coerceBool($node->getSingleParameterValueByName(
                            'trim', $this->cloneProcessor(), $currentData, false
                        ));

                        $stackContent = $this->cloneProcessor()->setData($currentData)->reduce($node->children);

                        if ($node->name->name == 'push') {
                            StackReplacementManager::pushStack($node->name->methodPart, $stackContent, $trimContentWhitespace);
                        } else {
                            StackReplacementManager::prependStack($node->name->methodPart, $stackContent, $trimContentWhitespace);
                        }

                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, $stackContent);
                        }

                        continue;
                    }

                    if ($node->hasRecursiveNode) {
                        RecursiveNodeManager::registerRecursiveNode($node, $this->getActiveData());
                    }
                }

                if ($node instanceof ConditionNode && ! empty($node->logicBranches)) {
                    $lockData = $this->data;
                    $result = $this->conditionProcessor->process($node, $this->getActiveData());
                    $this->data = $lockData;

                    if ($result == null) {
                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                        }

                        continue;
                    } else {
                        if ($result instanceof ExecutionBranch) {
                            $processStack[] = [$nodes, $i + 1];
                            $processStack[] = [$result->nodes, 0];
                            break;
                        } else {
                            continue;
                        }
                    }
                } elseif ($node instanceof AntlersNode) {
                    if ($node->hasRecursiveNode && $node->recursiveReference->isNestedRecursive) {
                        RecursiveNodeManager::incrementDepth($node->recursiveReference);
                        $recursiveKey = $node->recursiveReference->name->name.'_depth';
                        $namedDepthMappings = RecursiveNodeManager::getNamedMappings();
                        $tmpActiveData = $namedDepthMappings + $this->getActiveData();

                        if (array_key_exists($recursiveKey, $namedDepthMappings)) {
                            $tmpActiveData['depth'] = $namedDepthMappings[$recursiveKey];
                        }

                        RecursiveNodeManager::decrementDepth($node->recursiveReference);

                        $this->updateCurrentScope($tmpActiveData);
                    }

                    if ($node instanceof RecursiveNode) {
                        $children = $this->pathDataManager->getRuntimeValue($node->pathReference, $this->getActiveData());
                        RecursiveNodeManager::incrementDepth($node);

                        if (! empty($children) && $this->isLoopable($children)) {
                            /** @var AntlersNode $recursiveParent */
                            $recursiveParent = RecursiveNodeManager::getRecursiveNode($node);

                            if ($node->isNestedRecursive) {
                                // Late recursive binding.
                                $lrbActiveData = $this->getActiveData();
                                unset($lrbActiveData['depth']);
                                unset($lrbActiveData[$node->content]);

                                RecursiveNodeManager::registerRecursiveNode($node->recursiveParent, $lrbActiveData);
                                $recursiveParent = RecursiveNodeManager::getRecursiveNode($node);
                            }

                            if ($recursiveParent == null) {
                                throw ErrorFactory::makeSyntaxError(
                                    AntlersErrorCodes::TYPE_RECURSIVE_NODE_UNASSOCIATED_PARENT,
                                    $node,
                                    'Encountered recursive node without a parent node during processing.'
                                );
                            }

                            $recursiveParent->activeDepth += 1;

                            $rootData = RecursiveNodeManager::getRecursiveRootData($node);
                            $rootData = array_merge($rootData, $this->getRuntimeAssignments());

                            // Prevent an infinite loop with arbitrary data.
                            if (array_key_exists($node->content, $rootData)) {
                                unset($rootData[$node->content]);
                            }

                            $parentParameterValues = array_values($recursiveParent->getParameterValues($this));

                            GlobalRuntimeState::$traceTagAssignments = true;
                            GlobalRuntimeState::$activeTracerCount += 1;
                            GlobalRuntimeState::$tracedRuntimeAssignments = $this->runtimeAssignments;

                            $currentAssignments = $this->getRuntimeAssignments();

                            $recursiveProcessor = $this->cloneProcessor();
                            $recursiveProcessor->setRuntimeAssignments($this->runtimeAssignments);

                            // Substitute the current node with the original parent.
                            foreach ($children as $childData) {
                                $namedDepthMapping = $node->content.'_depth';

                                $depths = RecursiveNodeManager::getActiveDepthNames();
                                $depths['depth'] = $recursiveParent->activeDepth;
                                $depths[$namedDepthMapping] = $recursiveParent->activeDepth;

                                // Keep the manager in sync.
                                RecursiveNodeManager::updateNamedDepth($node, $recursiveParent->activeDepth);

                                $childDataToUse = $childData;

                                if (! empty($recursiveParent->parameters)) {
                                    $lockData = $this->data;
                                    foreach ($recursiveParent->parameters as $param) {
                                        if ($param->name === 'scope') {
                                            $childDataToUse = $this->runModifier($param->name, $parentParameterValues, $childDataToUse, $rootData);
                                        }
                                    }
                                    $this->data = $lockData;
                                    $childDataToUse = $childDataToUse + $rootData;
                                } else {
                                    $childDataToUse = $childDataToUse + $rootData;
                                }

                                // Apply depths after merging root data to prevent overwriting.
                                $childDataToUse = array_merge($childDataToUse, $depths);

                                // Add an empty array for consistency.
                                if (! array_key_exists($node->content, $childDataToUse)) {
                                    $childDataToUse[$node->content] = [];
                                }

                                $result = $recursiveProcessor->replaceData($childDataToUse)->reduce($recursiveParent->children);

                                $recursiveAssignmentValues = $recursiveProcessor->getRuntimeAssignments();

                                if (! empty($recursiveAssignmentValues)) {
                                    foreach ($recursiveAssignmentValues as $assignVar => $assignVal) {
                                        if (array_key_exists($assignVar, $currentAssignments)) {
                                            GlobalRuntimeState::$tracedRuntimeAssignments[$assignVar] = $assignVal;
                                        }
                                    }
                                }

                                $buffer .= $this->measureBufferAppend($node, $result);

                                if ($this->isTracingEnabled()) {
                                    $this->runtimeConfiguration->traceManager->traceOnExit($node, $result);
                                }
                            }

                            $recursiveParent->activeDepth -= 1;
                            RecursiveNodeManager::releaseRecursiveNode($node);

                            if (GlobalRuntimeState::$activeTracerCount == 0) {
                                GlobalRuntimeState::$traceTagAssignments = false;
                                GlobalRuntimeState::$tracedRuntimeAssignments = [];
                            }
                        }

                        continue;
                    }

                    if ($node->isClosingTag) {
                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                        }

                        continue;
                    }

                    $currentProcessorCanHandleTagValue = false;
                    $tagCallbackResult = null;

                    $lockData = $this->data;
                    $shouldProcessAsTag = $this->shouldProcessAsTag($node);
                    $this->data = $lockData;

                    if (! $shouldProcessAsTag) {
                        $this->profilingTagName = null;
                    }

                    if ($shouldProcessAsTag) {
                        $tagName = $node->name->getCompoundTagName();
                        $tagMethod = $node->name->getMethodName();
                        $isCacheTag = false;

                        $this->startMeasuringTag($tagName, $node);

                        if (! empty($node->processedInterpolationRegions)) {
                            foreach ($node->processedInterpolationRegions as $region => $regionNodes) {
                                $this->canHandleInterpolations[$region] = $regionNodes;

                                if (Str::contains($tagMethod, $region)) {
                                    $tagMethod = str_replace($region, $this->evaluateDeferredInterpolation($region), $tagMethod);
                                }
                            }

                            $tagName = $node->name->name.':'.$tagMethod;
                        }

                        if ($this->encounteredBuilder) {
                            $tagName = 'query';
                            $tagMethod = $node->name->name;
                        }

                        $guardCheck = $tagName.':'.$tagMethod;

                        if (! $this->guardRuntimeTag($guardCheck)) {
                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                            }

                            continue;
                        }

                        $lockData = $this->data;
                        GlobalRuntimeState::$globalTagEnterStack[] = $node;

                        $tagParameters = $node->getParameterValues($this, $this->getActiveData());
                        $this->data = $lockData;

                        $tagActiveData = $this->getActiveData();

                        $contributedPrefixHandles = 0;

                        if ($node->name->name == 'partial') {
                            $lockData = $this->data;
                            $namedSlotResults = $this->checkPartialForNamedSlots($node);

                            if ($namedSlotResults[0] === true) {
                                $namedSlots = $namedSlotResults[1];

                                $slotProcessor = $this->cloneProcessor();
                                $slotProcessor->setData($tagActiveData);

                                /** @var AntlersNode $slot */
                                foreach ($namedSlots as $slotName => $slot) {
                                    $tagActiveData['slot:'.$slotName] = trim($slotProcessor->reduce($slot->children));
                                }

                                $tagActiveData[GlobalRuntimeState::createIndicatorVariable(GlobalRuntimeState::INDICATOR_NAMED_SLOTS_AVAILABLE)] = true;
                            }
                            $this->data = $lockData;
                        }

                        if ($node->name->name == 'partial' || $node->name->name == 'scope') {
                            if (array_key_exists('handle_prefix', $tagParameters)) {
                                $handlePrefixes = $tagParameters['handle_prefix'];

                                if (is_array($handlePrefixes)) {
                                    foreach (array_reverse($handlePrefixes) as $prefix) {
                                        GlobalRuntimeState::$prefixState[] = $prefix;
                                        $contributedPrefixHandles += 1;
                                    }
                                } else {
                                    GlobalRuntimeState::$prefixState[] = $tagParameters['handle_prefix'];
                                    $contributedPrefixHandles = 1;
                                }
                            }
                        }

                        $tagToLoad = $node->name->name;

                        if ($this->encounteredBuilder) {
                            $tagToLoad = 'query';
                            $tagParameters['builder'] = $this->resolvedBuilder;
                        }

                        if (! empty($this->runtimeAssignments)) {
                            GlobalRuntimeState::$traceTagAssignments = true;
                            GlobalRuntimeState::$tracedRuntimeAssignments = array_merge(
                                $this->runtimeAssignments,
                                GlobalRuntimeState::$tracedRuntimeAssignments
                            );
                        }
                        /** @var Tags $tag */
                        $tag = $this->loader->load($tagToLoad, [
                            'parser' => $this->antlersParser,
                            'params' => $tagParameters,
                            'content' => $node->runtimeContent,
                            'context' => $tagActiveData,
                            'tag' => $tagName,
                            'tag_method' => $tagMethod,
                        ]);

                        if (in_array(CachesOutput::class, class_implements($tag))) {
                            $isCacheTag = true;
                            GlobalRuntimeState::$isCacheEnabled = true;
                        }

                        $methodToCall = $node->name->getRuntimeMethodName();

                        if ($this->encounteredBuilder) {
                            $methodToCall = 'index';
                        }

                        $beforeAssignments = $this->runtimeAssignments;
                        $currentIsolationState = GlobalRuntimeState::$requiresRuntimeIsolation;
                        GlobalRuntimeState::$requiresRuntimeIsolation = true;
                        GlobalRuntimeState::$evaulatingTagContents = true;

                        $args = [];

                        if ($methodToCall == 'wildcard') {
                            $args = [$tagMethod];
                        }

                        try {
                            $output = call_user_func([$tag, $methodToCall], ...$args);

                            if ($isCacheTag) {
                                GlobalRuntimeState::$isCacheEnabled = false;
                            }
                        } catch (Exception $e) {
                            throw $e;
                        } finally {
                            GlobalRuntimeState::$requiresRuntimeIsolation = $currentIsolationState;
                            GlobalRuntimeState::$evaulatingTagContents = false;
                            $this->stopMeasuringTag();
                        }

                        $afterAssignments = $this->runtimeAssignments;

                        foreach ($afterAssignments as $assignedVar => $val) {
                            if (! array_key_exists($assignedVar, $beforeAssignments)) {
                                unset($this->runtimeAssignments[$assignedVar]);
                            }
                        }

                        // While the PathDataManager can resolve builder instances,
                        // we will handle this case here so that the values can
                        // "fall through" to the rest of the Runtime process
                        // and avoid multiple lookups of the same data.
                        if ($this->encounteredBuilder) {
                            $dataSetName = $node->name->name;

                            $tempLockData = $lockData;
                            $activeLockFrame = [];

                            if (count($tempLockData) > 0) {
                                $activeLockFrame = $tempLockData[count($tempLockData) - 1];
                            }

                            if ($output instanceof Collection) {
                                $output = $output->all();
                            }

                            $newData = [
                                $dataSetName => $output,
                            ];

                            $builderScope = array_merge($activeLockFrame, $newData);

                            // It is important to reset these so builder instances do not leak.
                            $this->encounteredBuilder = false;
                            $this->resolvedBuilder = null;
                        }

                        RuntimeParser::pushNodeCache($node->runtimeContent, $node->children);

                        if ($node->name->name == 'yield') {
                            // If the current processor instance is a conditional processor, we
                            // will simply return whether the section has been registered.
                            if ($this->isConditionalProcessor) {
                                $this->stopMeasuringTag();

                                return LiteralReplacementManager::hasRegisteredSectionName($tagMethod);
                            }

                            GlobalRuntimeState::$yieldCount += 1;
                            // Wrap it in a partial thing.
                            $wrapName = 'section:'.$tagMethod.'__yield'.GlobalRuntimeState::$yieldCount;
                            $bufferOverride = LiteralReplacementManager::registerRegion($wrapName, $tagMethod, $output);

                            if (! array_key_exists(GlobalRuntimeState::$environmentId, GlobalRuntimeState::$yieldStacks)) {
                                GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId] = [];
                            }

                            if (! array_key_exists($tagMethod, GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId])) {
                                GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId][$tagMethod] = [];
                            }

                            GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId][$tagMethod][] = GlobalRuntimeState::$yieldCount;

                            $buffer .= $bufferOverride;
                            $this->data = $lockData;

                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, $bufferOverride);
                            }

                            continue;
                        } elseif ($node->name->name == 'section') {
                            // We need to reach into the cascade to get the rendered content.
                            if (! array_key_exists(GlobalRuntimeState::$environmentId, GlobalRuntimeState::$yieldStacks)) {
                                GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId] = [];
                            }

                            if (! array_key_exists($tagMethod, GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId])) {
                                GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId][$tagMethod] = [];
                            }

                            $activeYield = array_pop(GlobalRuntimeState::$yieldStacks[GlobalRuntimeState::$environmentId][$tagMethod]);

                            // If we receive a NULL, override this with a one to ensure "override" behavior.
                            if ($activeYield === null) {
                                $activeYield = 1;
                            }

                            $sectionName = 'section:'.$tagMethod.'__yield'.$activeYield;

                            LiteralReplacementManager::registerRegionReplacement(
                                $sectionName,
                                $tagMethod,
                                $this->cascade->sections()->get($tagMethod)
                            );

                            if (GlobalRuntimeState::$isCacheEnabled) {
                                LiteralReplacementManager::$cachedSections[] = [
                                    $sectionName,
                                    $tagMethod,
                                    (string) $this->cascade->sections()->get($tagMethod),
                                ];
                            }

                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                            }

                            $this->data = $lockData;

                            continue;
                        }

                        $this->data = $lockData;

                        if (is_object($output)) {
                            if ($output instanceof Collection) {
                                $output = RuntimeValues::resolveWithRuntimeIsolation($output);
                            }

                            $output = PathDataManager::reduceForAntlers($output, $this->antlersParser, $this->getActiveData(), $node->isClosedBy != null);
                        }

                        if ($this->isInterpolationProcessor) {
                            $buffer = $output;
                            array_pop(GlobalRuntimeState::$globalTagEnterStack);

                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, $buffer);
                            }

                            $this->data = $lockData;

                            continue;
                        }

                        // Allow tags to return an array. We'll parse it for them.
                        if (is_array($output)) {
                            if (empty($output)) {
                                $output = $tag->parseNoResults();
                                $currentProcessorCanHandleTagValue = false;
                            } else {
                                GlobalRuntimeState::$traceTagAssignments = true;
                                GlobalRuntimeState::$activeTracerCount += 1;
                                GlobalRuntimeState::$tracedRuntimeAssignments = $this->runtimeAssignments;

                                // Only revert to parseLoop behavior if the tag contains
                                // parameters that are likely to dramatically change
                                // the scope or overall output of the tag result
                                if ($node->hasScopeAdjustingParameters) {
                                    $tagAssocOutput = $output;
                                    $output = Arr::assoc($output) ? (string) $tag->parse($output) : (string) $tag->parseLoop($this->addLoopIterationVariables($output));
                                    $tagCallbackResult = null;
                                    $currentProcessorCanHandleTagValue = false;

                                    $adjustedScope = false;

                                    // It is possible that the tag has injected a variable
                                    // that was already in the scope. If this is the case,
                                    // let's check if the final value is the same as the
                                    // value returned from the tag. If so, we will not
                                    // push this new value back up the stack as no
                                    // explicit re-assignment has occurred.
                                    foreach ($this->scopeAdjustingParams as $paramName) {
                                        if (array_key_exists($paramName, $tagParameters)) {
                                            $potentialVariableCollisionName = $tagParameters[$paramName];

                                            if (array_key_exists($potentialVariableCollisionName, $beforeAssignments) && array_key_exists($potentialVariableCollisionName, $tagAssocOutput)) {
                                                if ($this->data[count($this->data) - 1][$potentialVariableCollisionName] == $tagAssocOutput[$potentialVariableCollisionName]) {
                                                    unset($this->runtimeAssignments[$potentialVariableCollisionName]);
                                                    $adjustedScope = true;
                                                }
                                            }
                                        }
                                    }

                                    if ($adjustedScope) {
                                        GlobalRuntimeState::$tracedRuntimeAssignments = $this->runtimeAssignments;
                                    }
                                } else {
                                    $tagCallbackResult = $output;
                                    $currentProcessorCanHandleTagValue = true;
                                }

                                GlobalRuntimeState::$activeTracerCount -= 1;

                                $this->data = $lockData;
                                $this->processAssignments(GlobalRuntimeState::$tracedRuntimeAssignments);
                                $lockData = $this->data;

                                if (GlobalRuntimeState::$activeTracerCount == 0) {
                                    GlobalRuntimeState::$traceTagAssignments = false;
                                    GlobalRuntimeState::$tracedRuntimeAssignments = [];
                                }
                            }
                        }

                        array_pop(GlobalRuntimeState::$globalTagEnterStack);

                        if ($contributedPrefixHandles > 0) {
                            while ($contributedPrefixHandles > 0) {
                                array_pop(GlobalRuntimeState::$prefixState);
                                $contributedPrefixHandles -= 1;
                            }
                        }

                        $this->data = $lockData;

                        if (! $currentProcessorCanHandleTagValue) {
                            $buffer .= $this->measureBufferAppend($node, $output);

                            if (! empty(GlobalRuntimeState::$tracedRuntimeAssignments)) {
                                $runtimeAssignmentsToProcess = [];

                                foreach (GlobalRuntimeState::$tracedRuntimeAssignments as $assignmentVar => $value) {
                                    if (array_key_exists($assignmentVar, $this->runtimeAssignments)) {
                                        $runtimeAssignmentsToProcess[$assignmentVar] = $value;
                                    }
                                }

                                if (! empty($runtimeAssignmentsToProcess)) {
                                    $this->processAssignments($runtimeAssignmentsToProcess);
                                }
                            }
                        }

                        if (! $currentProcessorCanHandleTagValue) {
                            continue;
                        }
                    }

                    if ($node->name->name == 'once') {
                        $node->abandon();

                        $results = $this->cloneProcessor()->setData($this->getActiveData())->reduce($node->children);

                        $buffer .= $this->measureBufferAppend($node, $results);

                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, $results);
                        }

                        continue;
                    }

                    $val = null;
                    $runtimeResolveLoopVar = false;

                    if ($tagCallbackResult == null) {
                        if (count($node->parameters) == 0 && ! empty($node->runtimeNodes)) {
                            $environmentData = $this->getActiveData();

                            if (! empty($node->processedInterpolationRegions)) {
                                foreach ($node->processedInterpolationRegions as $region => $regionNodes) {
                                    $this->canHandleInterpolations[$region] = $regionNodes;
                                }
                            }

                            $environment = new Environment();
                            $environment->cascade($this->cascade)->setData($environmentData);
                            $environment->setProcessor($this);

                            if ($this->isProvidingParameterContent) {
                                $environment->setReduceFinal(false);
                            }

                            if ($node->isTagNode && $node->isPaired() == false && ! $this->isProvidingParameterContent) {
                                $environment->setReduceFinal(false);
                            }

                            if ($node->pathReference != null && $node->pathReference->isStrictVariableReference) {
                                $environment->setReduceFinal(false);
                            }

                            if ($this->antlersParser != null) {
                                $environment->setParserInstance($this->antlersParser);
                            }

                            if ($node->hasParsedRuntimeNodes == false) {
                                // Parse will rebuild the modifier chain. Reset so we don't double up!
                                foreach ($node->runtimeNodes as $runtimeNode) {
                                    $runtimeNode->modifierChain = null;
                                }

                                $parsedNodes = $this->languageParser->parse($node->runtimeNodes);

                                $node->parsedRuntimeNodes = $parsedNodes;
                                $node->hasParsedRuntimeNodes = true;
                            }

                            $restoreData = $this->data;

                            if ($node->isPaired()) {
                                $environment->setReduceFinal(false);
                            } else {
                                $environment->setReduceFinal(true);
                            }

                            $environment->setIsPaired($node->isPaired());

                            if (! empty($node->processedInterpolationRegions)) {
                                $environment->setDataManagerInterpolations($node->processedInterpolationRegions);
                            }

                            $runtimeResult = $environment->evaluate($node->parsedRuntimeNodes);

                            $this->data = $restoreData;

                            if (is_string($runtimeResult) && $node->hasProcessedInterpolationRegions) {
                                $interpolationScope = $this->getActiveData();

                                foreach ($node->processedInterpolationRegions as $region => $regionNodes) {
                                    if (Str::contains($runtimeResult, $region)) {
                                        if (! array_key_exists($region, $this->interpolationCache)) {
                                            $interpolationValue = $this->cloneProcessor()
                                                ->setIsInterpolationProcessor(true)
                                                ->setData($interpolationScope)->reduce($regionNodes);
                                            $this->interpolationCache[$region] = $interpolationValue;
                                        }

                                        $runtimeResult = str_replace($region, $this->interpolationCache[$region], $runtimeResult);
                                    }
                                }
                            }

                            $assignments = $environment->getAssignments();

                            if (! empty($assignments)) {
                                $runtimeAssignmentsToProcess = [];

                                foreach ($assignments as $assignmentVar => $assignmentValue) {
                                    if (array_key_exists($assignmentVar, $this->runtimeAssignments)) {
                                        $runtimeAssignmentsToProcess[$assignmentVar] = $assignmentValue;
                                    }
                                }

                                $this->processAssignments($runtimeAssignmentsToProcess);
                                $this->runtimeAssignments = $assignments + $this->runtimeAssignments;
                            }

                            $this->updateCurrentScope($environment->getData());

                            // If we are a tag pair, let's reduce the runtime result. Anything
                            // that is array-like will now be turned into an array. If we
                            // are not a tag pair, any value object that returns as a
                            // string will be left alone to account for string-likes.
                            if ($node->isClosedBy != null) {
                                $runtimeResult = PathDataManager::reduceForAntlers($runtimeResult, $this->antlersParser, $this->getActiveData());
                            }

                            if ($node->isClosedBy != null && count($node->parsedRuntimeNodes) > 0) {
                                if (count($node->parsedRuntimeNodes) > 1) {
                                    if ($node->parsedRuntimeNodes[count($node->parsedRuntimeNodes) - 1] instanceof SemanticGroup) {
                                        throw ErrorFactory::makeRuntimeError(
                                            AntlersErrorCodes::TYPE_INVALID_ASSIGNMENT_LOOP_PAIR,
                                            $node,
                                            'Cannot iterate assignment tag pair with complex expressions.'
                                        );
                                    }
                                }

                                $checkNodes = $node->parsedRuntimeNodes[0];

                                if ($checkNodes instanceof SemanticGroup) {
                                    $checkNodes = $checkNodes->nodes;
                                }

                                if (count($checkNodes) > 2) {
                                    if ($checkNodes[1] instanceof LeftAssignmentOperator) {
                                        if (! $this->shouldProceedWithLoopValue($checkNodes)) {
                                            throw ErrorFactory::makeRuntimeError(
                                                AntlersErrorCodes::TYPE_INVALID_ASSIGNMENT_LOOP_PAIR,
                                                $node,
                                                'Cannot iterate assignment tag pair with complex expressions.'
                                            );
                                        }

                                        if (array_key_exists($node->name->name, $assignments)) {
                                            // Scope the runtime result to the dynamic assignment
                                            // if this node also has a closing loop pair.
                                            $runtimeResult = $assignments[$node->name->name];
                                        }
                                    }
                                }
                            }

                            if (is_array($runtimeResult) || $runtimeResult instanceof Collection) {
                                $runtimeResolveLoopVar = true;
                                $val = $runtimeResult;
                            } else {
                                if (! $runtimeResult instanceof Builder) {
                                    if ($runtimeResult === null) {
                                        continue;
                                    }

                                    if ($this->guardRuntime($node, $runtimeResult)) {
                                        $buffer .= $this->measureBufferAppend($node, $this->modifyBufferAppend($runtimeResult));
                                    }

                                    if ($this->isTracingEnabled()) {
                                        $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                                    }

                                    continue;
                                }
                            }
                        } elseif (count($node->runtimeNodes) == 0 && $node->isTagNode) {
                            $dataRetriever = new PathDataManager();
                            if (! empty(GlobalRuntimeState::$prefixState)) {
                                $dataRetriever->setHandlePrefixes(array_reverse(GlobalRuntimeState::$prefixState));
                            }

                            $dataRetriever->setNodeProcessor($this);
                            $dataRetriever->cascade($this->cascade);

                            if ($this->antlersParser != null) {
                                $dataRetriever->setAntlersParser($this->antlersParser);
                            }

                            $val = $dataRetriever->getRuntimeValue($node->pathReference, $this->getActiveData());
                        }

                        if ($this->isInterpolationProcessor && empty($node->parameters)) {
                            $buffer = $val;

                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, $val);
                            }

                            continue;
                        }
                    } else {
                        $val = $tagCallbackResult;
                        $runtimeResolveLoopVar = true;
                    }

                    $runLoopMagic = true;

                    if ($node->isClosedBy == null && $this->runtimeConfiguration != null && $this->runtimeConfiguration->fatalErrorOnUnpairedLoop) {
                        throw ErrorFactory::makeRuntimeError(
                            AntlersErrorCodes::TYPE_RUNTIME_FATAL_UNPAIRED_LOOP_END,
                            $node,
                            'Fatal Error: Unpaired loop tag detected during iteration.'
                        );
                    }

                    if ($runtimeResolveLoopVar == false) {
                        if ($node->pathReference != null) {
                            $dataRetriever = new PathDataManager();
                            if (! empty(GlobalRuntimeState::$prefixState)) {
                                $dataRetriever->setHandlePrefixes(array_reverse(GlobalRuntimeState::$prefixState));
                            }

                            $dataRetriever->setNodeProcessor($this);
                            $dataRetriever->cascade($this->cascade);

                            if ($this->antlersParser != null) {
                                $dataRetriever->setAntlersParser($this->antlersParser);
                            }

                            $dataRetriever->setIsPaired($node->isClosedBy != null);
                            $valDetails = $dataRetriever->getDataWithExistence($node->pathReference, $this->getActiveData());

                            if ($valDetails[0] == false) {
                                if ($this->isTracingEnabled()) {
                                    $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                                }

                                continue;
                            }

                            $val = $valDetails[1];
                        }
                    }

                    if ($val instanceof Builder) {
                        $val = $val->get()->all();
                    }

                    $executedParamModifiers = false;

                    if ($tagCallbackResult != null) {
                        $executedParamModifiers = true;
                    }

                    if ($node->hasParameters && $tagCallbackResult == null) {
                        $curIsPaired = $this->pathDataManager->getIsPaired();
                        $curReduceFinal = $this->pathDataManager->getReduceFinal();
                        $this->pathDataManager->setIsPaired(false);
                        $this->pathDataManager->setReduceFinal(false);
                        $val = $this->pathDataManager->getData($node->pathReference, $this->getActiveData());
                        $this->pathDataManager->setIsPaired($curIsPaired);
                        $this->pathDataManager->setReduceFinal($curReduceFinal);

                        if (! $shouldProcessAsTag && $val !== null) {
                            foreach ($node->parameters as $param) {
                                if (ModifierManager::isModifier($param)) {
                                    $lockData = $this->data;
                                    $activeData = $this->getActiveData();
                                    $paramValues = [];

                                    if ($param->isVariableReference) {
                                        $varValue = $node->getSingleParameterValue($param, $this, $activeData);

                                        if ($varValue == 'void::'.GlobalRuntimeState::$environmentId) {
                                            $this->data = $lockData;

                                            continue;
                                        }

                                        $paramValues[] = $varValue;
                                    } else {
                                        $tempValues = $node->getModifierParameterValuesForParameter($param, $activeData);

                                        foreach ($tempValues as $paramName => $value) {
                                            $containedInterpolation = false;

                                            foreach ($node->interpolationRegions as $regionName => $region) {
                                                if (Str::contains($value, $regionName)) {
                                                    $containedInterpolation = true;
                                                    if (array_key_exists($regionName, $this->canHandleInterpolations) == false) {
                                                        $this->canHandleInterpolations[$regionName] = $node->processedInterpolationRegions[$regionName];
                                                    }

                                                    $interpolationResult = $this->evaluateDeferredInterpolation($regionName);

                                                    $resolvedValue = null;

                                                    if ($value == $regionName) {
                                                        $resolvedValue = $interpolationResult;
                                                    } else {
                                                        $resolvedValue = str_replace($regionName, (string) $interpolationResult, $value);
                                                    }

                                                    $paramValues[$paramName] = $resolvedValue;
                                                }
                                            }

                                            if (! $containedInterpolation) {
                                                $paramValues[$paramName] = $value;
                                            }
                                        }
                                    }

                                    if ($val instanceof Value) {
                                        if ($val->shouldParseAntlers()) {
                                            GlobalRuntimeState::$isEvaluatingUserData = true;
                                            GlobalRuntimeState::$isEvaluatingData = true;
                                            GlobalRuntimeState::$userContentEvalState = [
                                                $val,
                                                $node,
                                            ];

                                            $val = $val->antlersValue($this->antlersParser, $this->getActiveData());
                                            GlobalRuntimeState::$userContentEvalState = null;
                                            GlobalRuntimeState::$isEvaluatingUserData = false;
                                            GlobalRuntimeState::$isEvaluatingData = false;
                                        } else {
                                            GlobalRuntimeState::$isEvaluatingData = true;
                                            $val = $val->value();
                                            GlobalRuntimeState::$isEvaluatingData = false;
                                        }
                                    }

                                    if ($val instanceof AntlersString) {
                                        $val = (string) $val;
                                    }

                                    if ($this->isLoopable($val) && is_array($val) && ! Arr::isAssoc($val) && count($val) > 0 && ! is_object($val[0])) {
                                        $val = $this->addLoopIterationVariables($val);
                                    }

                                    $val = $this->runModifier($param->name, $paramValues, $val, $activeData);

                                    if ($val === null) {
                                        $this->data = $lockData;
                                        break;
                                    }
                                } else {
                                    if ($param->name === 'raw') {
                                        if ($val instanceof Value) {
                                            GlobalRuntimeState::$isEvaluatingData = true;
                                            $val = $val->raw();
                                            GlobalRuntimeState::$isEvaluatingData = false;
                                        }
                                    } elseif ($param->name === 'noparse') {
                                        if ($val instanceof Value) {
                                            GlobalRuntimeState::$isEvaluatingData = true;
                                            $val = $val->value();
                                            GlobalRuntimeState::$isEvaluatingData = false;
                                        }
                                    } else {
                                        // Throw an exception here to maintain consistent behavior with the regex parser.
                                        throw new ModifierNotFoundException($param->name);
                                    }
                                }

                                $this->data = $lockData;
                            }

                            if ($val instanceof Collection) {
                                $val = $val->all();
                                if (! Arr::isAssoc($val)) {
                                    $val = $this->addLoopIterationVariables($val);
                                }
                            }
                        }
                        $executedParamModifiers = true;

                        if (is_array($val) && ! Arr::isAssoc($val)) {
                            $val = $this->addLoopIterationVariables($val);
                        }
                        $lockData = $this->data;
                    }

                    if ($this->guardRuntime($node, $val) == false) {
                        if ($this->isTracingEnabled()) {
                            $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                        }

                        continue;
                    }

                    if ($this->isInterpolationProcessor && is_array($val)) {
                        $buffer = $val;

                        continue;
                    }

                    $lockData = $this->data;

                    if ($runLoopMagic) {
                        if ($node->hasRecursiveNode) {
                            RecursiveNodeManager::incrementDepth($node->recursiveReference);
                        }

                        if ($this->isLoopable($val)) {
                            if (! empty($val)) {
                                $val = $this->massageKeys($val);

                                if (is_array($val) && Arr::isAssoc($val)) {
                                    $tmpArrayData = $this->getActiveData();

                                    if ($node->hasRecursiveNode) {
                                        $namedDepthMappings = RecursiveNodeManager::getNamedMappings();
                                        $recursiveKey = $node->recursiveReference->name->name.'_depth';
                                        $tmpArrayData = $namedDepthMappings + $tmpArrayData;

                                        if (array_key_exists($recursiveKey, $tmpArrayData)) {
                                            $tmpArrayData['depth'] = $tmpArrayData[$recursiveKey];
                                        }
                                    }

                                    $evalData = $val + $tmpArrayData;
                                    $this->pushScope($node, $evalData);

                                    $childNodes = $node->children;

                                    $processor = new NodeProcessor($this->loader, $this->envDetails);

                                    $processor->allowPhp($this->allowPhp);
                                    $processor->setAntlersParserInstance($this->antlersParser);
                                    $processor->setData($evalData);
                                    $processor->cascade($this->cascade);
                                    $processor->setRuntimeAssignments($this->runtimeAssignments);

                                    if ($this->runtimeConfiguration != null) {
                                        $processor->setRuntimeConfiguration($this->runtimeConfiguration);
                                    }
                                    $assocOutput = $processor->reduce($childNodes);

                                    $buffer .= $this->measureBufferAppend($node, $this->modifyBufferAppend($assocOutput));

                                    $runtimeAssignmentsToProcess = $processor->getRuntimeAssignments();

                                    $this->popScope($node->refId);

                                    if (! empty($runtimeAssignmentsToProcess)) {
                                        $this->data = $lockData;
                                        $this->processAssignments($runtimeAssignmentsToProcess);
                                        $lockData = $this->data;
                                    }
                                } else {
                                    if (! $executedParamModifiers || $tagCallbackResult != null) {
                                        $val = $this->addLoopIterationVariables($val);
                                    }

                                    $runtimeData = $this->getActiveData();
                                    $processor = new NodeProcessor($this->loader, $this->envDetails);
                                    $processor->allowPhp($this->allowPhp);
                                    $processor->cascade($this->cascade);
                                    $processor->setAntlersParserInstance($this->antlersParser);
                                    $processor->setRuntimeAssignments($this->runtimeAssignments);

                                    if ($this->runtimeConfiguration != null) {
                                        $processor->setRuntimeConfiguration($this->runtimeConfiguration);
                                    }

                                    $dataCount = count($this->data);
                                    $loopBuffer = '';

                                    $tChildren = $node->children;

                                    if ($tagCallbackResult != null) {
                                        GlobalRuntimeState::$traceTagAssignments = true;
                                    }

                                    $runtimeAssignmentsToProcess = [];

                                    foreach ($val as $procVal) {
                                        if ($tagCallbackResult != null) {
                                            $runtimeData = array_merge($runtimeData, GlobalRuntimeState::$tracedRuntimeAssignments);
                                        }
                                        $processor->replaceData(PathDataManager::reduce($procVal) + $runtimeData);
                                        $loopBuffer .= $this->modifyBufferAppend($processor->reduce($tChildren));

                                        $runtimeAssignments = $processor->getRuntimeAssignments();

                                        if ($tagCallbackResult != null) {
                                            $runtimeAssignments = array_merge($runtimeAssignments, GlobalRuntimeState::$tracedRuntimeAssignments);
                                        }

                                        if (! empty($runtimeAssignments)) {
                                            $procActive = $processor->getActiveData();

                                            foreach ($runtimeAssignments as $var => $varValue) {
                                                if (array_key_exists($var, $procActive)) {
                                                    $runtimeAssignments[$var] = $procActive[$var];
                                                }
                                            }

                                            foreach ($runtimeAssignments as $assignmentVar => $assignmentValue) {
                                                if (array_key_exists($assignmentVar, $this->runtimeAssignments)) {
                                                    $runtimeAssignmentsToProcess[$assignmentVar] = $assignmentValue;
                                                }
                                            }

                                            $runtimeData = array_merge($runtimeData, $runtimeAssignmentsToProcess);
                                        }

                                        $processor->setRuntimeAssignments($runtimeAssignments);

                                        if (count($this->data) > $dataCount) {
                                            $this->popLoopScope();
                                        }
                                    }

                                    $this->data = $lockData;
                                    $this->processAssignments($runtimeAssignmentsToProcess);
                                    $lockData = $this->data;

                                    $buffer .= $this->measureBufferAppend($node, $loopBuffer);
                                }
                            }

                            if ($this->isTracingEnabled()) {
                                $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                            }

                            $this->data = $lockData;

                            continue;
                        }
                    }

                    if ($executedParamModifiers == false && $node->hasParameters) {
                        $curActiveData = [];

                        if (! empty($node->interpolationRegions)) {
                            $curActiveData = $this->getActiveData();
                        }

                        foreach ($node->parameters as $param) {
                            if (ModifierManager::isModifier($param)) {
                                $lockData = $this->data;
                                $val = $this->runModifier($param->name, array_values($node->getParameterValues($this, $curActiveData)), $val, $this->getActiveData());
                                $this->data = $lockData;
                            }
                        }
                    }

                    $buffer .= $this->measureBufferAppend($node, $this->modifyBufferAppend($val));

                    if ($this->isTracingEnabled()) {
                        $this->runtimeConfiguration->traceManager->traceOnExit($node, null);
                    }
                    $this->data = $lockData;

                    continue;
                }
            }
        }

        // If we finished processing the stack, just call this
        // one last time to make sure we didn't miss anything.
        $this->stopMeasuringTag();

        if ($this->allowPhp) {
            $buffer = $this->evaluatePhp($buffer);
        }

        return $buffer;
    }

    /**
     * Executes any PHP within the provided buffer and returns the result.
     *
     * @param  string|array  $buffer  The content to evaluate.
     * @return false|string|array
     */
    protected function evaluatePhp($buffer)
    {
        if (is_array($buffer) || $this->isLoopable($buffer)) {
            return $buffer;
        }

        if (GlobalRuntimeState::$allowPhpInContent == false && GlobalRuntimeState::$isEvaluatingUserData) {
            return StringUtilities::sanitizePhp($buffer);
        }

        if (! Str::contains($buffer, $this->validPhpOpenTags)) {
            return $buffer;
        }

        ob_start();

        try {
            extract($this->getActiveData());
            eval('?>'.$buffer.'<?php ');
        } catch (ParseError $e) {
            throw new SyntaxError("{$e->getMessage()} on line {$e->getLine()} of:\n\n{$buffer}");
        }

        return ob_get_clean();
    }

    protected function evaluateAntlersPhpNode(PhpExecutionNode $___node)
    {
        if (! GlobalRuntimeState::$allowPhpInContent && GlobalRuntimeState::$isEvaluatingUserData) {
            return StringUtilities::sanitizePhp($___node->content);
        }

        $___phpBuffer = '';

        if ($___node->isEchoNode == false) {
            $___phpBuffer = $___node->content;

            if (! Str::contains($___node->content, $this->validPhpOpenTags)) {
                $___phpBuffer = '<?php '.$___node->content.' ?>';
            }
        } else {
            $___phpBuffer = '<?php echo '.$___node->content.'; ?>';
        }

        $___phpRuntimeAssignments = [];
        ob_start();

        try {
            extract($this->getActiveData());
            $___antlersVarBefore = get_defined_vars();
            eval('?>'.$___phpBuffer.'<?php ');
            $___antlersPhpExecutionResult = ob_get_clean();

            if (! $___node->isEchoNode) {
                $___antlersVarAfter = get_defined_vars();

                foreach ($___antlersVarAfter as $___varKey => $___varValue) {
                    if (str_starts_with($___varKey, '___')) {
                        continue;
                    }

                    $___phpRuntimeAssignments[$___varKey] = $___varValue;
                }
            }
        } catch (ParseError $e) {
            throw new SyntaxError("{$e->getMessage()} on line {$e->getLine()} of:\n\n{$___phpBuffer}");
        }

        if (! $___node->isEchoNode && ! empty($___phpRuntimeAssignments)) {
            unset($___phpRuntimeAssignments['___antlersVarBefore']);
            unset($___phpRuntimeAssignments['___antlersPhpExecutionResult']);

            $this->processAssignments($___phpRuntimeAssignments);
        }

        return $___antlersPhpExecutionResult;
    }

    /**
     * Adds standard Antlers loop variables to the array data.
     *
     * @param  array  $loop  The loop data.
     * @return array
     */
    protected function addLoopIterationVariables($loop)
    {
        $index = 0;
        $total = count($loop);
        $lastIndex = $total - 1;
        $curData = $this->data;

        if ($loop instanceof Collection) {
            $this->createLockData();
            $loop = $loop->all();
            $this->restoreLockedData();

            if (Arr::isAssoc($loop)) {
                return $loop;
            }
        }

        foreach ($loop as $key => &$value) {
            if ($value instanceof Augmentable) {
                $value = RuntimeValues::resolveWithRuntimeIsolation($value);
            }

            if ($value instanceof Values) {
                $value = $value->toArray();
            }

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            // If the value of the current iteration is *not* already an array (ie. we're
            // dealing with a super basic list like [one, two, three] then convert it
            // to one, where the value is stored in a key named "value".
            if (! is_array($value)) {
                $value = ['value' => $value, 'name' => $value];
            }

            $value['count'] = $index + 1;
            $value['index'] = $index;
            $value['total_results'] = $total;
            $value['no_results'] = false;
            $value['first'] = $index === 0;
            $value['last'] = $index === $lastIndex;

            $loopData[$index] = $value;

            $index++;
        }

        $this->data = $curData;

        $prev = null;

        foreach ($loop as $index => &$data) {
            $data['prev'] = $prev;

            if ($data['last'] == false && array_key_exists($index + 1, $loop)) {
                $data['next'] = $loop[$index + 1];
            } else {
                $data['next'] = null;
            }

            $prev = $data;
        }

        return $loop;
    }

    /**
     * Tests if the value can be iterated.
     *
     * @param  mixed  $value  The value to test.
     * @return bool
     */
    protected function isLoopable($value)
    {
        if (is_array($value) || $value instanceof Collection) {
            return true;
        }

        if (! $value instanceof Value) {
            return false;
        }

        $this->createLockData();
        $value = $value->value();
        $this->restoreLockedData();

        return is_array($value) || $value instanceof Collection;
    }

    /**
     * Runs a modifier on the provided data.
     *
     * @param  string  $modifier  The modifier name.
     * @param  array  $parameters  The parameters.
     * @param  array  $data  The data to modify.
     * @param  array  $context  The current scope data.
     * @return AntlersString|string
     *
     * @throws Throwable
     */
    protected function runModifier($modifier, $parameters, $data, $context = [])
    {
        $data = $data instanceof Value ? $data : new Value($data);

        if (! ModifierManager::guardRuntimeModifier($modifier)) {
            return $data;
        }

        if ($modifier === 'raw') {
            return $data->raw();
        }

        if ($modifier === 'noparse') {
            return $data->value();
        }

        GlobalRuntimeState::$isEvaluatingUserData = true;
        $value = $data->antlersValue($this->antlersParser, $context);
        GlobalRuntimeState::$isEvaluatingUserData = false;

        try {
            return Modify::value($value)->context($context)->$modifier($parameters)->fetch();
        } catch (ModifierException $e) {
            throw_if(config('app.debug'), ($prev = $e->getPrevious()) ? $prev : $e);
            Log::notice(sprintf('Error in [%s] modifier: %s', $e->getModifier(), $e->getMessage()));

            return $value;
        }
    }

    /**
     * Resets an array's keys if they are all numeric.
     *
     * This prevents arrays with non-ordered numeric keys from being flagged as associative.
     *
     * @param  array|mixed  $value  The array to check.
     * @return array|mixed
     */
    private function massageKeys($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        if (Arr::isAssoc($value)) {
            $resetValues = true;

            foreach ($value as $tKey => $tVal) {
                if (! is_numeric($tKey)) {
                    $resetValues = false;
                    break;
                }
            }

            if ($resetValues) {
                $value = array_values($value);
            }

            return $value;
        }

        return $value;
    }
}
