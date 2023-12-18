<?php

namespace Statamic\Git;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Git as GitProcess;
use Statamic\Facades\Antlers;
use Statamic\Facades\Path;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Git
{
    /**
     * Instantiate git tracked content manager.
     */
    public function __construct()
    {
        if (! config('statamic.git.enabled')) {
            throw new \Exception(__('statamic::messages.git_disabled'));
        }
    }

    /**
     * Listen to custom addon event.
     *
     * @param  string  $event
     */
    public function listen($event)
    {
        \Illuminate\Support\Facades\Event::listen($event, Subscriber::class.'@commit');
    }

    /**
     * Get statuses of tracked content paths.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function statuses()
    {
        $statuses = $this
            ->groupTrackedContentPathsByRepo()
            ->map(function ($paths, $gitRoot) {
                return GitProcess::create($gitRoot)
                    ->colorized(true) // TODO: Why is it not colorizing?
                    ->status($paths);
            })
            ->map(function ($status) {
                return (object) $this->statusWithFileCounts($status);
            })
            ->filter
            ->totalCount;

        return $statuses->isNotEmpty() ? $statuses : null;
    }

    /**
     * Git add and commit all tracked content, using configured commands.
     */
    public function commit($message = null, $committer = null)
    {
        $this->groupTrackedContentPathsByRepo()->each(function ($paths, $gitRoot) use ($message, $committer) {
            $this->runConfiguredCommands($gitRoot, $paths, $message ?? __('Content saved'), $committer);
        });
    }

    /**
     * Dispatch commit job to queue.
     */
    public function dispatchCommit($message = null, $committer = null)
    {
        if ($delay = config('statamic.git.dispatch_delay')) {
            $delayInMinutes = now()->addMinutes($delay);
            $message = null;
        }

        CommitJob::dispatch($message, $committer)
            ->onConnection(config('statamic.git.queue_connection'))
            ->delay($delayInMinutes ?? null);
    }

    /**
     * Get git user name.
     *
     * @param  \Statamic\Contracts\Auth\User|null  $committer
     * @return string
     */
    public function gitUserName($committer = null)
    {
        $default = config('statamic.git.user.name');

        if (! config('statamic.git.use_authenticated')) {
            return $default;
        }

        return $committer?->name() ?? $default;
    }

    /**
     * Get git user email.
     *
     * @param  \Statamic\Contracts\Auth\User|null  $committer
     * @return string
     */
    public function gitUserEmail($committer = null)
    {
        $default = config('statamic.git.user.email');

        if (! config('statamic.git.use_authenticated')) {
            return $default;
        }

        return $committer?->email() ?? $default;
    }

    /**
     * Group tracked content paths by repo.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function groupTrackedContentPathsByRepo()
    {
        return collect(config('statamic.git.paths'))
            ->map(function ($path) {
                return $this->ensureAbsolutePath($path);
            })
            ->filter(function ($path) {
                return app(Filesystem::class)->exists($path);
            })
            ->filter(function ($path) {
                return $this->gitProcessForPath($path)->isRepo();
            })
            ->filter(function ($path) {
                return $this->gitProcessForPath($path)->status();
            })
            ->groupBy(function ($path) {
                return $this->gitProcessForPath($path)->root();
            });
    }

    /**
     * Get git process for content path.
     *
     * @param  string  $path
     * @return GitProcess
     */
    protected function gitProcessForPath($path)
    {
        return is_link($path) || is_file($path)
            ? GitProcess::create($path)->fromParent()
            : GitProcess::create($path);
    }

    /**
     * Merge status string with calculated file count stats.
     *
     * @param  string  $status
     * @return array
     */
    protected function statusWithFileCounts($status)
    {
        $lines = collect(explode("\n", $status))->filter();

        $totalCount = $lines->count();

        $addedCount = $lines->filter(function ($line) {
            return Str::startsWith($line, ['A ', ' A', '??']);
        })->count();

        $modifiedCount = $lines->filter(function ($line) {
            return Str::startsWith($line, ['M ', ' M']);
        })->count();

        $deletedCount = $lines->filter(function ($line) {
            return Str::startsWith($line, ['D ', ' D']);
        })->count();

        return compact('status', 'totalCount', 'addedCount', 'modifiedCount', 'deletedCount');
    }

    /**
     * Ensure absolute path.
     *
     * @param  string  $path
     * @return string
     */
    protected function ensureAbsolutePath($path)
    {
        $absolute = Path::isAbsolute(Path::tidy($path))
            ? $path
            : base_path($path);

        return Path::resolve($absolute);
    }

    /**
     * Run configured commands.
     *
     * @param  mixed  $gitRoot
     * @param  mixed  $paths
     * @param  mixed  $message
     */
    protected function runConfiguredCommands($gitRoot, $paths, $message, $committer = null)
    {
        $this->getParsedCommands($paths, $message, $committer)->each(function ($command) use ($gitRoot) {
            GitProcess::create($gitRoot)->run($command);
        });

        if (config('statamic.git.push')) {
            $this->push($gitRoot);
        }
    }

    /**
     * Get parsed commands.
     *
     * @param  mixed  $paths
     * @param  mixed  $message
     */
    protected function getParsedCommands($paths, $message, $committer = null)
    {
        $context = $this->getCommandContext($paths, $message, $committer);

        return collect(config('statamic.git.commands'))->map(function ($command) use ($context) {
            return Antlers::parse($command, $context);
        });
    }

    /**
     * Get command context.
     *
     * @param  array  $paths
     * @param  string  $message
     * @return array
     */
    protected function getCommandContext($paths, $message, $committer = null)
    {
        return [
            'paths' => collect($paths)->implode(' '),
            'message' => $this->shellEscape($message),
            'name' => $this->shellEscape($this->gitUserName($committer)),
            'email' => $this->shellEscape($this->gitUserEmail($committer)),
        ];
    }

    /**
     * Git push tracked content for a specific repo.
     */
    protected function push($gitRoot)
    {
        GitProcess::create($gitRoot)->push();
    }

    /**
     * Shell escape string for use in git commands.
     *
     * @return string
     */
    protected function shellEscape(string $string)
    {
        $string = str_replace('"', '', $string);
        $string = str_replace("'", '', $string);

        return escapeshellcmd($string);
    }
}
