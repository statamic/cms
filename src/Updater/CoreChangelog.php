<?php

namespace Statamic\Updater;

use Statamic\Statamic;
use Statamic\Support\Str;

class CoreChangelog extends Changelog
{
    public function item()
    {
        return Statamic::PACKAGE;
    }

    public function currentVersion()
    {
        return Statamic::version();
    }

    public function get()
    {
        return parent::get()->map(function ($release) {
            $release->canUpdate = $this->canUpdateToVersion($release->version);

            return $release;
        });
    }

    private function canUpdateToVersion($version)
    {
        $currentVersion = Statamic::version();

        if (Str::contains($currentVersion, 'dev')) {
            return true; // If you're on dev-master, 3.2.x-dev, etc - go nuts.
        }

        // Just get the major.minor numbers so we can compare them.

        $currentVersion = preg_match('/^[0-9]+\.[0-9]+/', $currentVersion, $matches);
        $currentVersion = $matches[0];

        $version = preg_match('/^[0-9]+\.[0-9]+/', $version, $matches);
        $version = $matches[0];

        return version_compare($currentVersion, $version, '>=');
    }
}
