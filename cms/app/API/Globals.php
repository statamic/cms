<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class Globals
{
    /**
     * @param string $slug
     * @return \Statamic\Contracts\Data\Globals\GlobalFactory
     * @deprecated since 2.1
     */
    public static function create($slug)
    {
        \Log::notice('Globals::create() is deprecated. Use GlobalSet::create()');

        return GlobalSet::create($slug);
    }

    /**
     * Get a global by UUID
     *
     * @param string $uuid
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     * @deprecated since 2.1
     */
    public static function getByUuid($uuid)
    {
        \Log::notice('Globals::getByUuid() is deprecated. Use GlobalSet::find()');

        return GlobalSet::find($uuid);
    }

    /**
     * Get a global by its slug
     *
     * @param string      $slug
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     * @deprecated since 2.1
     */
    public static function getBySlug($slug)
    {
        \Log::notice('Globals::getBySlug() is deprecated. Use GlobalSet::whereHandle()');

        return GlobalSet::whereHandle($slug);
    }

    /**
     * @return \Statamic\Data\Globals\GlobalCollection
     * @deprecated since 2.1
     */
    public static function getAll()
    {
        \Log::notice('Globals::getAll() is deprecated. Use GlobalSet::all()');

        return GlobalSet::all();
    }
}
