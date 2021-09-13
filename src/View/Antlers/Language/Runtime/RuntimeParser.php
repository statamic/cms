<?php

namespace Statamic\View\Antlers\Language\Runtime;

use Facade\Ignition\Exceptions\ViewException;
use ReflectionProperty;
use Statamic\Contracts\Antlers\ParserContract;
use Statamic\Modifiers\ModifierNotFoundException;
use Statamic\Search\Comb\Exceptions\Exception;
use Statamic\Support\Str;
use Statamic\Tags\TagNotFoundException;
use Statamic\View\Antlers\AntlersString;
use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Cascade;
use Statamic\View\Antlers\Language\Errors\LineRetriever;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Exceptions\RuntimeException;
use Statamic\View\Antlers\Language\Exceptions\SyntaxErrorException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\Debugging\GlobalDebugManager;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Throwable;

class RuntimeParser implements ParserContract
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

    public function __construct(DocumentParser $documentParser, NodeProcessor $nodeProcessor)
    {
        $this->documentParser = $documentParser;
        $this->nodeProcessor = $nodeProcessor;
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

        if (Str::contains($text, DocumentParser::LeftBrace)) {
            return true;
        }

        return false;
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
        $text = $this->runPreParserCallbacks($text);

        if (! $this->canPossiblyParseAntlers($text)) {
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

            if (! array_key_exists($cacheSlug, self::$standardRenderNodeCache)) {
                if (strlen($this->view) > 0) {
                    $seedStartLine = $this->getFrontMatterLineOffset($this->view);
                    $this->documentParser->setStartLineSeed($seedStartLine);
                } else {
                    $this->documentParser->setStartLineSeed(1);
                }

                if (! empty(GlobalRuntimeState::$globalTagEnterStack)) {
                    /** @var AntlersNode $lastTagNode */
                    $lastTagNode = GlobalRuntimeState::$globalTagEnterStack[count(GlobalRuntimeState::$globalTagEnterStack) - 1];
                    $this->documentParser->setStartLineSeed($lastTagNode->endPosition->line);
                }

                self::$standardRenderNodeCache[$cacheSlug] = $this->documentParser->parse($parseText);
            }

            $renderNodes = self::$standardRenderNodeCache[$cacheSlug];

            $this->nodeProcessor->setData($data);
            $this->nodeProcessor->setAntlersParserInstance($this);
            $this->nodeProcessor->cascade($this->cascade);

            $bufferContent = $this->nodeProcessor->render($renderNodes);
            $this->nodeProcessor->triggerRenderComplete();
        } catch (AntlersException $antlersException) {
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
            $ignitionException = new ViewException($newMessage);
            $traceProperty = new ReflectionProperty('Exception', 'trace');
            $traceProperty->setAccessible(true);
            $traceProperty->setValue($ignitionException, $rebuiltTrace);

            $ignitionException->setViewData($data);

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

            if (GlobalDebugManager::isDebugSessionActive()) {
                GlobalDebugManager::writeException($antlersException);
            }

            throw $ignitionException;
        } catch (ModifierNotFoundException $exception) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($exception->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_MODIFIER_NOT_FOUND;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $exception;
        } catch (Exception $exception) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($exception->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_RUNTIME_GENERAL_FAULT;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $exception;
        } catch (Throwable $throwable) {
            if (GlobalRuntimeState::$lastNode != null && GlobalDebugManager::isDebugSessionActive()) {
                $wrapper = new AntlersException($throwable->getMessage());
                $wrapper->node = GlobalRuntimeState::$lastNode;
                $wrapper->type = AntlersErrorCodes::TYPE_RUNTIME_GENERAL_FAULT;

                GlobalDebugManager::writeException($wrapper);
            }

            throw $throwable;
        }

        if ($newLineStyle != "\n") {
            if (Str::contains($bufferContent, "\r\n") == false) {
                $bufferContent = str_replace("\n", "\r\n", $bufferContent);
            }
        }

        $bufferContent = LiteralReplacementManager::processReplacements($bufferContent);
        $bufferContent = StackReplacementManager::processReplacements($bufferContent);

        return new AntlersString($bufferContent, $this);
    }

    private function buildStackTrace(AbstractNode $activeNode, $documentText)
    {
        if (count(GlobalRuntimeState::$templateFileStack) == 0) {
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

    public function parse($text, $data = [])
    {
        return $this->renderText($text, $data);
    }

    public function valueWithNoParse($text)
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
     * Takes a scope-notated key and finds the value for it in the given
     * array or object.
     *
     * @param  string  $key  Dot-notated key to find
     * @param $context
     * @param  mixed  $default  Default value to use if not found
     * @return mixed
     *
     * @throws RuntimeException
     * @throws SyntaxErrorException
     */
    public function getVariable($key, $context, $default = null)
    {
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
     * @param $callback
     * @return ParserContract
     */
    public function callback($callback)
    {
        return $this;
    }
}
