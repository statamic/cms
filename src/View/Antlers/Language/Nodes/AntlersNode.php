<?php

namespace Statamic\View\Antlers\Language\Nodes;

use Statamic\Facades\Antlers;
use Statamic\Tags\TagNotFoundException;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\Parameters\ParameterNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Throwable;

class AntlersNode extends AbstractNode
{
    /**
     * Indicates if the runtime has abandoned this node.
     *
     * @var bool
     */
    public $isNodeAbandoned = false;

    /**
     * Indicates if the parsed node is an Antlers comment.
     *
     * @var bool
     */
    public $isComment = false;

    /**
     * Indicates if the node represents an Antlers tag.
     *
     * @var bool
     */
    public $isTagNode = false;

    /**
     * @var DocumentParser|null
     */
    protected $parser = null;

    /**
     * The parsed runtime content.
     *
     * @var string
     */
    public $runtimeContent = '';

    public $activeDepth = 1;

    /**
     * Sets the internal DocumentParser instance.
     *
     * @param  DocumentParser  $parser  The parser instance.
     */
    public function withParser(DocumentParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Returns the Parser instance that generated the node.
     *
     * @return DocumentParser|null
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * The node's identifier.
     *
     * @var TagIdentifier|null
     */
    public $name = null;

    /**
     * The resolved path reference, if any.
     *
     * This will be parsed against the node's
     * primary identifier, or it's name.
     *
     * @var VariableReference|null
     */
    public $pathReference = null;

    /**
     * Indicates if the node is a closing pair.
     *
     * @var bool
     */
    public $isClosingTag = false;

    /**
     * Indicates if the node was parsed inside an interpolation parser.
     *
     * @var bool
     */
    public $isInterpolationNode = false;

    /**
     * A reference to the node's opening node, if any.
     *
     * @var AntlersNode|null
     */
    public $isOpenedBy = null;

    /**
     * A reference to the node's closing node, if any.
     *
     * @var AntlersNode|null
     */
    public $isClosedBy = null;

    /**
     * Indicates if the node is self-closing.
     *
     * @var bool
     */
    public $isSelfClosing = false;

    /**
     * A list of all the node's children, if any.
     *
     * This will be a combination of literal
     * Literal and Antlers node types.
     *
     * @var AbstractNode[]
     */
    public $children = [];

    /**
     * The runtime runtime nodes.
     *
     * These nodes are produced by the lexer, and
     * are the result of analyzing the content.
     *
     * @var AbstractNode[]
     */
    public $runtimeNodes = [];

    /**
     * The parsed runtime nodes.
     *
     * These nodes are produced by the LanguageParser,
     * and are the result of parsing the lexer nodes.
     *
     * @var AbstractNode[]
     */
    public $parsedRuntimeNodes = [];

    /**
     * Indicates if the $parsedRuntimeNodes
     * value has already been set or not.
     *
     * Prefer using this over calls to empty or count
     * on the $parsedRuntimeNodes instance member.
     *
     * @var bool
     */
    public $hasParsedRuntimeNodes = false;

    /**
     * @var ParameterNode[]
     */
    public $parameters = [];
    public $hasParameters = false;

    /**
     * The offset at which the node's content begins.
     *
     * @var Position|null
     */
    public $contentOffset = null;

    /**
     * A cache of the node's adjusted content.
     *
     * @var string|null
     */
    private $cachedContent = null;

    /**
     * A cache of the node's inner content value.
     *
     * @var string|null
     */
    private $cachedInnerContent = null;

    /**
     * Indicates if this node has an associated recursive node.
     *
     * @var bool
     */
    public $hasRecursiveNode = false;

    /**
     * A reference to the node's RecursiveNode.
     *
     * This reference will be set if the node's
     * hasRecursiveNode member is set to true.
     *
     * @var RecursiveNode|null
     */
    public $recursiveReference = null;

    /**
     * The node's raw start.
     *
     * Possible values are:
     *   - {{#  Comment node.
     *   - {{$  Antlers PHP node.
     *   -  {{  Antlers node.
     *   -   {  Interpolation region.
     *
     * @var string
     */
    public $rawStart = '';

    /**
     * The node's raw end.
     *
     * Possible values are:
     *   - #}}  Comment node.
     *   - $}}  Antlers PHP node.
     *   -  }}  Antlers node.
     *   -   }  Interpolation region.
     *
     * @var string
     */
    public $rawEnd = '';

    /**
     * A reference to the AntlersNode that was rewritten.
     *
     * If this node was created due to a node rewrite
     * (such as unless or endif), this will be a
     * reference to the originally parsed node.
     *
     * @var AntlersNode|null
     */
    public $originalNode = null;

    /**
     * A list of all nested interpolation regions.
     *
     * @var array
     */
    public $interpolationRegions = [];

    /**
     * A mapping of the interpolation regions and their parsed runtime nodes.
     *
     * @var array
     */
    public $processedInterpolationRegions = [];

    /**
     * Indicates if node's interpolation regions have been parsed.
     *
     * Prefer this variable over calling count or empty
     * on the $processedInterpolationRegions member.
     *
     * @var bool
     */
    public $hasProcessedInterpolationRegions = false;

    /**
     * An internal counter of how many other nodes reference this one.
     *
     * @var int
     */
    public $ref = 0;

    /**
     * Indicates if the node has param-style modifiers.
     *
     * @var bool|null
     */
    private $hasModifierParametersCache = null;

    /**
     * Returns a new AntlersNode with basic details copied.
     *
     * @return AntlersNode
     */
    public function copyBasicDetails()
    {
        $copy = new AntlersNode();

        return $this->copyBasicDetailsTo($copy);
    }

    /**
     * Copies details to the target instance node.
     *
     * @param  AntlersNode  $instance  The node instance.
     * @return mixed
     */
    public function copyBasicDetailsTo($instance)
    {
        $instance->refId = $this->refId;
        $instance->isComment = $this->isComment;
        $instance->isTagNode = $this->isTagNode;
        $instance->children = $this->children;
        $instance->parameters = $this->parameters;
        $instance->isClosingTag = $this->isClosingTag;
        $instance->rawStart = $this->rawStart;
        $instance->rawEnd = $this->rawEnd;
        $instance->startPosition = $this->startPosition;
        $instance->endPosition = $this->endPosition;

        return $instance;
    }

    public function innerContent()
    {
        if ($this->cachedInnerContent == null) {
            $this->cachedInnerContent = str_replace('"', '\\"', $this->content);
        }

        return $this->cachedInnerContent;
    }

    public function hasModifierParameters()
    {
        if ($this->hasModifierParametersCache == null) {
            $this->hasModifierParametersCache = false;

            if ($this->hasParameters) {
                foreach ($this->parameters as $parameter) {
                    if (ModifierManager::isModifier($parameter) || $parameter->name == 'handle_prefix') {
                        $this->hasModifierParametersCache = true;
                        break;
                    }
                }
            }
        }

        return $this->hasModifierParametersCache;
    }

    /**
     * Returns the adjusted parameter values for this node.
     *
     * @param  NodeProcessor  $processor  The node processor instance.
     * @param  array  $data  The data to resolve parameter values with.
     * @return array
     *
     * @throws TagNotFoundException
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws Throwable
     */
    public function getParameterValues(NodeProcessor $processor, $data = [])
    {
        $values = [];

        foreach ($this->parameters as $param) {
            $value = $this->getSingleParameterValue($param, $processor, $data);

            if ($this->isVoidValue($value)) {
                continue;
            }

            if (is_string($value)) {
                $value = DocumentParser::applyEscapeSequences($value);
            }

            $values[$param->name] = $value;
        }

        return $values;
    }

    protected function isVoidValue($value)
    {
        return is_string($value) && $value == 'void::'.GlobalRuntimeState::$environmentId;
    }

    /**
     * Returns the value of a single parameter by name.
     *
     * @param $parameterName
     * @param  NodeProcessor  $processor
     * @param $data
     * @param $default
     * @return array|mixed|\Statamic\Contracts\Query\Builder|string|string[]|null
     */
    public function getSingleParameterValueByName($parameterName, NodeProcessor $processor, $data, $default = null)
    {
        $result = $default;

        if (empty($this->parameters)) {
            return $result;
        }

        foreach ($this->parameters as $param) {
            if ($param->name == $parameterName) {
                $value = $this->getSingleParameterValue($param, $processor, $data);

                if ($this->isVoidValue($value)) {
                    break;
                }

                $result = $value;

                break;
            }
        }

        return $result;
    }

    public function getSingleParameterValue(ParameterNode $param, NodeProcessor $processor, $data = [])
    {
        $value = $param->value;

        if ($param->isVariableReference) {
            $pathToParse = $this->reduceParameterInterpolations($param, $processor->cloneProcessor()->setIsProvidingParameterContent(true), $param->value, $data);

            // Only use the full Antlers parser here if the string contains characters like |, (, {, }, etc.
            if (StringUtilities::containsSymbolicCharacters($pathToParse)) {
                $value = Antlers::parser()->getVariable($pathToParse, $data, null);
            } else {
                $pathParser = new PathParser();
                $path = $pathParser->parse($pathToParse);
                $doIntercept = count($path->pathParts) > 1;

                $retriever = new PathDataManager();
                $retriever->setIsPaired(false)->setReduceFinal(false)
                    ->cascade($processor->getCascade())
                    ->setShouldDoValueIntercept($doIntercept);
                $value = $retriever->getData($path, $data);
            }
        } else {
            $value = $this->reduceParameterInterpolations($param, $processor, $value, $data);
        }

        if (is_string($value)) {
            $value = DocumentParser::applyEscapeSequences($value);
        }

        return $value;
    }

    /**
     * Processes any nested interpolations that may be within the parameter content.
     *
     * @param  ParameterNode  $param  The parameter to analyze.
     * @param  NodeProcessor  $processor  The node processor.
     * @param  string  $mutateVar  The value to apply interpolations to.
     * @param  array  $data  The context data.
     * @return array|string|string[]
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     * @throws TagNotFoundException
     * @throws Throwable
     */
    public function reduceParameterInterpolations(ParameterNode $param, NodeProcessor $processor, $mutateVar, $data)
    {
        if ($param->parent != null && ! empty($param->interpolations)) {
            foreach ($param->interpolations as $interpolationVar) {
                if (array_key_exists($interpolationVar, $param->parent->processedInterpolationRegions)) {
                    $interpolationResult = $processor->cloneProcessor()
                        ->setData($data)
                        ->cascade($processor->getCascade())
                        ->setIsInterpolationProcessor(true)
                        ->setIsProvidingParameterContent(true)
                        ->reduce($param->parent->processedInterpolationRegions[$interpolationVar]);

                    if ((is_object($interpolationResult) || is_array($interpolationResult)) && count($param->interpolations) == 1) {
                        return $interpolationResult;
                    }

                    $mutateVar = str_replace($interpolationVar, $interpolationResult, $mutateVar);
                }
            }
        }

        return $mutateVar;
    }

    public function getModifierParameterValuesForParameter(ParameterNode $param, $data = [])
    {
        $values = [];

        $value = $param->value;

        if ($param->isVariableReference) {
            $pathParser = new PathParser();
            $retriever = new PathDataManager();
            $retriever->setIsPaired($this->isClosedBy != null);
            $value = $retriever->getData($pathParser->parse($value), $data);

            if (is_string($value)) {
                $value = DocumentParser::applyEscapeSequences($value);
            }

            $values[] = $value;
        } else {
            $pipeEscape = DocumentParser::getPipeEscape();

            $values = array_map(function ($item) use ($pipeEscape) {
                return DocumentParser::applyEscapeSequences(str_replace($pipeEscape, DocumentParser::Punctuation_Pipe, $item));
            }, explode('|', $value));
        }

        return array_values($values);
    }

    public function getModifierParameterValues($data = [])
    {
        $values = [];

        /** @var ParameterNode $param */
        foreach ($this->parameters as $param) {
            $values += $this->getModifierParameterValuesForParameter($param, $data);
        }

        return array_values($values);
    }

    public function resetContentCache()
    {
        $this->cachedContent = null;
        $this->cachedInnerContent = null;
    }

    public function getContent()
    {
        if ($this->cachedContent == null) {
            if ($this->isComment) {
                $this->cachedContent = $this->content;
                $this->contentOffset = $this->startPosition;
            } else {
                if ($this->isTagNode) {
                    $contentToAnalyze = $this->content;

                    if (! empty($this->parameters)) {
                        $leadContent = $contentToAnalyze;
                        $contentWithoutSpace = ltrim($leadContent);
                        $leadingWsCount = mb_strlen($leadContent) - mb_strlen($contentWithoutSpace);
                        $leadNameLen = mb_strlen($this->name->compound);
                        $leadOffset = $leadNameLen + $leadingWsCount;

                        $contentToAnalyze = StringUtilities::substr($contentToAnalyze, 0, $this->parameters[0]->startPosition->index + $leadOffset);
                    }

                    $contentWithoutSpace = ltrim($contentToAnalyze);
                    $leadingWsCount = mb_strlen($contentToAnalyze) - mb_strlen($contentWithoutSpace);
                    $leadNameLen = mb_strlen($this->name->compound);
                    $leadOffset = $leadNameLen + $leadingWsCount + 2;

                    $this->contentOffset = $this->parser->positionFromOffset($this->startPosition->offset + $leadOffset, 0);

                    if ($this->name->name == 'if' || $this->name->name == 'elseif' ||
                        $this->name->name == 'unless' || $this->name->name == 'elseunless') {
                        $contentToAnalyze = ' '.ltrim($contentToAnalyze);
                        $this->cachedContent = StringUtilities::substr($contentToAnalyze, $leadNameLen + 1);
                    } else {
                        $this->cachedContent = $contentToAnalyze;
                    }
                } else {
                    if (! empty($this->parameters)) {
                        $this->contentOffset = $this->startPosition;
                        $this->cachedContent = StringUtilities::substr($this->content, 0, $this->parameters[0]->startPosition->index);
                    } else {
                        $this->contentOffset = $this->startPosition;
                        $this->cachedContent = $this->content;
                    }
                }
            }
        }

        return $this->cachedContent;
    }

    public function isPaired()
    {
        if ($this->isClosedBy == null) {
            return false;
        }

        if ($this->isSelfClosing) {
            return false;
        }

        return true;
    }

    public function getNodeDocumentText()
    {
        return $this->parser->getText($this->startPosition->index, $this->endPosition->index + 1);
    }

    public function documentText()
    {
        if ($this->isSelfClosing || $this->isClosedBy == null) {
            return $this->rawContent();
        }

        return $this->parser->getText(
            $this->startPosition->index,
            $this->isClosedBy->endPosition->index + 1
        );
    }

    public function relativePositionFromOffset($offset, $index)
    {
        return $this->parser->positionFromOffset($this->contentOffset->offset + $offset, $index);
    }

    public function lexerRelativeOffset($offset)
    {
        if ($this->parser == null) {
            $position = new Position();
            $position->index = $offset;
            $position->offset = $offset;

            return $position;
        }

        $relativeIndex = $offset + strlen($this->rawStart);

        if ($this->startPosition != null) {
            $relativeIndex += $this->startPosition->index;
        }

        return $this->parser->positionFromOffset($relativeIndex, $relativeIndex, true);
    }

    public function relativeOffset($offset, $index = null)
    {
        if ($index == null) {
            $index = $offset;
        }

        if ($this->parser == null) {
            $position = new Position();
            $position->index = $offset;
            $position->offset = $offset;

            return $position;
        }

        return $this->parser->positionFromOffset($this->startPosition->offset + $offset + mb_strlen($this->rawStart), $index);
    }

    public function rawContent()
    {
        return $this->rawStart.$this->content.$this->rawEnd;
    }
}
