<?php

namespace Statamic\Updater;

class AddonChangelog extends Changelog
{
    protected $addon;

    public function __construct($addon)
    {
        $this->addon = $addon;
    }

    public function slug()
    {
        return $this->addon->marketplaceSlug();
    }

    public function currentVersion()
    {
        return $this->addon->version();
    }
}
