<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

/**
 * Class GlobalRuntimeState.
 *
 * The GlobalRuntimeState is responsible for communicating data state
 * across key parts of the Antlers Runtime/Virtual Machine. Non-core
 * developers are highly discouraged from interacting with any of
 * these properties directly, as this can cause runtime issues.
 */
class GlobalRuntimeState
{
    const INDICATOR_NAMED_SLOTS_AVAILABLE = '_ns_a';

    /**
     * Indicates if the executing environment is in debug mode.
     *
     * @var bool
     */
    public static $isDebugMode = false;

    /**
     * A unique identifier for the active Antlers environment.
     *
     * @var string
     */
    public static $environmentId = '';

    /**
     * A reference of all observed interpolation regions.
     *
     * @var string[]
     */
    public static $interpolatedVariables = [];

    /**
     * A list of all Antlers template files processed.
     *
     * This list will be updated dynamically, and
     * will reflect the template execution path.
     *
     * @var array
     */
    public static $templateFileStack = [];

    /**
     * The active section count.
     *
     * @var int
     */
    public static $yieldCount = 0;

    /**
     * A reference to all active yield stacks, and their corresponding counts.
     *
     * @var array
     */
    public static $yieldStacks = [];

    /** @var AntlersNode|null */
    public static $lastNode = null;

    /**
     * A trace of the Antlers template file being executed.
     *
     * @var string|null
     */
    public static $currentExecutionFile = null;

    /**
     * Indicates if the Antlers runtime is evaluating user-supplied data.
     *
     * @var bool
     */
    public static $isEvaluatingUserData = false;

    public static $isEvaluatingData = false;

    /**
     * A counter of the active tracer count.
     *
     * @var int
     */
    public static $activeTracerCount = 0;

    /**
     * Controls whether the Antlers runtime should trace tag assignments.
     *
     * @var bool
     */
    public static $traceTagAssignments = false;

    /**
     * A list of all traced runtime assignments.
     *
     * @var array
     */
    public static $tracedRuntimeAssignments = [];

    /**
     * A list of IDs of all abandoned nodes.
     *
     * @var array
     */
    public static $abandonedNodes = [];

    /**
     * Updates the global state with the provided Antlers runtime tag assignments.
     *
     * @param  array  $assignments  The assignments.
     */
    public static function mergeTagRuntimeAssignments($assignments)
    {
        self::$tracedRuntimeAssignments = array_merge(self::$tracedRuntimeAssignments, $assignments);
    }

    /**
     * Maintains a record of all tag nodes that have been entered.
     *
     * Utilized to offset start lines when re-parsing tag callbacks.
     *
     * @var AntlersNode[]
     */
    public static $globalTagEnterStack = [];

    /**
     * Controls runtime access violation error behavior.
     *
     * @var bool
     */
    public static $throwErrorOnAccessViolation = false;

    /**
     * A list of all globally invalid variable paths.
     *
     * @var string[]
     */
    public static $bannedVarPaths = [];

    /**
     * A list of globally invalid content variable paths.
     *
     * @var string[]
     */
    public static $bannedContentVarPaths = [];

    /**
     * A list of all invalid tag paths.
     *
     * @var string[]
     */
    public static $bannedTagPaths = [];

    /**
     * A list of all invalid content tag paths.
     *
     * @var string[]
     */
    public static $bannedContentTagPaths = [];

    /**
     * A list of all invalid modifier paths.
     *
     * @var string[]
     */
    public static $bannedModifierPaths = [];

    /**
     * A list of all invalid content modifier paths.
     *
     * @var string[]
     */
    public static $bannedContentModifierPaths = [];

    /**
     * Controls if PHP is evaluated in user content.
     *
     * @var bool
     */
    public static $allowPhpInContent = false;

    /**
     * Maintains a list of all field prefixes that have been encountered.
     *
     * @var array
     */
    public static $prefixState = [];

    public static $containsLayout = false;
    public static $renderingLayout = false;
    public static $shareVariablesTemplateTrigger = '';
    public static $layoutVariables = [];

    public static $requiresRuntimeIsolation = false;

    public static $evaulatingTagContents = false;

    public static $userContentEvalState = null;

    /**
     * A list of callbacks that will be invoked when ___internal_debug:peek is called.
     *
     * The callback will receive the current NodeProcessor
     * instance before it enters into tag callback state.
     *
     * @var array
     */
    public static $peekCallbacks = [];

    public static $isCacheEnabled = false;

    public static function resetGlobalState()
    {
        self::$shareVariablesTemplateTrigger = '';
        self::$layoutVariables = [];
        self::$containsLayout = false;
        self::$tracedRuntimeAssignments = [];
        self::$traceTagAssignments = false;
        self::$environmentId = StringUtilities::uuidv4();
        self::$yieldCount = 0;
        self::$yieldStacks = [];
        self::$abandonedNodes = [];

        StackReplacementManager::clearStackState();
        LiteralReplacementManager::resetLiteralState();
        RecursiveNodeManager::resetRecursiveNodeState();
        RuntimeParser::clearRenderNodeCache();
    }

    public static function createIndicatorVariable($indicator)
    {
        return '__'.self::$environmentId.$indicator;
    }
}
