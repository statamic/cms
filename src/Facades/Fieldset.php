<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\FieldsetRepository;

/**
 * @method static self setDirectory($directory)
 * @method static string directory()
 * @method static null|\Statamic\Fields\Fieldset find(string $handle)
 * @method static \Statamic\Fields\Fieldset findOrFail(string $handle)
 * @method static bool exists(string $handle)
 * @method static \Statamic\Fields\Fieldset make($handle = null)
 * @method static \Illuminate\Support\Collection all()
 * @method static void save(\Statamic\Fields\Fieldset $fieldset)
 * @method static void delete(\Statamic\Fields\Fieldset $fieldset)
 * @method static void reset(\Statamic\Fields\Fieldset $fieldset)
 * @method static void addNamespace(string $namespace, string $directory)
 *
 * @see \Statamic\Fields\FieldsetRepository
 * @see \Statamic\Fields\Fieldset
 */
class Fieldset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FieldsetRepository::class;
    }
}
