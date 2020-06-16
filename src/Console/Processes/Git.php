<?php

namespace Statamic\Console\Processes;

class Git extends Process
{
    /**
     * Get git root.
     *
     * @return string
     */
    public function root()
    {
        return $this->runGitCommand('rev-parse', '--show-toplevel');
    }

    /**
     * Get git status.
     *
     * @param mixed $subPaths
     * @return string
     */
    public function status($subPaths = null)
    {
        return $this->runGitCommand('status', '-s', $subPaths);
    }

    /**
     * Git add.
     *
     * @param mixed $subPaths
     * @return $this
     */
    public function add($subPaths)
    {
        return $this->runGitCommand('add', $subPaths);
    }

    /**
     * Git commit.
     *
     * @param mixed $subPaths
     * @return null
     */
    public function commit($message)
    {
        return $this->runGitCommand('commit', '-m', $message);
    }

    /**
     * Git push.
     *
     * @return null
     */
    public function push()
    {
        return $this->runGitCommand('push');
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
        return collect(['git'])
            ->merge($parts)
            ->flatten()
            ->reject(function ($part) {
                return is_null($part);
            })
            ->all();
    }
}
