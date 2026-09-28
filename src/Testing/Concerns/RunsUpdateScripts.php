<?php

namespace Statamic\Testing\Concerns;

trait RunsUpdateScripts
{
    /**
     * Run update script in your tests without checking package version.
     */
    protected function runUpdateScript(string $fqcn, ?string $package = null)
    {
        $package = $package ?: $this->getPackage();

        $script = new $fqcn($package);

        $script->update();

        return $script;
    }

    protected function assertUpdateScriptRegistered(string $class)
    {
        $this->assertTrue(
            app('statamic.update-scripts')->map->class->contains($class),
            "Update script $class is not registered."
        );
    }
}
