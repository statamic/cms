<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Illuminate\Http\Exceptions\HttpResponseException;
use ReflectionProperty;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Spatie\LaravelIgnition\Exceptions\ViewExceptionWithSolution;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Fields\Value;
use Statamic\Modifiers\ModifierNotFoundException;
use Statamic\Search\Comb\Exceptions\Exception;
use Statamic\Support\Str;
use Statamic\Tags\TagNotFoundException;
use Statamic\View\Antlers\AntlersString;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\LineRetriever;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Position;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageKeywords;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationManager;
use Statamic\View\Instrumentation\InstrumentationState;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class RuntimeParser implements Parser
{
    const CAPTURED_CONTENT = '__captured';

    /**
     * The current DocumentParser instance.
     *
     * @var DocumentParser
     */
    private $documentParser = null;

    /**
     * The current NodeProcessor instance.
     *
     * @var NodeProcessor
     */
    private $nodeProcessor = null;

    /**
     * The current view template file path, if available.
     *
     * @var string
     */
    private $view = '';

    /**
     * @var Cascade|null
     */
    private $cascade = null;

    /**
     * Indicates if PHP code execution is allowed.
     *
     * @var bool
     */
    private $allowPhp = false;

    /** @var RuntimeConfiguration|null */
    protected $runtimeConfiguration;

    /** @var \Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract[] */
    protected $syncedVisitors = [];

    /** @var bool */
    protected $htmlInstrumentationActive = false;

    /** @var InstrumentationManager|null */
    private $instrumentation = null;

    /** @var array{0: callable[], 1: callable[], 2: HtmlInstrumentation[], 3: callable[]}|null */
    private $instrumenterCache = null;

    /**
     * @var array<string, array{0: array, 1: array{0: int, 1: int, 2: bool}}>
     */
    private static $viewSourceSeeds = [];

    /**
     * @var array<string, string>
     */
    private static $newLineStyles = [];

    /** @var int */
    protected static $activeRenders = 0;

    /**
     * A list of pre-parsers.
     *
     * @var callable[]
     */
    protected $preParsers = [];

    /**
     * A cache of previously observed render nodes.
     *
     * @var array
     */
    protected static $standardRenderNodeCache = [];

    /**
     * A reference to any temporary file created during error reporting.
     *
     * @var string|null
     */
    private $tempFileCreated = null;

    /**
     * The Antlers Lexer instance.
     *
     * @var AntlersLexer
     */
    private $antlersLexer = null;

    /**
     * The Antlers Parser instance.
     *
     * @var LanguageParser
     */
    private $antlersParser = null;

    /**
     * Keeps track of how nested the RuntimeParser instance is.
     *
     * @var int
     */
    private $parseStack = 0;

    /**
     * Determines if runtime processors should be isolated.
     *
     * @var bool
     */
    private $isolateRuntimes = false;

    protected $componentCompiler;

    public function __construct(DocumentParser $documentParser, NodeProcessor $nodeProcessor, AntlersLexer $lexer, LanguageParser $antlersParser, ?InstrumentationManager $instrumentation = null)
    {
        $this->documentParser = $documentParser;
        $this->nodeProcessor = $nodeProcessor;
        $this->antlersLexer = $lexer;
        $this->antlersParser = $antlersParser;
        $this->instrumentation = $instrumentation;

        $this->componentCompiler = new ComponentCompiler();
    }

    /**
     * Sets the RuntimeConfiguration instance.
     *
     * @param  RuntimeConfiguration  $configuration  The RuntimeConfiguration instance.
     * @return $this
     */
    public function setRuntimeConfiguration(RuntimeConfiguration $configuration)
    {
        $this->runtimeConfiguration = $configuration;
        GlobalRuntimeState::$allowPhpInContent = $configuration->allowPhpInUserContent;
        GlobalRuntimeState::$allowMethodsInContent = $configuration->allowMethodsInUserContent;
        GlobalRuntimeState::$throwErrorOnAccessViolation = $configuration->throwErrorOnAccessViolation;
        GlobalRuntimeState::$bannedVarPaths = $configuration->guardedVariablePatterns;
        GlobalRuntimeState::$bannedContentVarPaths = $configuration->guardedContentVariablePatterns;
        GlobalRuntimeState::$bannedTagPaths = $configuration->guardedTagPatterns;
        GlobalRuntimeState::$bannedContentTagPaths = $configuration->guardedContentTagPatterns;
        GlobalRuntimeState::$allowedContentTagPaths = $configuration->allowedContentTagPatterns;
        GlobalRuntimeState::$bannedModifierPaths = $configuration->guardedModifiers;
        GlobalRuntimeState::$bannedContentModifierPaths = $configuration->guardedContentModifiers;
        GlobalRuntimeState::$allowedContentModifierPaths = $configuration->allowedContentModifiers;

        $this->documentParser->annotateHtmlContext($configuration->annotateHtmlContext);
        $this->nodeProcessor->setRuntimeConfiguration($configuration);

        return $this;
    }

    /**
     * Resets the internal runtime configuration instance.
     *
     * @return $this
     */
    public function resetRuntimeConfiguration()
    {
        $this->documentParser->annotateHtmlContext(false);

        if ($this->syncedVisitors !== []) {
            static::clearRenderNodeCache();
        }

        $this->documentParser->setRuntimeVisitors([]);
        $this->syncedVisitors = [];
        $this->runtimeConfiguration = null;
        $this->nodeProcessor->resetRuntimeConfiguration();

        return $this;
    }

    /**
     * Escapes the PHP starting tags from the input text.
     *
     * @param  string  $text  The text to sanitize.
     * @return string|string[]
     */
    protected function sanitizePhp($text)
    {
        if (GlobalRuntimeState::$isEvaluatingUserData && ! GlobalRuntimeState::$allowPhpInContent) {
            return StringUtilities::sanitizePhp($text);
        }

        if ($this->allowPhp) {
            return $text;
        }

        return StringUtilities::sanitizePhp($text);
    }

    /**
     * Executes all pre-parser callbacks on the input text.
     *
     * @param  string  $text  The text to pre-parse.
     * @param  callable[]|null  $preParsers
     * @return string
     */
    protected function runPreParserCallbacks($text, $preParsers = null, $instrument = true)
    {
        $value = $text ?? '';
        $preParsers ??= $this->preParserState()[3];

        if ($preParsers === []) {
            return $value;
        }

        $instrumenters = [];

        foreach ($preParsers as $preParser) {
            if ($preParser instanceof HtmlInstrumentation) {
                if ($instrument) {
                    $instrumenters[] = $preParser;
                }

                continue;
            }

            if ($instrumenters !== []) {
                $value = HtmlInstrumentation::instrumentTogether($value ?? '', $instrumenters);
                $instrumenters = [];
            }

            $value = call_user_func($preParser, $value);
        }

        if ($instrumenters === []) {
            return $value;
        }

        return HtmlInstrumentation::instrumentTogether($value ?? '', $instrumenters);
    }

    /**
     * @internal
     *
     * @return bool
     */
    public function annotatesHtmlContext()
    {
        return $this->documentParser->annotatesHtmlContext();
    }

    /**
     * @param  string  $text
     * @return void
     */
    private function adoptHtmlContexts($text)
    {
        $contexts = HtmlInstrumentation::annotationsFor($text);

        if ($contexts !== null) {
            $this->documentParser->adoptHtmlContexts($contexts);
        }
    }

    /**
     * @internal
     *
     * @return bool
     */
    public static function isRendering()
    {
        return self::$activeRenders > 0;
    }

    /**
     * @internal
     *
     * @return bool
     */
    public function instrumenting()
    {
        return $this->htmlInstrumentationActive;
    }

    /**
     * @internal
     *
     * @return bool
     */
    public function hasInstrumenters()
    {
        return $this->htmlInstrumenters() !== [];
    }

    /** @return HtmlInstrumentation[] */
    private function htmlInstrumenters()
    {
        return $this->preParserState()[2];
    }

    /**
     * @return array{0: callable[], 1: callable[], 2: HtmlInstrumentation[], 3: callable[]}
     */
    private function preParserState()
    {
        $configured = $this->runtimeConfiguration?->getPreparsers() ?? [];
        $cache = $this->instrumenterCache;

        if ($cache !== null && $cache[0] === $configured && $cache[1] === $this->preParsers) {
            return $cache;
        }

        $preParsers = $this->mergeConfigurationCallbacks($this->preParsers, 'getPreparsers');
        $instrumenters = array_values(array_filter(
            $preParsers,
            fn ($preparser) => $preparser instanceof HtmlInstrumentation
        ));

        return $this->instrumenterCache = [$configured, $this->preParsers, $instrumenters, $preParsers];
    }

    /**
     * @param  string  $text
     * @param  string  $key
     * @return string
     */
    private function newLineStyle($text, $key)
    {
        $key = ($this->htmlInstrumentationActive ? 'i' : 'p').$key;

        if (isset(self::$newLineStyles[$key])) {
            return self::$newLineStyles[$key];
        }

        if (count(self::$newLineStyles) >= 512) {
            self::$newLineStyles = array_slice(self::$newLineStyles, 256, null, true);
        }

        return self::$newLineStyles[$key] = StringUtilities::detectNewLineStyle(
            $this->htmlInstrumentationActive ? HtmlInstrumentation::removeMarkers($text, $this->htmlInstrumenters()) : $text
        );
    }

    /**
     * @param  callable[]  $local
     * @param  string  $accessor
     * @return callable[]
     */
    private function mergeConfigurationCallbacks(array $local, $accessor)
    {
        if ($this->runtimeConfiguration === null) {
            return $local;
        }

        $callbacks = $this->runtimeConfiguration->{$accessor}();

        foreach ($local as $callback) {
            if (! in_array($callback, $callbacks, true)) {
                $callbacks[] = $callback;
            }
        }

        return $callbacks;
    }

    protected function syncRuntimeVisitors()
    {
        if ($this->runtimeConfiguration === null) {
            return;
        }

        $visitors = $this->runtimeConfiguration->getVisitors();

        if ($visitors === $this->syncedVisitors) {
            return;
        }

        $this->documentParser->setRuntimeVisitors($visitors);
        $this->syncedVisitors = $visitors;
    }

    /**
     * Analyzes a file's contents to determine a front matter line offset.
     *
     * The line offset is utilized to correctly determine
     * the line nodes starting line for error reporting.
     *
     * @param  string  $path  The file path.
     * @return int
     */
    private function getFrontMatterLineOffset($path)
    {
        if (file_exists($path) == false) {
            return 1;
        }

        $contents = file_get_contents($path);

        if (preg_match('/^---[\r\n?|\n]/', $contents)) {
            $linedContent = StringUtilities::breakByNewLine(StringUtilities::normalizeLineEndings($contents));
            $lineCount = count($linedContent);

            if ($lineCount <= 1) {
                unset($lineCount);
                unset($contents);

                return 1;
            }

            for ($i = 1; $i < $lineCount; $i++) {
                if (Str::startsWith($linedContent[$i], '---')) {
                    unset($lineCount);
                    unset($contents);

                    return $i + 2;
                }
            }
        }

        unset($contents);

        return 1;
    }

    /**
     * @param  string  $path
     * @param  string  $text
     * @return array{0: int, 1: int, 2: bool}
     */
    private function getViewSourceSeed($path, $text)
    {
        if (! is_string($text) || ! is_file($path)) {
            return [1, 0, true];
        }

        $signature = [filemtime($path), filesize($path), hash('xxh3', $text)];
        $cached = self::$viewSourceSeeds[$path] ?? null;

        if ($cached !== null && $cached[0] === $signature) {
            return $cached[1];
        }

        $contents = file_get_contents($path);

        if (! str_ends_with($contents, $text)) {
            $seed = [1, 0, false];
        } else {
            $frontMatter = substr($contents, 0, strlen($contents) - strlen($text));
            $seed = [substr_count($frontMatter, "\n") + 1, mb_strlen($frontMatter), true];
        }

        if (count(self::$viewSourceSeeds) >= 128) {
            self::$viewSourceSeeds = array_slice(self::$viewSourceSeeds, 64, null, true);
        }

        self::$viewSourceSeeds[$path] = [$signature, $seed];

        return $seed;
    }

    /**
     * Adds a string preparser to the list of executable preparsers.
     *
     * @param  callable  $preparser  The preparser to add.
     */
    public function preparse(callable $preparser)
    {
        $this->preParsers[] = $preparser;
    }

    protected function canPossiblyParseAntlers($text)
    {
        if ($text == null || mb_strlen(trim($text)) == 0) {
            return false;
        }

        // Check if there may be PHP tags in the document. If
        // PHP is enabled, we will let the NodeProcessor
        // handle it so that we do not have to copy
        // any of its PHP-specific logic here.
        if ($this->allowPhp && Str::contains($text, '<?')) {
            return true;
        }

        if (Str::contains($text, [DocumentParser::LeftBrace, '@props', '@aware', '@cascade'])) {
            return true;
        }

        return false;
    }

    /**
     * Adds a list of nodes to the internal node cache.
     *
     * @param  string  $text  The source content.
     * @param  AbstractNode[]  $nodes  The parsed nodes.
     * @param  bool  $annotatedHtmlContext
     */
    public static function pushNodeCache($text, $nodes, $annotatedHtmlContext = false)
    {
        $cacheSlug = md5($text);

        if ($annotatedHtmlContext) {
            $cacheSlug .= ':html';
        }

        self::$standardRenderNodeCache[$cacheSlug] = $nodes;
    }

    /**
     * Returns all node cache entries.
     *
     * @return array
     */
    public static function getNodeCache()
    {
        return self::$standardRenderNodeCache;
    }

    /**
     * Restores a previous cache entry.
     *
     * @param  string  $entry  The cache slug.
     * @param  AbstractNode[]  $nodes  The parsed nodes.
     */
    public static function setCacheEntry($entry, $nodes)
    {
        self::$standardRenderNodeCache[$entry] = $nodes;
    }

    protected function isIgnitionInstalled()
    {
        return class_exists(ViewException::class) || class_exists('Spatie\LaravelIgnition\Exceptions\ViewException');
    }

    protected function shouldCacheRenderNodes($text)
    {
        return ! str_contains($text, '/noparse');
    }

    /**
     * Parses and renders the input text, with the provided runtime data.
     *
     * @param  string  $text  The text to parse and render.
     * @param  array  $data  The runtime data.
     * @return AntlersString
     *
     * @throws AntlersException
     * @throws ViewException
     * @throws TagNotFoundException
     * @throws Throwable
     */
    protected function renderText($text, $data = [], $isView = false, $instrument = true)
    {
        if (self::$activeRenders === 0) {
            $this->instrumentation?->sync();
        }

        if (GlobalRuntimeState::$isEvaluatingUserData
            && ! InstrumentationState::suppressed()
            && $this->hasInstrumenters()) {
            return InstrumentationState::withoutInstrumentation(
                fn () => $this->renderText($text, $data, $isView, $instrument)
            );
        }

        if (self::$activeRenders === 0
            && ! InstrumentationState::suppressed()
            && $this->hasInstrumenters()
            && ! InstrumentationState::bufferedOutputIsCommentSafe()) {
            return InstrumentationState::withoutInstrumentation(
                fn () => $this->renderText($text, $data, $isView, $instrument)
            );
        }

        $this->parseStack += 1;
        $parseStackReleased = false;
        $isOutermostRender = ++self::$activeRenders === 1;

        try {
            $preParserState = $this->preParserState();
            $this->htmlInstrumentationActive = $preParserState[2] !== [];

            if ($this->htmlInstrumentationActive) {
                $tagBody = $isView || ! is_string($text) ? null : InstrumentationState::activeTagBody()?->align($text);

                if ($tagBody !== null) {
                    $text = $tagBody;
                    $instrument = false;
                }

                $sourceSeed = [1, 0, strlen($this->view) > 0];

                if ($sourceSeed[2] && $instrument) {
                    $sourceSeed = $this->getViewSourceSeed($this->view, $text);
                }

                $text = $this->componentCompiler->compile($text, true);
                $text = InstrumentationState::withSourceMap(
                    $this->componentCompiler->sourceMap(),
                    fn () => InstrumentationState::withSourceSeed(
                        $sourceSeed,
                        fn () => $this->runPreParserCallbacks($text, $preParserState[3], $instrument)
                    )
                );
            } else {
                $text = $this->runPreParserCallbacks($this->componentCompiler->compile($text), $preParserState[3]);
            }

            if (! $this->canPossiblyParseAntlers($text)) {
                $text = $this->sanitizePhp($text);

                $this->parseStack -= 1;
                $parseStackReleased = true;

                if ($text == null) {
                    return new AntlersString('', $this);
                }

                return new AntlersString($text, $this);
            }

            $bufferContent = '';
            $parseText = $this->sanitizePhp($text);
            $cacheSlug = md5($parseText);
            $newLineStyle = $this->newLineStyle($text, $cacheSlug);
            $annotateHtmlContext = $this->documentParser->annotatesHtmlContext();

            if ($this->runtimeConfiguration !== null) {
                if ($this->runtimeConfiguration->annotateHtmlContext !== $annotateHtmlContext) {
                    $annotateHtmlContext = $this->runtimeConfiguration->annotateHtmlContext;
                    $this->documentParser->annotateHtmlContext($annotateHtmlContext);
                }

                $this->syncRuntimeVisitors();
            }

            if ($annotateHtmlContext) {
                $cacheSlug .= ':html';
            }

            if (! array_key_exists($cacheSlug, self::$standardRenderNodeCache) || ! $this->shouldCacheRenderNodes($text)) {
                $previousParser = null;

                foreach ($this->documentParser->getNodes() as $node) {
                    if ($node instanceof AntlersNode) {
                        $node->withParser($previousParser ??= $this->documentParser->snapshot());
                    }
                }

                $this->documentParser->setIsVirtual($this->view == '');

                if (strlen($this->view) > 0) {
                    $seedStartLine = $this->getFrontMatterLineOffset($this->view);
                    $this->documentParser->setStartLineSeed($seedStartLine);
                } else {
                    $this->documentParser->setStartLineSeed(1);
                }

                if (! empty(GlobalRuntimeState::$globalTagEnterStack)) {
                    /** @var AntlersNode $lastTagNode */
                    $lastTagNode = GlobalRuntimeState::$globalTagEnterStack[count(GlobalRuntimeState::$globalTagEnterStack) - 1];

                    if (! in_array($lastTagNode->name->name, ['partial', 'include'])) {
                        $this->documentParser->setStartLineSeed($lastTagNode->endPosition->line);
                    }
                }
                $this->documentParser->withComponentPrefixes(
                    $this->htmlInstrumentationActive ? $preParserState[2][0]->getComponentPrefixes() : null
                );
                self::$standardRenderNodeCache[$cacheSlug] = $this->documentParser->parse($parseText);

                if ($this->htmlInstrumentationActive && $parseText === $text) {
                    $this->adoptHtmlContexts($text);
                }
            }

            $renderNodes = self::$standardRenderNodeCache[$cacheSlug];

            $this->nodeProcessor->setData($data);
            $this->nodeProcessor->setAntlersParserInstance($this);
            $this->nodeProcessor->cascade($this->cascade);

            $this->nodeProcessor->mergeRuntimeAssignments(GlobalRuntimeState::$tracedRuntimeAssignments);

            $initialTraceManager = $this->nodeProcessor->getRuntimeConfiguration()?->traceManager;
            $initialTraceDepth = 0;
            $renderCompleted = false;

            if ($initialTraceManager !== null) {
                $initialTraceDepth = $initialTraceManager->renderDepth();
                $this->nodeProcessor->triggerRenderStart();
            }

            try {
                $bufferContent = $this->nodeProcessor->render($renderNodes);

                if ($newLineStyle != "\n") {
                    if (Str::contains($bufferContent, "\r\n") == false) {
                        $bufferContent = str_replace("\n", "\r\n", $bufferContent);
                    }
                }

                $this->parseStack -= 1;
                $parseStackReleased = true;

                if ($this->parseStack == 0 && GlobalRuntimeState::$containsLayout == false) {
                    $bufferContent = LiteralReplacementManager::processReplacements($bufferContent);
                    $bufferContent = StackReplacementManager::processReplacements($bufferContent);

                    $bufferContent = str_replace(DocumentParser::getLeftBraceEscape(), DocumentParser::LeftBrace, $bufferContent);
                    $bufferContent = str_replace(DocumentParser::getRightBraceEscape(), DocumentParser::RightBrace, $bufferContent);
                }

                if (GlobalRuntimeState::$containsLayout && $this->view == GlobalRuntimeState::$shareVariablesTemplateTrigger) {
                    GlobalRuntimeState::$layoutVariables = $this->nodeProcessor->getRuntimeAssignments();
                }

                if ($isOutermostRender && $this->htmlInstrumentationActive) {
                    $bufferContent = $this->instrumentation !== null
                        ? $this->instrumentation->removeMisplacedMarkers($bufferContent, $this->htmlInstrumenters())
                        : HtmlInstrumentation::removeMisplacedMarkers($bufferContent, $this->htmlInstrumenters());
                }

                $renderCompleted = true;

                return $this->antlersString($bufferContent, $isView, $isOutermostRender);
            } finally {
                if ($initialTraceManager !== null || $this->nodeProcessor->getRuntimeConfiguration()?->traceManager !== null) {
                    $this->completeRenderTracing($initialTraceManager, $initialTraceDepth, $renderCompleted, $isOutermostRender);
                }
            }
        } catch (AntlersException $antlersException) {
            if ($this->isIgnitionInstalled()) {
                throw $this->buildAntlersExceptionError($antlersException, $text, $data);
            }

            throw $antlersException;
        } catch (ModifierNotFoundException $exception) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($exception->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_MODIFIER_NOT_FOUND;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $this->addAntlersErrorDetails($exception, $text, $data);
        } catch (Exception $exception) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($exception->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_RUNTIME_GENERAL_FAULT;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $this->addAntlersErrorDetails($exception, $text, $data);
        } catch (Throwable $throwable) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($throwable->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_RUNTIME_GENERAL_FAULT;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $this->addAntlersErrorDetails($throwable, $text, $data);
        } finally {
            self::$activeRenders--;

            if (! $parseStackReleased) {
                $this->parseStack -= 1;
            }
        }
    }

    /**
     * @param  string  $content
     * @param  bool  $isView
     * @param  bool  $isOutermostRender
     * @return AntlersString
     */
    private function antlersString($content, $isView, $isOutermostRender)
    {
        if ($this->htmlInstrumentationActive && ! $isView && ! $isOutermostRender && ! InstrumentationState::activeTagBody()?->composesOutput) {
            $instrumentation = $this->instrumentation ??= Runtime::getFacadeRoot();

            if ($instrumentation->containsMarkers($content)) {
                return AntlersString::withInstrumentation($content, $this, $instrumentation);
            }
        }

        return new AntlersString($content, $this);
    }

    private function completeRenderTracing(?Tracing\TraceManager $initialManager, int $initialDepth, bool $renderCompleted, bool $isOutermostRender)
    {
        $currentManager = $this->nodeProcessor->getRuntimeConfiguration()?->traceManager;
        $managers = [];

        if ($initialManager !== null && $initialManager->renderDepth() > $initialDepth) {
            $managers[] = $initialManager;
        }

        if ($isOutermostRender
            && $currentManager !== null
            && $currentManager !== $initialManager
            && $currentManager->renderDepth() > 0) {
            $managers[] = $currentManager;
        }

        $failure = null;

        foreach ($managers as $manager) {
            try {
                $manager->traceRenderComplete();
            } catch (Throwable $throwable) {

                if ($renderCompleted) {
                    $failure ??= $throwable;
                }
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    private function cleanUpTempFiles()
    {
        // Automatically clean up the temporary file.
        // If we wrap this in app()->terminating,
        // the file exists long enough for the
        // Ignition renderer to display it.
        if ($this->tempFileCreated != null) {
            $tmpFile = $this->tempFileCreated;
            app()->terminating(function () use ($tmpFile) {
                @unlink($tmpFile);
            });
            $this->tempFileCreated = null;
        }
    }

    private function addAntlersErrorDetails($exception, $text, $data)
    {
        // This is important to not completely bomb out the tests.
        if (! class_exists(ViewException::class)) {
            return $exception;
        }

        if ($exception instanceof ViewException || $exception instanceof ViewExceptionWithSolution) {
            return $exception;
        }

        if ($exception instanceof HttpException || $exception instanceof HttpResponseException) {
            return $exception;
        }

        $exceptionClass = ViewException::class;

        if (in_array(ProvidesSolution::class, class_implements($exception))) {
            $exceptionClass = ViewExceptionWithSolution::class;
        }

        $line = null;

        if (GlobalRuntimeState::$lastNode != null) {
            $line = GlobalRuntimeState::$lastNode->startPosition->line;
        }

        $newException = new $exceptionClass($exception->getMessage(), 0, 1, $this->view, $line, $exception);

        if ($exceptionClass === ViewExceptionWithSolution::class) {
            $newException->setSolution($exception->getSolution());
        }

        $rebuiltTrace = $this->buildStackTrace(GlobalRuntimeState::$lastNode, $text);
        $rebuiltTrace = array_merge($rebuiltTrace, $exception->getTrace());

        $traceProperty = new ReflectionProperty('Exception', 'trace');
        $traceProperty->setValue($newException, $rebuiltTrace);

        $this->cleanUpTempFiles();

        return $newException;
    }

    private function buildAntlersExceptionError(AntlersException $antlersException, $text, $data)
    {
        if ($antlersException->node == null && GlobalRuntimeState::$lastNode != null) {
            $antlersException->node = GlobalRuntimeState::$lastNode;
        } elseif ($antlersException->node == null && GlobalRuntimeState::$lastNode == null) {
            throw $antlersException;
        }

        $rebuiltTrace = $this->buildStackTrace($antlersException->node, $text);

        // Build up a new Ignition exception.
        $typeText = '';

        if ($antlersException->type != '') {
            $typeText = '['.$antlersException->type.']: ';
        }

        $newMessage = $typeText.$antlersException->getMessage().' '.LineRetriever::getErrorLineAndCharText($antlersException->node);

        $exceptionLine = 1;

        if ($antlersException->node->startPosition != null) {
            $exceptionLine = $antlersException->node->startPosition->line;
        }

        $exceptionClass = ViewException::class;

        if (class_exists('Spatie\LaravelIgnition\Exceptions\ViewException')) {
            $exceptionClass = 'Spatie\LaravelIgnition\Exceptions\ViewException';
        }

        $exceptionView = $this->view;

        if (GlobalRuntimeState::$userContentEvalState != null && ! empty(GlobalRuntimeState::$userContentEvalState)) {
            $dynamicExceptionLine = $exceptionLine;

            if (GlobalRuntimeState::$userContentEvalState[1] instanceof AbstractNode) {
                /** @var AbstractNode $node */
                $node = GlobalRuntimeState::$userContentEvalState[1];

                if ($node->startPosition != null) {
                    if (count($rebuiltTrace) >= 1) {
                        $rebuiltTrace[0]['line'] = $node->startPosition->line;
                    }
                }
            }

            if (GlobalRuntimeState::$userContentEvalState[0] instanceof Value) {
                /** @var Value $value */
                $value = GlobalRuntimeState::$userContentEvalState[0];
                $valueContent = (string) $value;

                $handle = $value->handle();

                $newMessage .= ' Exception thrown while parsing an Antlers enabled field ['.$handle.']';

                $valueAugmentable = $value->augmentable();

                if ($valueAugmentable != null) {
                    if (method_exists($valueAugmentable, 'path')) {
                        $path = $valueAugmentable->path();

                        $additionalInfo = <<<'INFO'

{{#
    {name} field defined in
        {path}
#}}
INFO;

                        $additionalInfo = str_replace('{name}', $handle, $additionalInfo);
                        $additionalInfo = str_replace('{path}', $path, $additionalInfo);

                        $valueContent .= $additionalInfo;
                    }
                }

                $this->makeTempAntlersFile($valueContent);
                $exceptionView = $this->tempFileCreated;
                $rebuiltTrace[] = [
                    'file' => $this->tempFileCreated,
                    'line' => $dynamicExceptionLine,
                ];
            }
        }

        $ignitionException = new $exceptionClass($newMessage, 0, 1, $exceptionView, $exceptionLine, $antlersException);
        $traceProperty = new ReflectionProperty('Exception', 'trace');
        $traceProperty->setValue($ignitionException, $rebuiltTrace);

        $ignitionException->setViewData($data);

        $this->cleanUpTempFiles();

        if (GlobalDebugManager::isDebugSessionActive()) {
            GlobalDebugManager::writeException($antlersException);
        }

        return $ignitionException;
    }

    private function makeTempAntlersFile($documentText)
    {
        $extension = 'html';

        if ($this->allowPhp) {
            $extension = 'php';
        }

        $antlersDirectory = storage_path('antlers/');

        if (! file_exists($antlersDirectory)) {
            @mkdir($antlersDirectory, 0755);
        }

        $this->tempFileCreated = storage_path('antlers/'.sha1($documentText).'.antlers.'.$extension);
        file_put_contents($this->tempFileCreated, $documentText);
    }

    private function buildStackTrace(AbstractNode $activeNode, $documentText)
    {
        if (count(GlobalRuntimeState::$templateFileStack) == 0) {
            $this->makeTempAntlersFile($documentText);

            $debugTrace = debug_backtrace();

            // The first item in the trace should be our current buildStackTrace call.
            // The second item should be the call to RuntimeParser renderText method.
            // The third item should be the user-land item that invoked the parse.
            $userLandCode = $debugTrace[2];
            $currentStack = [
                [$this->tempFileCreated, $activeNode],
                [$userLandCode['file'], $userLandCode['line']],

            ];
        } else {
            $currentStack = array_reverse(GlobalRuntimeState::$templateFileStack);
        }

        $currentStack[0][1] = $activeNode;
        $stackTrace = [];

        foreach ($currentStack as $stackItem) {
            $stackNode = $stackItem[1];
            $stackFile = $stackItem[0];

            if (is_object($stackNode)) {
                $stackLine = LineRetriever::getErrorLine($stackNode);
            } elseif (is_numeric($stackNode)) {
                $stackLine = $stackNode;
            } else {
                $stackLine = 1;
            }

            $stackTrace[] = [
                'file' => $stackFile,
                'line' => $stackLine,
            ];
        }

        return $stackTrace;
    }

    public function isolateRuntimes($isolate)
    {
        $this->isolateRuntimes = $isolate;

        return $this;
    }

    private function cloneRuntimeParser()
    {
        $parser = (new RuntimeParser(
            $this->documentParser,
            $this->nodeProcessor->cloneProcessor(),
            $this->antlersLexer, $this->antlersParser,
            $this->instrumentation
        ))->allowPhp($this->allowPhp);

        $parser->runtimeConfiguration = $this->runtimeConfiguration;
        $parser->instrumenterCache = $this->instrumenterCache;
        $parser->syncedVisitors = &$this->syncedVisitors;

        foreach ($this->preParsers as $preParser) {
            $parser->preparse($preParser);
        }

        if ($this->cascade != null) {
            $parser->cascade($this->cascade);
        }

        return $parser;
    }

    private function shouldIsolate()
    {
        if ($this->isolateRuntimes) {
            return true;
        }

        if (GlobalRuntimeState::$isEvaluatingUserData) {
            return true;
        }

        if (GlobalRuntimeState::$requiresRuntimeIsolation) {
            return true;
        }

        return false;
    }

    public function parse($text, $data = [], $instrument = true)
    {
        if ($this->shouldIsolate()) {
            return $this->cloneRuntimeParser()->renderText($text, $data, false, $instrument);
        }

        return $this->renderText($text, $data, false, $instrument);
    }

    public function render($text, $data = [])
    {
        return $this->renderText($text, $data);
    }

    public function valueWithNoparse($text)
    {
        // Just pass-thru.
        return $text;
    }

    public function extractNoparse($text)
    {
        // Just pass-thru.
        return $text;
    }

    public function allowPhp($allow = true)
    {
        $this->allowPhp = $allow;
        $this->nodeProcessor->allowPhp($this->allowPhp);

        return $this;
    }

    public function cascade($cascade)
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function parseView($view, $text, $data = [])
    {
        $instrument = ! ($data[self::CAPTURED_CONTENT] ?? false);
        unset($data[self::CAPTURED_CONTENT]);

        $previousIsEvaluatingUserData = GlobalRuntimeState::$isEvaluatingUserData;
        GlobalRuntimeState::$isEvaluatingUserData = false;

        $existingView = $this->view;

        $shouldSwapData = GlobalRuntimeState::$isolateViewData;
        GlobalRuntimeState::$isolateViewData = false;
        $suspendedData = $shouldSwapData ? $this->nodeProcessor->getAllData() : null;

        try {
            return $this->renderViewContent($view, $text, $data, $instrument);
        } finally {
            if ($shouldSwapData) {
                $this->nodeProcessor->swapData($suspendedData);
            }
            $this->view = $existingView;
            array_pop(GlobalRuntimeState::$templateFileStack);
            GlobalRuntimeState::$currentExecutionFile = $this->view;
            GlobalRuntimeState::$isEvaluatingUserData = $previousIsEvaluatingUserData;
        }
    }

    private function renderViewContent($view, $text, $data = [], $instrument = true)
    {
        $this->view = $view;
        GlobalRuntimeState::$templateFileStack[] = [$view, null];

        if (count(GlobalRuntimeState::$templateFileStack) > 1) {
            GlobalRuntimeState::$templateFileStack[count(GlobalRuntimeState::$templateFileStack) - 2][1] = GlobalRuntimeState::$lastNode;
        }

        GlobalRuntimeState::$currentExecutionFile = $this->view;

        if (GlobalDebugManager::$isConnected) {
            GlobalDebugManager::registerPathLocator($this->view);
        }

        $data = array_merge($data, [
            'view' => $this->cascade->getViewData($view),
        ]);

        return $this->renderText($text, $data, true, $instrument);
    }

    public function injectNoparse($text)
    {
        return $text;
    }

    /**
     * Constructs a "virtual" node from the provided text.
     *
     * @param  string  $text  The node content.
     * @return AntlersNode
     */
    private function wrapText($text)
    {
        $node = new AntlersNode();

        $mockPosition = new Position();
        $mockPosition->offset = 0;
        $mockPosition->line = 0;
        $mockPosition->char = 0;

        $node->startPosition = $mockPosition;
        $node->endPosition = $mockPosition;

        $node->content = $text;

        return $node;
    }

    /**
     * Tests if the full Antlers Lexer & Parser should be used to parse the provided text.
     *
     * @param  string  $text  The input to test.
     * @return bool
     */
    private function shouldUseFullParserForVariable($text)
    {
        return Str::contains($text, [
            DocumentParser::Punctuation_Pipe,
            DocumentParser::LeftBracket,
            DocumentParser::RightBracket,
            DocumentParser::LeftBrace,
            DocumentParser::RightBrace,
            DocumentParser::LeftParen,
            DocumentParser::RightParent,
            DocumentParser::Punctuation_Exclamation,
            DocumentParser::Punctuation_Question,
            DocumentParser::Punctuation_Ampersand,
            DocumentParser::Punctuation_Equals,
            LanguageKeywords::LogicalXor,
            LanguageKeywords::LogicalOr,
            LanguageKeywords::LogicalNot,
            LanguageKeywords::LogicalAnd,
        ]);
    }

    /**
     * Takes a scope-notated key and finds the value for it in the given
     * array or object.
     *
     * @param  string  $key  Dot-notated key to find
     * @param  mixed  $default  Default value to use if not found
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     */
    public function getVariable($key, $context, $default = null)
    {
        // If the incoming key has the | character, we will assume that
        // it contains modifiers. To handle this case, we will hand
        // the incoming string off to the Antlers lexer, parser
        // and finally, through the runtime processor & env.
        if ($this->shouldUseFullParserForVariable($key)) {
            // 1: Create a wrapper node to contain the text.
            $wrappedNode = $this->wrapText($key);
            $tokens = $this->antlersLexer->tokenize($wrappedNode, $key);
            $antlersNodes = $this->antlersParser->parse($tokens);
            //                           SG        LG
            $variableNode = $antlersNodes[0]->nodes[0];

            // We don't want to share the previous processor instance and
            // accidentally mess up it's internal state while handling
            // any variables that are being resolved for parameters.
            /** @var NodeProcessor $processor */
            $processor = app(NodeProcessor::class);
            $processor->cascade($this->cascade)->setData($context);

            // Evaluate the variable, and return the results.
            return $processor->evaluateDeferredVariable($variableNode);
        }

        $pathParser = new PathParser();

        $path = $pathParser->parse($key);

        $pathDataManager = new PathDataManager();
        $pathDataManager->setIsPaired(false);
        $pathDataManager->cascade($this->cascade);

        $results = $pathDataManager->getDataWithExistence($path, $context);

        if ($results[0] === true) {
            return $results[1];
        }

        return $default;
    }

    /**
     * Sets a render callback.
     *
     * @return Parser
     */
    public function callback($callback)
    {
        return $this;
    }

    /**
     * Clears the standard render node cache.
     */
    public static function clearRenderNodeCache()
    {
        self::$standardRenderNodeCache = [];
    }
}
