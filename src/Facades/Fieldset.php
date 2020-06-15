<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\FieldsetRepository;

/**
 * @method static self setDirectory($directory)
 * @method static null|\Statamic\Fields\Fieldset find(string $handle)
 * @method static bool exists(string $handle)
 * @method static \Statamic\Fields\Fieldset make($handle = null)
 * @method static \Illuminate\Support\Collection all()
 * @method static void save(Fieldset $fieldset)
 * @method static void delete(Fieldset $fieldset)
 *
 * @see \Statamic\Fields\FieldsetRepository
 */
class Fieldset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FieldsetRepository::class;
    }
}
