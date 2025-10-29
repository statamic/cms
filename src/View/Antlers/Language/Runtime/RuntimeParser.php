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
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Position;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageKeywords;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class RuntimeParser implements Parser
{
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

    public function __construct(DocumentParser $documentParser, NodeProcessor $nodeProcessor, AntlersLexer $lexer, LanguageParser $antlersParser)
    {
        $this->documentParser = $documentParser;
        $this->nodeProcessor = $nodeProcessor;
        $this->antlersLexer = $lexer;
        $this->antlersParser = $antlersParser;
    }

    /**
     * Sets the RuntimeConfiguration instance.
     *
     * @param  RuntimeConfiguration  $configuration  The RuntimeConfiguration instance.
     * @return $this
     */
    public function setRuntimeConfiguration(RuntimeConfiguration $configuration)
    {
        GlobalRuntimeState::$allowPhpInContent = $configuration->allowPhpInUserContent;
        GlobalRuntimeState::$throwErrorOnAccessViolation = $configuration->throwErrorOnAccessViolation;
        GlobalRuntimeState::$bannedVarPaths = $configuration->guardedVariablePatterns;
        GlobalRuntimeState::$bannedContentVarPaths = $configuration->guardedContentVariablePatterns;
        GlobalRuntimeState::$bannedTagPaths = $configuration->guardedTagPatterns;
        GlobalRuntimeState::$bannedContentTagPaths = $configuration->guardedContentTagPatterns;
        GlobalRuntimeState::$bannedModifierPaths = $configuration->guardedModifiers;
        GlobalRuntimeState::$bannedContentModifierPaths = $configuration->guardedContentModifiers;

        $this->nodeProcessor->setRuntimeConfiguration($configuration);

        foreach ($configuration->getPreparsers() as $preparser) {
            $this->preparse($preparser);
        }

        foreach ($configuration->getVisitors() as $visitor) {
            $this->documentParser->addVisitor($visitor);
        }

        return $this;
    }

    /**
     * Resets the internal runtime configuration instance.
     *
     * @return $this
     */
    public function resetRuntimeConfiguration()
    {
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
     * @return string
     */
    protected function runPreParserCallbacks($text)
    {
        $value = $text;

        foreach ($this->preParsers as $preParser) {
            $value = call_user_func($preParser, $value);
        }

        return $value;
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

        if (Str::contains($text, DocumentParser::LeftBrace)) {
            return true;
        }

        return false;
    }

    /**
     * Adds a list of nodes to the internal node cache.
     *
     * @param  string  $text  The source content.
     * @param  AbstractNode[]  $nodes  The parsed nodes.
     */
    public static function pushNodeCache($text, $nodes)
    {
        self::$standardRenderNodeCache[md5($text)] = $nodes;
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
    protected function renderText($text, $data = [])
    {
        $this->parseStack += 1;
        $text = $this->runPreParserCallbacks($text);

        if (! $this->canPossiblyParseAntlers($text)) {
            $text = $this->sanitizePhp($text);

            $this->parseStack -= 1;

            if ($text == null) {
                return new AntlersString('', $this);
            }

            return new AntlersString($text, $this);
        }

        $newLineStyle = StringUtilities::detectNewLineStyle($text);
        $bufferContent = '';

        try {
            $parseText = $this->sanitizePhp($text);
            $cacheSlug = md5($parseText);

            if (! array_key_exists($cacheSlug, self::$standardRenderNodeCache) || ! $this->shouldCacheRenderNodes($text)) {
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

                    if ($lastTagNode->name->name != 'partial') {
                        $this->documentParser->setStartLineSeed($lastTagNode->endPosition->line);
                    }
                }
                self::$standardRenderNodeCache[$cacheSlug] = $this->documentParser->parse($parseText);
            }

            $renderNodes = self::$standardRenderNodeCache[$cacheSlug];

            $this->nodeProcessor->setData($data);
            $this->nodeProcessor->setAntlersParserInstance($this);
            $this->nodeProcessor->cascade($this->cascade);

            $this->nodeProcessor->mergeRuntimeAssignments(GlobalRuntimeState::$tracedRuntimeAssignments);
            $bufferContent = $this->nodeProcessor->render($renderNodes);

            $this->nodeProcessor->triggerRenderComplete();
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
        }

        if ($newLineStyle != "\n") {
            if (Str::contains($bufferContent, "\r\n") == false) {
                $bufferContent = str_replace("\n", "\r\n", $bufferContent);
            }
        }

        $this->parseStack -= 1;

        if ($this->parseStack == 0 && GlobalRuntimeState::$containsLayout == false) {
            $bufferContent = LiteralReplacementManager::processReplacements($bufferContent);
            $bufferContent = StackReplacementManager::processReplacements($bufferContent);

            $bufferContent = str_replace(DocumentParser::getLeftBraceEscape(), DocumentParser::LeftBrace, $bufferContent);
            $bufferContent = str_replace(DocumentParser::getRightBraceEscape(), DocumentParser::RightBrace, $bufferContent);
        }

        if (GlobalRuntimeState::$containsLayout && $this->view == GlobalRuntimeState::$shareVariablesTemplateTrigger) {
            // Force the root runtime assignments to be merged into the global state.
            GlobalRuntimeState::$layoutVariables = $this->nodeProcessor->getRuntimeAssignments();
        }

        return new AntlersString($bufferContent, $this);
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
        $traceProperty->setAccessible(true);
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
        $traceProperty->setAccessible(true);
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
            $this->antlersLexer, $this->antlersParser
        ))->allowPhp($this->allowPhp);

        // If we are evaluating a tag's scope, we still
        // want the overall parser instances to be
        // isolated, but we also need the Cascade.
        if (GlobalRuntimeState::$evaulatingTagContents) {
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

    public function parse($text, $data = [])
    {
        if ($this->shouldIsolate()) {
            return $this->cloneRuntimeParser()->renderText($text, $data);
        }

        return $this->renderText($text, $data);
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
        $existingView = $this->view;
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

        $parsed = $this->renderText($text, $data);

        $this->view = $existingView;

        array_pop(GlobalRuntimeState::$templateFileStack);

        GlobalRuntimeState::$currentExecutionFile = $this->view;

        return $parsed;
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
