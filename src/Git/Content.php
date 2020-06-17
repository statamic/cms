<?php

namespace Statamic\Git;

use Statamic\Console\Processes\Git;
use Statamic\Facades\Antlers;
use Statamic\Facades\Path;
use Statamic\Facades\User;
use Statamic\Support\Str;

class Content
{
    /**
     * Instantiate git tracked content manager.
     */
    public function __construct()
    {
        if (! config('statamic.git.enabled')) {
            throw new \Exception('Statamic git integration is currently disabled.');
        }
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
                return Git::create($gitRoot)
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
    public function commit($message = 'Content saved.')
    {
        $this->groupTrackedContentPathsByRepo()->each(function ($paths, $gitRoot) use ($message) {
            $this->runConfiguredCommands($gitRoot, $paths, $message);
        });
    }

    /**
     * Git push all tracked content.
     */
    public function push()
    {
        $this->groupTrackedContentPathsByRepo()->each(function ($paths, $gitRoot) use ($message) {
            Git::create($gitRoot)->push();
        });
    }

    /**
     * Get git user name.
     *
     * @return string
     */
    public function gitUserName()
    {
        $default = config('statamic.git.user.name');

        if (! config('statamic.git.use_authenticated')) {
            return $default;
        }

        $currentUser = User::current();

        return $currentUser ? $currentUser->name() : $default;
    }

    /**
     * Get git user email.
     *
     * @return string
     */
    public function gitUserEmail()
    {
        $default = config('statamic.git.user.email');

        if (! config('statamic.git.use_authenticated')) {
            return $default;
        }

        $currentUser = User::current();

        return $currentUser ? $currentUser->email() : $default;
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
            ->groupBy(function ($path) {
                return Git::create($path)->root();
            });
    }

    /**
     * Merge status string with calculated file count stats.
     *
     * @param string $status
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
     * @param string $path
     * @return string
     */
    protected function ensureAbsolutePath($path)
    {
        $absolute = Str::startsWith($path, '/')
            ? $path
            : base_path($path);

        return Path::resolve($absolute);
    }

    /**
     * Run configured commands.
     *
     * @param mixed $gitRoot
     * @param mixed $paths
     * @param mixed $message
     */
    protected function runConfiguredCommands($gitRoot, $paths, $message)
    {
        $this->getParsedCommands($paths, $message)->each(function ($command) use ($gitRoot) {
            Git::create($gitRoot)->run($command);
        });

        if (config('statamic.git.push')) {
            $this->push();
        }
    }

    /**
     * Get parsed commands.
     *
     * @param mixed $paths
     * @param mixed $message
     */
    protected function getParsedCommands($paths, $message)
    {
        $context = $this->getCommandContext($paths, $message);

        return collect(config('statamic.git.commands'))->map(function ($command) use ($context) {
            return Antlers::parse($command, $context);
        });
    }

    /**
     * Get command context.
     *
     * @param array $paths
     * @param string $message
     * @return array
     */
    protected function getCommandContext($paths, $message)
    {
        return [
            'paths' => collect($paths)->implode(' '),
            'message' => $message,
            'name' => $this->gitUserName(),
            'email' => $this->gitUserEmail(),
        ];
    }
}
