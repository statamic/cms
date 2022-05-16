<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;

class RuntimeConfiguration
{
    /**
     * A list of all Antlers preparser callbacks.
     *
     * @var callable[]
     */
    protected $preparsers = [];

    /**
     * A list of all document node visitors.
     *
     * @var NodeVisitorContract[]
     */
    protected $visitors = [];

    /**
     * Controls whether unpaired loops throw an exception or not.
     *
     * @var bool
     */
    public $fatalErrorOnUnpairedLoop = false;

    /**
     * Controls whether attempting to render an object as a string throws an exception or not.
     *
     * @var bool
     */
    public $fatalErrorOnStringObject = false;

    /**
     * Controls whether runtime tracing is enabled.
     *
     * @var bool
     */
    public $isTracingEnabled = false;

    /**
     * An optional runtime TraceManager instance.
     *
     * @var TraceManager|null
     */
    public $traceManager = null;

    /**
     * Controls whether runtime access violations fail silently or not.
     *
     * @var bool
     */
    public $throwErrorOnAccessViolation = false;

    /**
     * A list of all invalid variable patterns.
     *
     * @var string[]
     */
    public $guardedVariablePatterns = [];

    /**
     * A list of all invalid content variable patterns.
     *
     * @var string[]
     */
    public $guardedContentVariablePatterns = [];

    /**
     * A list of all invalid tag patterns.
     *
     * @var string[]
     */
    public $guardedTagPatterns = [];

    /**
     * A list of all invalid content tag patterns.
     *
     * @var string[]
     */
    public $guardedContentTagPatterns = [];

    /**
     * A list of all invalid modifier patterns.
     *
     * @var string[]
     */
    public $guardedModifiers = [];

    /**
     * A list of all invalid content modifier patterns.
     *
     * @var string[]
     */
    public $guardedContentModifiers = [];

    /**
     * Indicates if PHP Code should be evaluated in user content.
     *
     * When disabled, *antlers.php templates will be allowed,
     * but PHP code when evaluating fields with antlers:true
     * will revert to the behavior of PHP being disabled.
     *
     * @var bool
     */
    public $allowPhpInUserContent = false;

    /**
     * Registers a new Antlers preparser callback.
     *
     * @param  callable  $callable  The preparser callback.
     */
    public function preparse(callable $callable)
    {
        $this->preparsers[] = $callable;
    }

    /**
     * Gets all registered Antlers preparsers.
     *
     * @return callable[]
     */
    public function getPreparsers()
    {
        return $this->preparsers;
    }

    /**
     * Registers a new NodeVisitorContract instance.
     *
     * @param  NodeVisitorContract  $visitor  The visitor.
     */
    public function addVisitor(NodeVisitorContract $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Returns all registered node visitors.
     *
     * @return NodeVisitorContract[]
     */
    public function getVisitors()
    {
        return $this->visitors;
    }
}
