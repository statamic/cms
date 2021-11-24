<?php

namespace Statamic\Console\Processes;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
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
     * @var bool
     */
    protected $throwOnFailure = false;

    /**
     * @var array
     */
    protected $errorOutput = [];

    /**
     * @var bool
     */
    protected $logErrorOutput = true;

    /**
     * @var array
     */
    protected $env = [];

    /**
     * Create new process on path.
     *
     * @param  string|null  $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = str_finish($basePath ?? base_path(), '/');

        $this->env = $this->constructEnv();
    }

    /**
     * Construct the environment variables that will be passed to the process.
     *
     * @return array
     */
    protected function constructEnv()
    {
        $env = collect(getenv())->only(['HOME', 'LARAVEL_SAIL', 'COMPOSER_HOME', 'APPDATA', 'LOCALAPPDATA']);

        if (! $env->has('HOME') && $env->get('LARAVEL_SAIL') === '1') {
            $env['HOME'] = '/home/sail';
        }

        return $env->all();
    }

    /**
     * Create new process on path.
     *
     * @param  string|null  $basePath
     * @return static
     */
    public static function create($basePath = null)
    {
        return new static($basePath);
    }

    /**
     * Run the command.
     *
     * @param  string|array  $command
     * @param  string|null  $cacheKey
     * @return mixed
     */
    public function run($command, $cacheKey = null)
    {
        $this->resetOutput();

        $process = $this->newSymfonyProcess($command, $this->basePath);

        if ($cacheKey) {
            $this->runAndCacheOutput($process, $cacheKey);

            return;
        }

        $output = $this->runAndReturnOutput($process);

        if ($this->throwOnFailure && $process->getExitCode() > 0) {
            $this->throwException($output);
        }

        return $output;
    }

    /**
     * Run and externally operate on ouput.
     *
     * @param  mixed  $command
     * @param  mixed  $operateOnOutput
     * @return string
     */
    public function runAndOperateOnOutput($command, $operateOnOutput)
    {
        $this->resetOutput();

        $process = $this->newSymfonyProcess($command, $this->basePath);

        $process->run(function ($type, $buffer) use (&$output, $operateOnOutput) {
            $this->prepareErrorOutput($type, $buffer);
            $this->output .= $operateOnOutput($buffer);
        }, $this->env);

        $this->logErrorOutput();

        if ($this->throwOnFailure && $process->getExitCode() > 0) {
            $this->throwException($this->output);
        }

        return $this->output;
    }

    /**
     * Check if process has error output.
     *
     * @return bool
     */
    public function hasErrorOutput()
    {
        return (bool) $this->errorOutput;
    }

    /**
     * Run callback without logging errors.
     *
     * @param  Closure  $callable
     * @return mixed
     */
    public function withoutLoggingErrors(Closure $callback)
    {
        $this->logErrorOutput = false;

        $output = $callback($this);

        $this->logErrorOutput = true;

        return $output;
    }

    /**
     * Run and return output when finished.
     *
     * @param  SymfonyProcess  $process
     * @return string
     */
    private function runAndReturnOutput($process)
    {
        $this->resetOutput();

        $process->run(function ($type, $buffer) use (&$output) {
            $this->prepareErrorOutput($type, $buffer);
            $this->output .= $buffer;
        }, $this->env);

        $this->logErrorOutput();

        return $this->normalizeOutput($this->output);
    }

    /**
     * Run and append output to cache as it's generated.
     *
     * @param  SymfonyProcess  $process
     * @param  string  $cacheKey
     */
    private function runAndCacheOutput($process, $cacheKey)
    {
        $this->resetOutput();

        Cache::forget($cacheKey);

        $this->appendOutputToCache($cacheKey, null);

        $process->run(function ($type, $buffer) use ($cacheKey) {
            $this->prepareErrorOutput($type, $buffer);
            $this->appendOutputToCache($cacheKey, $buffer);
        }, $this->env);

        $this->logErrorOutput();

        $this->setCompletedOnCache($cacheKey);
    }

    /**
     * Prepare error (stderr) output.
     *
     * @param  string  $type
     * @param  string  $buffer
     */
    private function prepareErrorOutput($type, $buffer)
    {
        if ($type !== 'err') {
            return;
        }

        if (! $error = trim($buffer)) {
            return true;
        }

        $this->errorOutput[] = $error;
    }

    /**
     * Log error output.
     */
    private function logErrorOutput()
    {
        if (! $this->logErrorOutput) {
            return;
        }

        if (! $this->hasErrorOutput()) {
            return;
        }

        $process = (new \ReflectionClass($this))->getShortName();

        $error = collect($this->errorOutput)->implode("\n");

        Log::error("{$process} Process: {$error}");
    }

    /**
     * Append output to cache.
     *
     * @param  string  $cacheKey
     * @param  string  $output
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
     * @param  string  $cacheKey
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
     * @param  string  $cacheKey
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
     * @param  string  $cacheKey
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

    /**
     * Show colorized output.
     *
     * @return $this
     */
    public function colorized()
    {
        $this->colorized = true;

        return $this;
    }

    /**
     * Throw exception on process failure.
     *
     * @param  bool  $throwOnFailure
     * @return $this
     */
    public function throwOnFailure($throwOnFailure = null)
    {
        $this->throwOnFailure = is_null($throwOnFailure)
            ? true
            : $throwOnFailure;

        return $this;
    }

    /**
     * Normalize output.
     *
     * @param  mixed  $output
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
     * @param  string  $command
     * @param  string|null  $path
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
            ? SymfonyProcess::fromShellCommandline($command, $path ?? $this->basePath, $this->env)
            : new SymfonyProcess($command, $path ?? $this->basePath, $this->env);

        $process->setTimeout(null);

        return $process;
    }

    /**
     * Throw exception.
     *
     * @param  string  $output
     *
     * @throws ProcessException
     */
    protected function throwException(string $output)
    {
        throw new ProcessException($output);
    }

    /**
     * Reset output.
     */
    private function resetOutput()
    {
        $this->output = null;
        $this->errorOutput = [];
    }

    /**
     * Get process base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        return preg_replace('/(.*)\/$/', '$1', $this->basePath);
    }

    /**
     * Clone process from parent relative to base path.
     *
     * @return Process
     */
    public function fromParent()
    {
        $that = clone $this;

        $that->basePath = str_finish(Path::resolve($this->basePath.'/../'), '/');

        return $that;
    }
}
