<?php

namespace Statamic\Console\Processes;

class Git extends Process
{
    /**
     * Get git status.
     *
     * @param string|null $path
     * @return string
     */
    public function status($path = null)
    {
        return $this->runGitCommand('status', '-s', $path);
    }

    /**
     * Get git root at current path.
     *
     * @return string
     */
    public function root()
    {
        return $this->runGitCommand('rev-parse', '--show-toplevel');
    }

    /**
     * Run git command.
     *
     * @param mixed $parts
     * @return mixed
     */
    private function runGitCommand(...$parts)
    {
        return $this->run($this->prepareProcessArguments($parts));
    }

    /**
     * Queue git command.
     *
     * @param string $command
     * @param string $package
     * @param mixed $extraParams
     */
    // private function queueGitCommand($command, $package, ...$extraParams)
    // {
    //     $parts = array_merge([$command, $package], $extraParams);

    //     dispatch(new RunComposer($this->prepareProcessArguments($parts), $this->getCacheKey($package)));
    // }

    /**
     * Prepare process arguments.
     *
     * @param array $parts
     * @return array
     */
    private function prepareProcessArguments($parts)
    {
        return array_merge(['git'], $parts);
    }
}
