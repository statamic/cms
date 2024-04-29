<?php

namespace Statamic\Console\Processes;

use Illuminate\Support\Facades\Cache;
use Statamic\Console\Composer\Lock;
use Statamic\Jobs\RunComposer;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

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
     * @param  mixed  $extraParams
     */
    public function require(string $package, ?string $version = null, ...$extraParams)
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
     */
    public function requireDev(string $package, ?string $version = null, ...$extraParams)
    {
        $this->require($package, $version, '--dev', ...$extraParams);
    }

    /**
     * Require multiple packages at once.
     *
     * @param  mixed  $extraParams
     */
    public function requireMultiple(array $packages, ...$extraParams)
    {
        $parts = array_merge($this->normalizePackagesArrayToRequireArgs($packages), $extraParams);

        $parts[] = '--update-with-dependencies';

        $this->queueComposerCommand('require', ...$parts);
    }

    /**
     * Require multiple dev packages at once.
     *
     * @param  mixed  $extraParams
     */
    public function requireMultipleDev(array $packages, ...$extraParams)
    {
        $this->requireMultiple($packages, '--dev', ...$extraParams);
    }

    /**
     * Remove a package.
     *
     * @param  mixed  $extraParams
     */
    public function remove(string $package, ...$extraParams)
    {
        $this->queueComposerCommand('remove', $package, ...$extraParams);
    }

    /**
     * Remove a dev package.
     *
     * @param  mixed  $extraParams
     */
    public function removeDev(string $package, ...$extraParams)
    {
        $this->remove($package, '--dev', ...$extraParams);
    }

    /**
     * Remove multiple packages at once.
     *
     * @param  mixed  $extraParams
     */
    public function removeMultiple(array $packages, ...$extraParams)
    {
        $parts = array_merge($packages, $extraParams);

        $this->queueComposerCommand('remove', ...$parts);
    }

    /**
     * Remove multiple dev packages at once.
     *
     * @param  mixed  $extraParams
     */
    public function removeMultipleDev(array $packages, ...$extraParams)
    {
        $this->removeMultiple($packages, '--dev', ...$extraParams);
    }

    /**
     * Update a package.
     */
    public function update(string $package)
    {
        $this->queueComposerCommand('update', $package, '--with-dependencies');
    }

    /**
     * Get cached output for package process.
     *
     * @return mixed
     */
    public function cachedOutput(string $package)
    {
        return parent::cachedOutput($this->getCacheKey($package));
    }

    /**
     * Get cached output for last completed package process.
     *
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
            $this->composerBinary(),
            $this->colorized ? '--ansi' : '--no-ansi',
        ], $parts);
    }

    /**
     * Absolute path to the Composer binary.
     */
    private function composerBinary(): string
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        $output = $this->run($isWindows ? 'where composer' : 'which composer');

        if ($isWindows) {
            return $this->locateComposerPharOnWindows($output);
        }

        return $output;
    }

    private function locateComposerPharOnWindows($output): string
    {
        $output = StringUtilities::normalizeLineEndings($output);

        if (! Str::contains($output, "\n")) {
            $candidates = [trim($output)];
        } else {
            $candidates = explode("\n", $output);
        }

        foreach ($candidates as $candidate) {
            // Do we have a bat file? The phar is likely beside it.
            if (Str::endsWith($candidate, '.bat')) {
                // Remove that 🦇 extension.
                $candidate = mb_substr($candidate, 0, mb_strlen($candidate) - 4);
            }

            $pharPath = $candidate.'.phar';

            if (file_exists($pharPath)) {
                // Use "composer.phar" if we have it.
                return $pharPath;
            }
        }

        return $output;
    }

    /**
     * Sometimes composer returns versions with a 'v', sometimes it doesn't.
     *
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

    /**
     * Normalize packages array to require args, with version handling if `package => version` array structure is passed.
     *
     * @return array
     */
    private function normalizePackagesArrayToRequireArgs(array $packages)
    {
        return collect($packages)
            ->map(function ($value, $key) {
                return Str::contains($key, '/')
                    ? "{$key}:{$value}"
                    : "{$value}";
            })
            ->values()
            ->all();
    }
}
