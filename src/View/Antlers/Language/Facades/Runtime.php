<?php

namespace Statamic\View\Antlers\Language\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\View\Instrumentation\InstrumentationManager;

/**
 * @method static InstrumentationManager preparse(callable $callable)
 * @method static InstrumentationManager removePreparser(callable $callable)
 * @method static InstrumentationManager addVisitor(\Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract $visitor)
 * @method static InstrumentationManager removeVisitor(\Statamic\View\Antlers\Language\Runtime\Tracing\NodeVisitorContract $visitor)
 * @method static InstrumentationManager sync()
 * @method static InstrumentationManager register(object|string $piece, ?array $engines = null)
 * @method static InstrumentationManager forget(object|string $piece, ?array $engines = null)
 * @method static InstrumentationManager flush()
 * @method static mixed using(object|string $piece, callable $callback, ?array $engines = null)
 * @method static void applyTo(\Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration $runtimeConfiguration)
 * @method static \Illuminate\Support\Collection regions(string $html)
 * @method static bool instrumenting()
 * @method static bool containsMarkers(string $html)
 * @method static string removeMarkers(string $html)
 * @method static string removeMisplacedMarkers(string $html, ?array $instrumenters = null)
 * @method static int misplacedMarkerRemovals()
 * @method static int removedMisplacedMarkerBytes()
 * @method static string trimAroundMarkers(string $html)
 * @method static array splitMarkers(string $output)
 * @method static \Statamic\View\Instrumentation\TagBody tagBody(\Statamic\View\Antlers\Language\Nodes\AntlersNode $node, bool $composesOutput = false)
 *
 * @see InstrumentationManager
 */
class Runtime extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InstrumentationManager::class;
    }
}
