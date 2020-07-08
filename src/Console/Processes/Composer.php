<?php

namespace Statamic\Console\Processes;

use Illuminate\Support\Facades\Cache;
use Statamic\Jobs\RunComposer;

class Composer extends Process
{
    public $memoryLimit;

    /**
     * Instantiate composer process.
     *
     * @param mixed $basePath
     */
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        // Set this process to eleven.
        $this->toEleven();

        // Set memory limit for child process to eleven.
        $this->memoryLimit = config('statamic.system.php_memory_limit');
    }

    /**
     * List installed packages (including dev dependencies).
     *
     * @return \Illuminate\Support\Collection
     */
    public function installed()
    {
        return collect(json_decode($this->runComposerCommand('show', '--direct', '--format=json', '--no-plugins'))->installed)
            ->keyBy('name')
            ->map(function ($package) {
                $package->version = $this->normalizeVersion($package->version);

                return $package;
            });
    }

    /**
     * Get installed version of a specific package.
     *
     * We can easily use composer.lock in this case, which is more performant than running composer show.
     *
     * @param string $package
     * @return string
     */
    public function installedVersion(string $package)
    {
        $version = collect(json_decode(file_get_contents($this->basePath.'composer.lock'))->packages)
            ->keyBy('name')
            ->get($package)
            ->version;

        return $this->normalizeVersion($version);
    }

    /**
     * Get installed path of a specific package.
     *
     * @param string $package
     * @return string
     */
    public function installedPath(string $package)
    {
        return collect(json_decode($this->runComposerCommand('show', '--direct', '--path', '--format=json', '--no-plugins'))->installed)
            ->keyBy('name')
            ->get($package)
            ->path;
    }

    /**
     * Require a package.
     *
     * @param string $package
     * @param string|null $version
     */
    public function require(string $package, string $version = null)
    {
        $version
            ? $this->queueComposerCommand('require', $package, $version)
            : $this->queueComposerCommand('require', $package);
    }

    /**
     * Remove a package.
     *
     * @param string $package
     */
    public function remove(string $package)
    {
        $this->queueComposerCommand('remove', $package);
    }

    /**
     * Update a package.
     *
     * @param string $package
     */
    public function update(string $package)
    {
        $this->queueComposerCommand('update', $package);
    }

    /**
     * Get cached output for package process.
     *
     * @param string $package
     * @return mixed
     */
    public function cachedOutput(string $package)
    {
        return parent::cachedOutput($this->getCacheKey($package));
    }

    /**
     * Get cached output for last completed package process.
     *
     * @param string $package
     * @return mixed
     */
    public function lastCompletedCachedOutput(string $package)
    {
        return parent::lastCompletedCachedOutput($this->getCacheKey($package));
    }

    /**
     * Clear cached output.
     */
    public function clearCachedOutput(string $package)
    {
        Cache::forget($this->getCacheKey($package));
    }

    /**
     * Run composer and externally operate on ouput.
     *
     * @param mixed $command
     * @param mixed $operateOnOutput
     * @return string
     */
    public function runAndOperateOnOutput($command, $operateOnOutput)
    {
        $command = $this->prepareProcessArguments($command);

        return parent::runAndOperateOnOutput($command, $operateOnOutput);
    }

    /**
     * Run composer command.
     *
     * @param mixed $parts
     * @return mixed
     */
    private function runComposerCommand(...$parts)
    {
        return $this->run($this->prepareProcessArguments($parts));
    }

    /**
     * Queue composer command.
     *
     * @param string $command
     * @param string $package
     * @param mixed $extraParams
     */
    private function queueComposerCommand($command, $package, ...$extraParams)
    {
        $parts = array_merge([$command, $package], $extraParams);

        dispatch(new RunComposer($this->prepareProcessArguments($parts), $this->getCacheKey($package)));
    }

    /**
     * Prepare process arguments.
     *
     * @param array $parts
     * @return array
     */
    private function prepareProcessArguments($parts)
    {
        return array_merge([
            $this->phpBinary(),
            "-d memory_limit={$this->memoryLimit}",
            'vendor/bin/composer',
            '--ansi',
        ], $parts);
    }

    /**
     * Sometimes composer returns versions with a 'v', sometimes it doesn't.
     *
     * @param string $version
     * @return string
     */
    private function normalizeVersion(string $version)
    {
        return ltrim($version, 'v');
    }

    /**
     * Get cache key for composer output storage.
     *
     * @param string $package
     * @return string
     */
    private function getCacheKey($package)
    {
        return "composer.{$package}";
    }
}
