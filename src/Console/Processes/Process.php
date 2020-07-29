<?php

namespace Statamic\Console\Processes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Statamic\Support\Arr;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process as SymfonyProcess;

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
     * @var bool
     */
    protected $colorized = false;

    /**
     * Create new process on path.
     *
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = str_finish($basePath ?? base_path(), '/');
    }

    /**
     * Create new process on path.
     *
     * @param string|null $basePath
     * @return static
     */
    public static function create($basePath = null)
    {
        return new static($basePath);
    }

    /**
     * Run the command.
     *
     * @param string|array $command
     * @param string|null $cacheKey
     * @return mixed
     * @throws ProcessFailedException
     */
    public function run($command, $cacheKey = null)
    {
        $process = $this->newSymfonyProcess($command, $this->basePath);

        if ($cacheKey) {
            $this->runAndCacheOutput($process, $cacheKey);

            return;
        }

        return $this->runAndReturnOutput($process);
    }

    /**
     * Run and externally operate on ouput.
     *
     * @param mixed $command
     * @param mixed $operateOnOutput
     * @return string
     */
    public function runAndOperateOnOutput($command, $operateOnOutput)
    {
        $process = $this->newSymfonyProcess($command, $this->basePath);

        $this->output = null;

        $process->run(function ($type, $buffer) use (&$output, $operateOnOutput) {
            $this->logErrorOutput($type, $buffer);
            $this->output .= $operateOnOutput($buffer);
        });

        return $this->output;
    }

    /**
     * Run and return output when finished.
     *
     * @param SymfonyProcess $process
     * @return string
     */
    private function runAndReturnOutput($process)
    {
        $this->output = null;

        $process->run(function ($type, $buffer) use (&$output) {
            $this->logErrorOutput($type, $buffer);
            $this->output .= $buffer;
        });

        return $this->normalizeOutput($this->output);
    }

    /**
     * Run and append output to cache as it's generated.
     *
     * @param SymfonyProcess $process
     * @param string $cacheKey
     */
    private function runAndCacheOutput($process, $cacheKey)
    {
        $this->output = null;

        Cache::forget($cacheKey);

        $this->appendOutputToCache($cacheKey, null);

        $process->run(function ($type, $buffer) use ($cacheKey) {
            $this->logErrorOutput($type, $buffer);
            $this->appendOutputToCache($cacheKey, $buffer);
        });

        $this->setCompletedOnCache($cacheKey);
    }

    /**
     * Log error (stderr) output.
     *
     * @param string $type
     * @param string $buffer
     */
    private function logErrorOutput($type, $buffer)
    {
        if ($type !== 'err') {
            return;
        }

        $process = (new \ReflectionClass($this))->getShortName();

        Log::error("{$process} Process: {$buffer}");
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
        ], now()->addMinutes(self::CACHE_EXPIRY_MINUTES));
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
        ], now()->addMinutes(self::CACHE_EXPIRY_MINUTES));
    }

    /**
     * Get cached output.
     *
     * @param string $cacheKey
     * @return array
     */
    public function cachedOutput(string $cacheKey)
    {
        $cache = Cache::get($cacheKey) ?? ['output' => false];

        $cache['output'] = $this->normalizeOutput($cache['output']);

        return $cache;
    }

    /**
     * Get cached output for last completed process.
     *
     * @param string $cacheKey
     * @return array
     */
    public function lastCompletedCachedOutput(string $cacheKey)
    {
        $cache = $this->cachedOutput($cacheKey);

        return Arr::get($cache, 'completed') ? $cache : ['output' => false];
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

    public function colorized()
    {
        $this->colorized = true;

        return $this;
    }

    /**
     * Normalize output.
     *
     * @param mixed $output
     * @return mixed
     */
    public function normalizeOutput($output)
    {
        if (is_null($output)) {
            return $output;
        }

        // Remove terminal color codes.
        if (! $this->colorized) {
            $output = preg_replace('/\\e\[[0-9]+m/', '', $output);
        }

        // Remove trailing new line.
        $output = preg_replace('/\\n$/', '', $output);

        return $output;
    }

    /**
     * New symfony process.
     *
     * @param string $command
     * @param string|null $path
     * @return SymfonyProcess
     */
    protected function newSymfonyProcess($command, $path = null)
    {
        // Ensure command is either an array or string.
        if (! is_array($command)) {
            $command = (string) $command;
        }

        // Handle both string and array command formats.
        $process = is_string($command) && method_exists(SymfonyProcess::class, 'fromShellCommandLine')
            ? SymfonyProcess::fromShellCommandline($command, $path ?? $this->basePath)
            : new SymfonyProcess($command, $path ?? $this->basePath);

        $process->setTimeout(null);

        return $process;
    }
}
