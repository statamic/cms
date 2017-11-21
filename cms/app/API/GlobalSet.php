<?php

namespace Statamic\API;

use Statamic\Contracts\Data\Globals\GlobalFactory;
use Statamic\Data\Services\GlobalsService;

class GlobalSet
{
    /**
     * Create a global set
     *
     * @param string $slug
     * @return GlobalFactory
     */
    public static function create($slug)
    {
        return app(GlobalFactory::class)->create($slug);
    }

    /**
     * Find a global set by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     */
    public static function whereHandle($handle)
    {
        return app(GlobalsService::class)->handle($handle);
    }

    /**
     * Get global by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Globals\GlobalSet
     */
    public static function find($id)
    {
        return app(GlobalsService::class)->id($id);
    }

    /**
     * Get all globals
     *
     * @return \Statamic\Data\Globals\GlobalCollection
     */
    public static function all()
    {
        return app(GlobalsService::class)->all()->sortBy(function ($global) {
            return $global->title();
        });
    }
}
