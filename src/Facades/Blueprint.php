<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\BlueprintRepository;

/**
 * @method static self setDirectory(string $directory)
 * @method static string directory()
 * @method static null|\Statamic\Fields\Blueprint find(string $handle)
 * @method static \Statamic\Fields\Blueprint findOrFail(string $handle)
 * @method static string findStandardBlueprintPath(string $handle)
 * @method static string findNamespacedBlueprintPath(string $handle)
 * @method static self setFallback(string $handle, Closure $blueprint)
 * @method static \Statamic\Fields\Blueprint findFallback(string $handle)
 * @method static void save(\Statamic\Fields\Blueprint $blueprint)
 * @method static void delete(\Statamic\Fields\Blueprint $blueprint)
 * @method static void reset(\Statamic\Fields\Blueprint $blueprint)
 * @method static \Statamic\Fields\Blueprint make($handle = null)
 * @method static \Statamic\Fields\Blueprint makeFromFields($fields)
 * @method static \Statamic\Fields\Blueprint makeFromTabs($tabs)
 * @method static \Illuminate\Support\Collection in(string $namespace)
 * @method static void addNamespace(string $namespace, string $directory)
 * @method static \Illuminate\Support\Collection getAdditionalNamespaces()
 *
 * @see \Statamic\Fields\BlueprintRepository
 * @link \Statamic\Fields\Blueprint
 */
class Blueprint extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlueprintRepository::class;
    }
}
