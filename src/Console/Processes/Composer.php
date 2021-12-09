<?php

namespace Statamic\Console\Processes;

use Illuminate\Support\Facades\Cache;
use Statamic\Console\Composer\Lock;
use Statamic\Jobs\RunComposer;

class Composer extends Process
{
    protected $memoryLimit;
    protected $withoutQueue = false;

    /**
     * Instantiate composer process.
     *
     * @param  mixed  $basePath
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
     * Run without queue.
     *
     * @return $this
     */
    public function withoutQueue()
    {
        $this->withoutQueue = true;

        return $this;
    }

    /**
     * Check if specific package is installed.
     *
     * @param  string  $package
     * @return bool
     */
    public function isInstalled(string $package)
    {
        return Lock::file($this->basePath.'composer.lock')->isPackageInstalled($package);
    }

    /**
     * List installed packages (including dev dependencies).
     *
     * @return \Illuminate\Support\Collection
     */
    public function installed()
    {
        $lock = Lock::file($this->basePath.'composer.lock');

        if (! $lock->exists()) {
            return collect();
        }

        return collect($this->runJsonComposerCommand('show', '--direct', '--no-plugins')->installed)
            ->keyBy('name')
            ->map(function ($package) use ($lock) {
                $package->version = $this->normalizeVersion($package->version);
                $package->dev = $lock->isDevPackageInstalled($package->name);

                return $package;
            });
    }

    /**
     * Get installed version of a specific package.
     *
     * @param  string  $package
     * @return string
     */
    public function installedVersion(string $package)
    {
        $lock = Lock::file($this->basePath.'composer.lock');

        if (! $lock->exists()) {
            return null;
        }

        $version = $lock->getInstalledVersion($package);

        return $this->normalizeVersion($version);
    }

    /**
     * Get installed path of a specific package.
     *
     * @param  string  $package
     * @return string
     */
    public function installedPath(string $package)
    {
        return collect($this->runJsonComposerCommand('show', '--direct', '--path', '--no-plugins')->installed)
            ->keyBy('name')
            ->get($package)
            ->path;
    }

    /**
     * Require a package.
     *
     * @param  string  $package
     * @param  string|null  $version
     * @param  mixed  $extraParams
     */
    public function require(string $package, string $version = null, ...$extraParams)
    {
        if ($version) {
            $parts[] = $version;
        }

        $parts[] = '--update-with-dependencies';

        $parts = array_merge($parts, $extraParams);

        $this->queueComposerCommand('require', $package, ...$parts);
    }

    /**
     * Require a dev package.
     *
     * @param  string  $package
     * @param  string|null  $version
     */
    public function requireDev(string $package, string $version = null, ...$extraParams)
    {
        $this->require($package, $version, '--dev', ...$extraParams);
    }

    /**
     * Remove a package.
     *
     * @param  string  $package
     * @param  mixed  $extraParams
     */
    public function remove(string $package, ...$extraParams)
    {
        $this->queueComposerCommand('remove', $package, ...$extraParams);
    }

    /**
     * Remove a dev package.
     *
     * @param  string  $package
     */
    public function removeDev(string $package)
    {
        $this->remove($package, '--dev');
    }

    /**
     * Update a package.
     *
     * @param  string  $package
     */
    public function update(string $package)
    {
        $this->queueComposerCommand('update', $package, '--with-dependencies');
    }

    /**
     * Get cached output for package process.
     *
     * @param  string  $package
     * @return mixed
     */
    public function cachedOutput(string $package)
    {
        return parent::cachedOutput($this->getCacheKey($package));
    }

    /**
     * Get cached output for last completed package process.
     *
     * @param  string  $package
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
     * @param  mixed  $command
     * @param  mixed  $operateOnOutput
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
     * @param  mixed  $parts
     * @return mixed
     */
    public function runComposerCommand(...$parts)
    {
        return $this->run($this->prepareProcessArguments($parts));
    }

    /**
     * Run json composer command.
     *
     * @param  mixed  $parts
     * @return string
     */
    private function runJsonComposerCommand(...$parts)
    {
        $output = $this->runComposerCommand(...array_merge($parts, ['--format=json']));

        // Strip out php8 deprecation warnings
        $json = substr($output, strpos($output, "\n{"));

        return json_decode($json);
    }

    /**
     * Queue composer command.
     *
     * @param  string  $command
     * @param  string  $package
     * @param  mixed  $extraParams
     */
    private function queueComposerCommand($command, $package, ...$extraParams)
    {
        if ($this->withoutQueue) {
            return $this->runComposerCommand($command, $package, ...$extraParams);
        }

        $parts = array_merge([$command, $package], $extraParams);

        dispatch(new RunComposer($this->prepareProcessArguments($parts), $this->getCacheKey($package)));
    }

    /**
     * Prepare process arguments.
     *
     * @param  array  $parts
     * @return array
     */
    private function prepareProcessArguments($parts)
    {
        return array_merge([
            $this->phpBinary(),
            "-d memory_limit={$this->memoryLimit}",
            'vendor/bin/composer',
            $this->colorized ? '--ansi' : '--no-ansi',
        ], $parts);
    }

    /**
     * Sometimes composer returns versions with a 'v', sometimes it doesn't.
     *
     * @param  string  $version
     * @return string
     */
    private function normalizeVersion(string $version)
    {
        return ltrim($version, 'v');
    }

    /**
     * Get cache key for composer output storage.
     *
     * @param  string  $package
     * @return string
     */
    private function getCacheKey($package)
    {
        return "composer.{$package}";
    }
}
