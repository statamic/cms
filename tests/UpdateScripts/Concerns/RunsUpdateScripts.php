<?php

namespace Tests\UpdateScripts\Concerns;

trait RunsUpdateScripts
{
    /**
     * Run update script in your tests without checking package version.
     *
     * @param  string  $fqcn
     * @param  string  $package
     */
    protected function runUpdateScript($fqcn, $package = 'statamic/cms')
    {
        $script = new $fqcn($package);

        $script->update();
    }
}
