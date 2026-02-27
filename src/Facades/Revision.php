<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Revisions\RevisionRepository;

/**
 * @method static string directory()
 * @method static \Statamic\Contracts\Revisions\Revision make()
 * @method static \Statamic\Support\FileCollection whereKey($key)
 * @method static \Statamic\Revisions\Revision findWorkingCopyByKey($key)
 * @method static void save(\Statamic\Contracts\Revisions\Revision $revision)
 * @method static void delete(\Statamic\Contracts\Revisions\Revision $revision)
 *
 * @see \Statamic\Revisions\RevisionRepository
 * @link \Statamic\Revisions\Revision
 */
class Revision extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RevisionRepository::class;
    }
}
