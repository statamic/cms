<?php

namespace Statamic\StarterKits;

use Illuminate\Support\Collection;

final class InstallableModules extends Modules
{
    protected $installer;

    /**
     * Set installer instance.
     */
    public function installer(?Installer $installer): self
    {
        $this->installer = $installer;

        return $this;
    }

    /**
     * Instantiate individual InstallableModule.
     */
    protected function instantiateIndividualModule(array|Collection $config, string $key): Module
    {
        return (new InstallableModule($config, $key))->installer($this->installer);
    }

    /**
     * Override so that we do not prefix option key for installable modules.
     */
    protected function prefixOptionsKey(string $key): ?string
    {
        return $key;
    }

    /**
     * Override so that we do not prefix modules key for installable modules.
     */
    protected function prefixModulesKey(string $key): ?string
    {
        return $key;
    }
}
