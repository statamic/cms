<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\EntryRepository;

/**
 * @method static \Statamic\Entries\EntryCollection all()
 * @method static \Statamic\Entries\EntryCollection whereCollection(string $handle)
 * @method static \Statamic\Entries\EntryCollection whereInCollection(array $handles)
 * @method static null|\Statamic\Contracts\Entries\Entry find($id)
 * @method static null|\Statamic\Contracts\Entries\Entry findByUri(string $uri, string $site)
 * @method static \Statamic\Contracts\Entries\Entry make()
 * @method static \Statamic\Contracts\Entries\QueryBuilder query()
 * @method static void save($entry)
 * @method static void delete($entry)
 * @method static array createRules($collection, $site)
 * @method static array updateRules($collection, $entry)
 * @method static void substitute($entry)
 *
 * @see \Statamic\Stache\Repositories\EntryRepository
 * @see \Statamic\Stache\Query\EntryQueryBuilder
 * @see \Statamic\Entries\EntryCollection
 * @see \Statamic\Entries\Entry
 */
class Entry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EntryRepository::class;
    }
}
