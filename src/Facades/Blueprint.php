<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\BlueprintRepository;

/**
 * @method static self setDirectory(string $directory)
 * @method static self setFallbackDirectory(string $directory)
 * @method static null|\Statamic\Fields\Blueprint find($handle)
 * @method static void save(Blueprint $blueprint)
 * @method static void delete(Blueprint $blueprint)
 * @method static \Statamic\Fields\Blueprint make($handle = null)
 * @method static \Statamic\Fields\Blueprint makeFromFields($fields)
 * @method static \Statamic\Fields\Blueprint makeFromSections($sections)
 *
 * @see \Statamic\Fields\Blueprint
 */
class Blueprint extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlueprintRepository::class;
    }
}
