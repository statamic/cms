<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\Commands\Concerns\HasStacheExcludes;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;
use Statamic\Git\Git;

use function Laravel\Prompts\spin;

class StacheRefresh extends Command
{
    use HasStacheExcludes, RunsInPlease;

    protected $signature = 'statamic:stache:refresh
        {--exclude= : Comma-separated list of store keys to exclude}
        {--git : Perform a targeted, git-diff-based cache refresh instead of a full clear and warm}
        {--include-dirty : Also process staged and unstaged dirty files (requires --git)}';

    protected $description = 'Clear and rebuild the "Stache" cache';

    public function handle()
    {
        $this->addExcludes($this->option('exclude'));

        if ($this->option('git')) {
            return $this->handleGitRefresh();
        }

        spin(callback: fn () => Stache::clear(), message: 'Clearing the Stache...');
        spin(callback: fn () => Stache::warm(), message: 'Warming the Stache...');

        $this->components->info('You have trimmed and polished the Stache. It is handsome, warm, and ready.');
    }

    protected function handleGitRefresh(): int
    {
        $git = app(Git::class);

        if (! $git->isRepo()) {
            $this->components->error('Not a git repository. Cannot use --git flag.');

            return self::FAILURE;
        }

        // First run: no ref file → full refresh, bootstrap.
        if ($git->getStacheRef() === null) {
            $this->components->warn('No stache git ref found. Performing full refresh to bootstrap.');

            spin(callback: fn () => Stache::clear(), message: 'Clearing the Stache...');
            spin(callback: fn () => Stache::warm(), message: 'Warming the Stache...');

            $git->setStacheRef($git->currentSha());

            $this->components->info('Stache bootstrapped from HEAD. Future --git runs will be targeted.');

            return self::SUCCESS;
        }

        $includeDirty = (bool) $this->option('include-dirty');

        try {
            $actions = $git->stacheDiff($includeDirty);
        } catch (ProcessException $e) {
            $this->components->error('Unable to diff git changes. Stache ref was not updated.');
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($actions->isEmpty()) {
            $this->components->info('No changes detected since last stache refresh.');
            $git->setStacheRef($git->currentSha());

            return self::SUCCESS;
        }

        if ($actions->contains(fn ($a) => $a['type'] === 'full-refresh')) {
            if ($this->getOutput()->isVerbose()) {
                $this->components->warn('A change requires a full stache refresh.');
                $this->output->listing(
                    $actions->filter(fn ($a) => $a['type'] === 'full-refresh')->pluck('displayPath')->all()
                );
            }

            spin(callback: fn () => Stache::clear(), message: 'Clearing the Stache...');
            spin(callback: fn () => Stache::warm(), message: 'Warming the Stache...');

            $git->setStacheRef($git->currentSha());
            $this->components->info('You have trimmed and polished the Stache. It is handsome, warm, and ready.');

            return self::SUCCESS;
        }

        $this->executeActions($actions);

        $git->setStacheRef($git->currentSha());

        if ($this->getOutput()->isVerbose()) {
            $this->outputVerboseTable($actions);
        }

        $this->components->info('The Stache has been selectively groomed. Targeted and precise.');

        return self::SUCCESS;
    }

    protected function executeActions($actions): void
    {
        $actions
            ->filter(fn ($a) => in_array($a['type'], ['update-item', 'forget-item']))
            ->each(function ($action) {
                $store = Stache::store($action['storeKey']);

                if (! $store) {
                    return;
                }

                if ($action['type'] === 'update-item') {
                    spin(
                        callback: fn () => $store->updateItemFromPath($action['absolutePath']),
                        message: 'Updating '.$action['displayPath'].'...'
                    );
                } else {
                    spin(
                        callback: fn () => $store->forgetItemByPath($action['absolutePath']),
                        message: 'Removing '.$action['displayPath'].'...'
                    );
                }
            });

        $actions
            ->filter(fn ($a) => $a['type'] === 'warm-store')
            ->pluck('storeKey')
            ->unique()
            ->each(function ($storeKey) {
                spin(
                    callback: fn () => Stache::store($storeKey)?->warm(),
                    message: 'Warming '.$storeKey.'...'
                );
            });
    }

    protected function outputVerboseTable($actions): void
    {
        $this->table(
            ['Path', 'Store', 'Action'],
            $actions->map(fn ($a) => [$a['displayPath'], $a['storeKey'] ?? '-', $a['type']])->all()
        );
    }
}
