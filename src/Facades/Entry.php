<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\EntryRepository;

/**
 * @method static \Statamic\Contracts\Entries\EntryRepository all()
 * @method static \Statamic\Contracts\Entries\EntryRepository whereCollection(string $handle)
 * @method static \Statamic\Contracts\Entries\EntryRepository whereInCollection(array $handles);
 * @method static \Statamic\Contracts\Entries\EntryRepository find($id)
 * @method static \Statamic\Contracts\Entries\EntryRepository findByUri(string $uri)
 * @method static \Statamic\Contracts\Entries\EntryRepository findBySlug(string $slug, string $collection)
 * @method static \Statamic\Contracts\Entries\EntryRepository make()
 * @method static \Statamic\Contracts\Entries\EntryRepository query()
 * @method static \Statamic\Contracts\Entries\EntryRepository save($entry)
 * @method static \Statamic\Contracts\Entries\EntryRepository delete($entry)
 * @method static \Statamic\Contracts\Entries\EntryRepository createRules($collection, $site)
 * @method static \Statamic\Contracts\Entries\EntryRepository updateRules($collection, $entry)
 *
 * @see \Statamic\Contracts\Entries\EntryRepository;
 */
class Entry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EntryRepository::class;
    }
}
