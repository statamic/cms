<?php

namespace Statamic\StarterKits;

use Illuminate\Support\Collection;

class ExportableModules extends Modules
{
    /**
     * Instantiate individual ExportableModule.
     */
    protected function instantiateIndividualModule(array|Collection $config, string $key): Module
    {
        return new ExportableModule($config, $key);
    }
}
