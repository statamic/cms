<?php

namespace Statamic\Composer;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process
{
    const CACHE_EXPIRY_MINUTES = 10;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $output;

    /**
     * Instantiate process.
     *
     * @param mixed $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = str_finish($basePath ?? base_path(), '/');
    }

    /**
     * Run the command.
     *
     * @param string|array $command
     * @param string $cacheKey
     * @return mixed
     * @throws ProcessFailedException
     */
    public function run($command, $cacheKey = null)
    {
        $process = new SymfonyProcess($command, $this->basePath);
        $process->setTimeout(null);

        if ($cacheKey) {
            $this->runAndCacheOutput($process, $cacheKey);
            return;
        }

        return $this->runAndReturnOutput($process);
    }

    /**
     * Run and return output when finished.
     *
     * @param SymfonyProcess $process
     * @return string
     */
    private function runAndReturnOutput($process)
    {
        $process->run(function ($type, $buffer) use (&$output) {
            $this->output .= $buffer;
        });

        return $this->output;
    }

    /**
     * Run and append output to cache as it's generated.
     *
     * @param SymfonyProcess $process
     * @param string $cacheKey
     */
    private function runAndCacheOutput($process, $cacheKey)
    {
        Cache::forget($cacheKey);

        $this->appendOutputToCache($cacheKey, null);

        $process->run(function ($type, $buffer) use ($cacheKey) {
            $this->appendOutputToCache($cacheKey, $buffer);
        });

        $this->setCompletedOnCache($cacheKey);
    }

    /**
     * Append output to cache.
     *
     * @param string $cacheKey
     * @param string $output
     */
    private function appendOutputToCache($cacheKey, $output)
    {
        Cache::put($cacheKey, [
            'completed' => false,
            'output' => $this->output .= $output,
        ], self::CACHE_EXPIRY_MINUTES);
    }

    /**
     * Set completed on cache.
     *
     * @param string $cacheKey
     */
    private function setCompletedOnCache($cacheKey)
    {
        Cache::put($cacheKey, [
            'completed' => true,
            'output' => $this->output,
        ], self::CACHE_EXPIRY_MINUTES);
    }

    /**
     * Get last cached output.
     *
     * @param string $cacheKey
     * @return mixed
     */
    public function lastCachedOutput(string $cacheKey)
    {
        return Cache::get($cacheKey)['completed'] ? Cache::get($cacheKey)['output'] : false;
    }

    /**
     * Absolute path to PHP Binary.
     *
     * @return string
     */
    public function phpBinary()
    {
        return (new PhpExecutableFinder)->find();
    }

    /**
     * Crank parent process up to eleven (doesn't apply to child process called by run method).
     */
    public function toEleven()
    {
        @ini_set('memory_limit', config('statamic.system.php_memory_limit'));

        @set_time_limit(config('statamic.system.php_max_execution_time'));
    }
}
