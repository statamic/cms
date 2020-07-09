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
        return $this->runGitCommand('status', '--porcelain', $subPaths);
    }

    /**
     * Git push.
     *
     * @return null
     */
    public function push()
    {
        return $this->runGitCommand('push', '--porcelain');
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
