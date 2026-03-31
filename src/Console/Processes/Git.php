<?php

namespace Statamic\Console\Processes;

use Statamic\Support\Str;

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
     * Determine if currently in a git repo.
     *
     * @return bool
     */
    public function isRepo()
    {
        $this->withoutLoggingErrors(function ($process) {
            $process->root();
        });

        return ! $this->hasErrorOutput();
    }

    /**
     * Get git status.
     *
     * @param  mixed  $subPaths
     * @return string
     */
    public function status($subPaths = null)
    {
        return $this->runGitCommand('status', '--porcelain', $subPaths);
    }

    /**
     * Git push.
     */
    public function push()
    {
        return $this->runGitCommand('push', '--porcelain');
    }

    /**
     * Get git diff --name-status between two refs.
     *
     * @param  string  $from  Base commit SHA or ref
     * @param  string  $to  Target commit SHA or ref (e.g. 'HEAD')
     * @return string
     */
    public function diff(string $from, string $to = 'HEAD')
    {
        return $this->runGitCommand('diff', '--name-status', $from, $to);
    }

    /**
     * Get git diff --name-status for dirty (unstaged) files.
     *
     * @return string
     */
    public function diffDirty()
    {
        return $this->runGitCommand('diff', '--name-status', 'HEAD');
    }

    /**
     * Get git diff --name-status for staged (cached) files.
     *
     * @return string
     */
    public function diffStaged()
    {
        return $this->runGitCommand('diff', '--name-status', '--cached', 'HEAD');
    }

    /**
     * Get the current HEAD commit SHA.
     *
     * @return string
     */
    public function currentSha()
    {
        return $this->runGitCommand('rev-parse', 'HEAD');
    }

    /**
     * Run git command.
     *
     * @param  mixed  $parts
     * @return mixed
     */
    private function runGitCommand(...$parts)
    {
        return $this->run($this->prepareProcessArguments($parts));
    }

    /**
     * Prepare process arguments.
     *
     * @param  array  $parts
     * @return array
     */
    private function prepareProcessArguments($parts)
    {
        return collect([config('statamic.git.binary')])
            ->merge($parts)
            ->flatten()
            ->reject(function ($part) {
                return is_null($part);
            })
            ->all();
    }

    /**
     * Prepare error (stderr) output.
     *
     * @param  string  $type
     * @param  string  $buffer
     */
    protected function prepareErrorOutput($type, $buffer)
    {
        $ignore = [
            'remote: Resolving deltas',
            'Permanently added the ECDSA host key for IP address',
            'remote: Processed',
            'Auto packing the repository',
            'remote: GitHub found',
        ];

        if (Str::contains($buffer, $ignore)) {
            return;
        }

        parent::prepareErrorOutput($type, $buffer);
    }
}
