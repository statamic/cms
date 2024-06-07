<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Actions\ActionRepository;
use Statamic\Dictionaries\DictionaryRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Dictionaries\Dictionary find(string $dictionary)
 *
 * @see \Statamic\Actions\DictionaryRepository
 */
class Dictionary extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DictionaryRepository::class;
    }
}
