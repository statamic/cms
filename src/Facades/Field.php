<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\FieldRepository;

/**
 * @method static \Statamic\Fields\Field|null find(string $field)
 * @method static void computedDefault(string $key, \Closure $callback)
 * @method static mixed resolveComputedDefault(string $key, mixed $payload = null)
 *
 * @see FieldRepository
 */
class Field extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FieldRepository::class;
    }
}
