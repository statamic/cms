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
        return $this->addon->package();
    }

    public function currentVersion()
    {
        return $this->addon->version();
    }

    protected function isLicensed($version)
    {
        return version_compare($version, $this->addon->license()->versionLimit() ?? 999999, '<');
    }
}
