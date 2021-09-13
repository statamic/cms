<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries;

use Statamic\View\Antlers\Language\Errors\AntlersErrorCodes;
use Statamic\View\Antlers\Language\Errors\ErrorFactory;
use Statamic\View\Antlers\Language\Exceptions\LibraryCallException;
use Statamic\View\Antlers\Language\Nodes\LibraryInvocationConstruct;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\ArrayLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\ConvertLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\DateTimeLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\FileLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\JsonLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\MathLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\MethodLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\PathLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\RequestLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\ScopeLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\StopwatchLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\StringLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\SystemLibrary;
use Statamic\View\Antlers\Language\Runtime\Libraries\Internal\UrlLibrary;
use Statamic\View\Antlers\Language\Runtime\Sandbox\Environment;

class LibraryManager
{
    const ARG_NORMAL = 'a';
    const ARG_NAMED = 'n';

    private $hasLoadedCore = false;

    /**
     * @var RuntimeLibrary[]
     */
    protected $loadedLibraries = [];

    public static $deferredCoreLibraries = [
        'arr' => 1,
        'array' => 1,
        'str' => 1,
        'string' => 1,
        'method' => 1,
        'math' => 1,
        'sys' => 1,
        'convert' => 1,
        'scope' => 1,
        'stopwatch' => 1,
        'file' => 1,
        'path' => 1,
        'url' => 1,
        'request' => 1,
        'json' => 1,
        'datetime' => 1,
    ];

    public function registerLibrary(RuntimeLibrary $library)
    {
        $name = $library->getName();

        if (! is_array($name)) {
            $name = [$name];
        }

        foreach ($name as $libraryName) {
            if (array_key_exists($libraryName, $this->loadedLibraries)) {
                throw ErrorFactory::makeLibraryError(
                    AntlersErrorCodes::TYPE_RUNTIME_ATTEMPT_TO_OVERWRITE_LOADED_LIBRARY,
                    'Fatal Runtime Violation: Attempted to overwrite loaded library: '.$libraryName
                );
            }

            $this->loadedLibraries[$libraryName] = $library;
        }
    }

    private function loadDeferredCoreLibrary($library)
    {
        if ($library == 'str' || $library == 'string') {
            $this->registerLibrary(new StringLibrary());
        } elseif ($library == 'arr' || $library == 'array') {
            $this->registerLibrary(new ArrayLibrary());
        } elseif ($library == 'math') {
            $this->registerLibrary(new MathLibrary());
        } elseif ($library == 'sys') {
            $this->registerLibrary(new SystemLibrary());
        } elseif ($library == 'convert') {
            $this->registerLibrary(new ConvertLibrary());
        } elseif ($library == 'stopwatch') {
            $this->registerLibrary(new StopwatchLibrary());
        } elseif ($library == 'method') {
            $this->registerLibrary(new MethodLibrary());
        } elseif ($library == 'file') {
            $this->registerLibrary(new FileLibrary());
        } elseif ($library == 'path') {
            $this->registerLibrary(new PathLibrary());
        } elseif ($library == 'url') {
            $this->registerLibrary(new UrlLibrary());
        } elseif ($library == 'request') {
            $this->registerLibrary(new RequestLibrary());
        } elseif ($library == 'json') {
            $this->registerLibrary(new JsonLibrary());
        } elseif ($library == 'scope') {
            $this->registerLibrary(new ScopeLibrary());
        } elseif ($library == 'datetime') {
            $this->registerLibrary(new DateTimeLibrary());
        }
    }

    public function loadCoreLibraries()
    {
        if ($this->hasLoadedCore) {
            return;
        }

        $this->hasLoadedCore = true;
    }

    public function tryExecute(LibraryInvocationConstruct $invocation, Environment $hostEnvironment)
    {
        if ($this->loadedLibraries[$invocation->libraryName]->hasMethod($invocation->methodName)) {
            return $this->loadedLibraries[$invocation->libraryName]->invokeMethod($invocation->methodName, $invocation->arguments, $hostEnvironment);
        }

        return null;
    }

    /**
     * Tests if a valid library exists with the provided name.
     *
     * @param  string  $name  The library name.
     * @return bool
     *
     * @throws LibraryCallException
     */
    public function hasLibrary($name)
    {
        $hasLibrary = array_key_exists($name, $this->loadedLibraries);

        if (! $hasLibrary && array_key_exists($name, self::$deferredCoreLibraries)) {
            $this->loadDeferredCoreLibrary($name);

            $hasLibrary = true;
        }

        if ($hasLibrary && GlobalRuntimeState::$isEvaluatingUserData) {
            if ($this->loadedLibraries[$name]->canBeExecutedByValueType() == false) {
                throw ErrorFactory::makeLibraryError(
                    AntlersErrorCodes::TYPE_RUNTIME_PROTECTED_LIBRARY_ACCESS_VIOLATION,
                    'Fatal Runtime Access Violation: Attempted to call runtime protected library from user input.'
                );
            }
        }

        return $hasLibrary;
    }
}
