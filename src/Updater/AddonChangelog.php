<?php

namespace Statamic\Updater;

class AddonChangelog extends Changelog
{
    protected $addon;

    public function __construct($addon)
    {
        $this->addon = $addon;
    }

    public function item()
    {
        return $this->addon->marketplaceSellerSlug().'/'.$this->addon->marketplaceSlug();
    }

    public function currentVersion()
    {
        return $this->addon->version();
    }
}
