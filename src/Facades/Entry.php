<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\EntryRepository;

/**
 * @method static \Statamic\Entries\EntryCollection all()
 * @method static \Statamic\Entries\EntryCollection whereCollection(string $handle)
 * @method static \Statamic\Entries\EntryCollection whereInCollection(array $handles)
 * @method static null|\Statamic\Contracts\Entries\Entry mixed find($id)
 * @method static null|\Statamic\Contracts\Entries\Entry findByUri(string $uri)
 * @method static null|\Statamic\Contracts\Entries\Entry findBySlug(string $slug, string $collection)
 * @method static \Statamic\Entries\Entry make()
 * @method static \Statamic\Stache\Query\EntryQueryBuilder query()
 * @method static void save($entry)
 * @method static void delete($entry)
 * @method static array createRules($collection, $site)
 * @method static array updateRules($collection, $entry)
 *
 * @see \Statamic\Entries\EntryCollection
 * @see \Statamic\Stache\Repositories\EntryRepository
 *
 */
class Entry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EntryRepository::class;
    }
}
