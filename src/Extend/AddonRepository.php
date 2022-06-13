<?php

namespace Statamic\Extend;

class AddonRepository
{
    protected $addons;

    /**
     * Make an addon instance.
     *
     * @param  string|array  $addon  The name of the addon. This will be converted to StudlyCase.
     *                               Or, an array containing package data.
     * @return Addon
     */
    public function make($addon)
    {
        $method = is_array($addon) ? 'makeFromPackage' : 'make';

        return Addon::$method($addon);
    }

    /**
     * Get all the addons.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->addons = $this->addons ?? app(Manifest::class)->addons()->map(function ($addon) {
            return $this->make($addon);
        });
    }

    /**
     * Get an addon instance.
     *
     * @return Addon
     */
    public function get($id)
    {
        return $this->all()->get($id);
    }
}
