<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Revisions\RevisionRepository;

/**
 * @method static mixed whereKey($key)
 * @method static \Statamic\Revisions\Revision findWorkingCopyByKey($key)
 * @method static void save(Revision $revision)
 * @method static void delete(Revision $revision)
 * @method static \Statamic\Contracts\Revisions\Revision make()
 *
 * @see \Statamic\Revisions\RevisionRepository
 * @see \Statamic\Revisions\Revision
 */
class Revision extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RevisionRepository::class;
    }
}
