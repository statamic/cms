<?php

namespace Statamic\Extend;

use Symfony\Component\Console\Output\OutputInterface;

abstract class Uninstaller
{
    protected OutputInterface $output;
    protected Addon $addon;

    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function setAddon(Addon $addon): self
    {
        $this->addon = $addon;

        return $this;
    }

    abstract public function handle();
}
