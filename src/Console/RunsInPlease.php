<?php

namespace Statamic\Console;

use Statamic\Support\Str;

trait RunsInPlease
{
    /**
     * Is command running in please?
     *
     * @var bool
     */
    public $runningInPlease = false;

    /**
     * Set running in please.
     */
    public function setRunningInPlease()
    {
        $this->runningInPlease = true;
    }

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

    /**
     * If `hiddenInPlease` property is set, override hidden status when running please.
     */
    public function setHiddenInPlease()
    {
        if (isset($this->hiddenInPlease)) {
            $this->setHidden($this->hiddenInPlease);
        }
    }

    protected function resolveCommand($command)
    {
        if ($this->runningInPlease && Str::startsWith($command, 'statamic:')) {
            $command = Str::after($command, 'statamic:');
        }

        return parent::resolveCommand($command);
    }
}
