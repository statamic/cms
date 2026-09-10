<?php

namespace Statamic\View\Antlers\Language\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\View\Instrumentation\InstrumentationManager;

/**
 * @method static void preparse(callable $callable)
 * @method static void addVisitor(\Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract $visitor)
 */
class Runtime extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InstrumentationManager::class;
    }
}
