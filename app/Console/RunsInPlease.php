<?php

namespace Statamic\Console;

trait RunsInPlease
{
    /**
     * Remove statamic grouping from signature for statamic please command.
     */
    public function removeStatamicGrouping()
    {
        // Commands that extend GeneratorCommand expect a `$name` property instead of `$signature`!
        if (! isset($this->signature)) {
            $this->signature = $this->name;
        }

        $this->signature = str_replace('statamic:', '', $this->signature);

        $this->configureUsingFluentDefinition();
        $this->specifyParameters();
    }
}
