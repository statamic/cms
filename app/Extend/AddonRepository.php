<?php

namespace Statamic\Extend;

use Statamic\Extend\Management\Manifest;

class AddonRepository
{
    /**
     * Create an addon instance.
     *
     * @param string|array $addon  The name of the addon. This will be converted to StudlyCase.
     *                             Or, an array containing package data.
     * @return Addon
     */
    public function create($addon)
    {
        $method = is_array($addon) ? 'createFromPackage' : 'create';

        return Addon::$method($addon);
    }

    /**
     * Get all the addons.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return app(Manifest::class)->addons()->map(function ($addon) {
            return $this->create($addon);
        });
    }

    /**
     * Get an addon instance.
     *
     * @return Addon
     */
    public function get($id)
    {
        return $this->create(
            app(Manifest::class)->addons()->get($id)
        );
    }
}
