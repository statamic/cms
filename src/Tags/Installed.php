<?php

namespace Statamic\Tags;

use Facades\Statamic\Console\Processes\Composer;

class Installed extends Tags
{
    /**
     * Check if composer package is installed via {{ installed package="vendor/package" }}.
     *
     * @return string|void
     */
    public function index()
    {
        if (! $package = $this->params->get('package')) {
            return;
        }

        return $this->installed($package);
    }

    /**
     * Check if composer package is installed via {{ installed:* }}.
     *
     * @param  string  $package
     * @return string|void
     */
    public function wildcard($package)
    {
        return $this->installed($package);
    }

    protected function installed($package)
    {
        $installed = Composer::isInstalled($package);

        if (! $this->isPair) {
            return $installed;
        }

        if ($installed) {
            return $this->parse();
        }
    }
}
