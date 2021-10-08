<?php

namespace Statamic\View\Antlers\Language\Runtime\Debugging;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Exceptions\AntlersException;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Runtime\Debugging\Tracers\TimingsTracer;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;

class GlobalDebugManager
{
    const TARGET_BREAKPOINTS = '_bpt/';
    const TARGET_BREAKPOINT_LOCKS = '_bpt_l/';
    const FILE_KEEP_ALIVE = 'debug-session';
    const FILE_TIMINGS = 'timings';
    const FILE_EXCEPTION = 'exception';

    protected static $managementDir = '';
    protected static $baseResourcePath = '';
    protected static $sessionKeepAliveFile = '';
    protected static $bpDir = '';
    protected static $bpLockDir = '';
    public static $isConnected = false;
    protected static $breakpointRegister = [];
    protected static $registeredBreakpointCount = 0;
    protected static $hasRegisteredBreakpoints = false;
    public static $activeSessionLocator = null;
    protected static $activeLockFile = null;
    protected static $exceptionWriteLock = [];

    /**
     * @var TimingsTracer|null
     */
    protected static $timingsTracer = null;

    public static function getTimingsTracer()
    {
        if (self::$timingsTracer == null) {
            self::$timingsTracer = new TimingsTracer();
        }

        return self::$timingsTracer;
    }

    public static function checkNodeForLocatorBreakpoint(AbstractNode $node, NodeProcessor $processor)
    {
        if (! array_key_exists(self::$activeSessionLocator, self::$breakpointRegister)) {
            return;
        }

        if ($node->startPosition == null) {
            return;
        }

        if (! array_key_exists($node->startPosition->line, self::$breakpointRegister[self::$activeSessionLocator])) {
            return;
        }

        self::writeCurrentLock($node->startPosition->line, $processor);
    }

    /**
     * Determines if the debug session is active.
     *
     * @return bool
     */
    public static function isDebugSessionActive()
    {
        if (! file_exists(self::$sessionKeepAliveFile)) {
            return false;
        }

        if (time() - filemtime(self::$sessionKeepAliveFile) >= 5) {
            return false;
        }

        clearstatcache();

        return true;
    }

    public static function writeException(AntlersException $exception)
    {
        if (GlobalRuntimeState::$currentExecutionFile != null) {
            $exceptionData = [];

            if (array_key_exists(GlobalRuntimeState::$currentExecutionFile, self::$exceptionWriteLock)) {
                return;
            }

            self::$exceptionWriteLock[GlobalRuntimeState::$currentExecutionFile] = 1;
            $exceptionData['msg'] = $exception->getMessage();
            $exceptionData['type'] = $exception->type;
            $exceptionData['file'] = str_replace('//', '/', substr(GlobalRuntimeState::$currentExecutionFile, strlen(self::$baseResourcePath)));
            $exceptionData['rl'] = $exception->node->startPosition->line;
            $exceptionData['rc'] = $exception->node->startPosition->char;
            $exceptionData['ll'] = GlobalRuntimeState::$lastNode->startPosition->line;
            $exceptionData['lc'] = GlobalRuntimeState::$lastNode->startPosition->char;

            file_put_contents(self::$managementDir.self::FILE_EXCEPTION, \json_encode($exceptionData));
        }
    }

    /**
     * Produces a list of stack frames from the runtime template stack.
     *
     * @return StackFrame[]
     */
    public static function getStackFrames()
    {
        if (empty(GlobalRuntimeState::$templateFileStack)) {
            return [];
        }

        $frames = [];
        $stackId = 0;
        foreach (GlobalRuntimeState::$templateFileStack as $stackData) {
            $stackSource = substr($stackData[0], strlen(self::$baseResourcePath));
            $stackName = basename($stackSource);
            $stackLine = 1;
            $stackColumn = 1;

            if ($stackData[1] instanceof AntlersNode) {
                $stackLine = $stackData[1]->startPosition->line;
                $stackColumn = $stackData[1]->startPosition->char;
            } else {
                if (GlobalRuntimeState::$lastNode != null) {
                    $stackLine = GlobalRuntimeState::$lastNode->startPosition->line;
                    $stackColumn = GlobalRuntimeState::$lastNode->startPosition->char;
                }
            }

            $stackFrame = new StackFrame();
            $stackFrame->line = $stackLine;
            $stackFrame->column = $stackColumn;
            $stackFrame->pathSource = $stackSource;
            $stackFrame->name = $stackName;
            $stackFrame->id = $stackId;

            $frames[] = $stackFrame;

            $stackId += 1;
        }

        return $frames;
    }

    private static function writeCurrentLock($line, NodeProcessor $processor)
    {
        $path = self::$bpLockDir;
        $lockPath = self::$activeSessionLocator.'_'.$line;
        $lockFile = $path.$lockPath;

        self::$activeLockFile = $lockFile;

        $activeData = $processor->getActiveData();
        $dump = [
            'data' => (new ScopeDumper())->dump($activeData),
            'frames' => self::getStackFrames(),
        ];

        file_put_contents($lockFile, \json_encode($dump, JSON_FORCE_OBJECT, 250));

        while (file_exists($lockFile)) {
            sleep(1);

            // Let's check if the debugger session is still "active".
            // If not, we will release all the locks ourselves.
            if (! self::isDebugSessionActive()) {
                self::releaseAllRuntimeLocks();
            }
        }

        // Now that the lock has released, let's reload
        // the breakpoint register since developers may
        // have been adjusting those while the runtime
        // was paused as they inspected the stack.
        self::clearRuntimeBreakpoints();

        if (self::isDebugSessionActive()) {
            self::loadRuntimeBreakpoints();
        }
    }

    public static function registerPathLocator($viewPath)
    {
        if ($viewPath == null || strlen(trim($viewPath)) == 0) {
            self::$activeSessionLocator = null;

            return;
        }

        // Remove any doubled up forward slashes if they happen.
        $locatorPath = substr($viewPath, strlen(self::$baseResourcePath));
        $locatorPath = str_replace('//', '/', $locatorPath);

        self::$activeSessionLocator = md5($locatorPath);
    }

    public static function loadDebugConfiguration($debugPath, $baseResourcePath)
    {
        self::$baseResourcePath = Str::finish($baseResourcePath, '/');
        $debugPath = Str::finish($debugPath, '/');
        self::$managementDir = $debugPath;
        self::$sessionKeepAliveFile = $debugPath.self::FILE_KEEP_ALIVE;
        self::$bpDir = $debugPath.self::TARGET_BREAKPOINTS;
        self::$bpLockDir = $debugPath.self::TARGET_BREAKPOINT_LOCKS;

        if (file_exists(self::$bpLockDir) && file_exists(self::$bpDir)) {
            self::$isConnected = true;
        }

        self::$isConnected = self::isDebugSessionActive();

        if (self::$isConnected) {
            self::releaseAllRuntimeLocks();
            self::loadRuntimeBreakpoints();

            app()->terminating(function () {
                self::writeTimings();
            });
        }
    }

    protected static function writeTimings()
    {
        if (self::$timingsTracer != null) {
            $timings = self::$timingsTracer->getTimings();
            $newTimings = [];
            foreach ($timings as $time) {
                if ($time[TimingsTracer::KEY_SOURCE] != null) {
                    $time[TimingsTracer::KEY_SOURCE] = substr($time[TimingsTracer::KEY_SOURCE], strlen(self::$baseResourcePath));
                }

                $newTimings[] = $time;
            }
            unset($timings);
            $path = self::$managementDir.self::FILE_TIMINGS;

            file_put_contents($path, \json_encode($newTimings));
        }
    }

    public static function hasAnyBreakpoints()
    {
        return self::$hasRegisteredBreakpoints;
    }

    /**
     * Releases all previously created runtime locks.
     */
    private static function releaseAllRuntimeLocks()
    {
        $allExistingLocks = self::getFiles(self::$bpLockDir);

        foreach ($allExistingLocks as $lock) {
            @unlink($lock);
        }
    }

    /**
     * Clears the internal breakpoint register.
     */
    private static function clearRuntimeBreakpoints()
    {
        self::$breakpointRegister = [];
    }

    private static function loadRuntimeBreakpoints()
    {
        $bpPaths = glob(self::$bpDir.'*', GLOB_ONLYDIR);

        foreach ($bpPaths as $path) {
            $parts = explode('/', $path);
            $runtimeSlug = array_pop($parts);
            $existingBreakpoints = self::getFiles($path);
            unset($parts);

            if (! array_key_exists($runtimeSlug, self::$breakpointRegister)) {
                self::$breakpointRegister[$runtimeSlug] = [];
            }

            foreach ($existingBreakpoints as $bpPath) {
                $contents = \json_decode(file_get_contents($bpPath), true);

                $runtimeBreakpoint = new Breakpoint();
                $runtimeBreakpoint->path = $contents['path'];
                $runtimeBreakpoint->line = $contents['line'];
                $runtimeBreakpoint->debugId = $contents['encode'];

                self::$breakpointRegister[$runtimeSlug][$runtimeBreakpoint->line] = $runtimeBreakpoint;
                self::$registeredBreakpointCount += 1;
            }
        }

        self::$hasRegisteredBreakpoints = self::$registeredBreakpointCount > 0;
    }

    private static function getFiles($path)
    {
        $paths = [];
        $path = Str::finish($path, '/');

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    $paths[] = $path.$entry;
                }
            }
            closedir($handle);
        }

        return $paths;
    }
}
