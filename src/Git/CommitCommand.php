<?php

namespace Statamic\Git;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Git;

class CommitCommand extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:git:commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Git add and commit tracked content.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! config('statamic.git.enabled')) {
            return $this->info(__('Statamic git integration is currently disabled.'));
        }

        if (! Git::statuses()) {
            return $this->info(__('Nothing to commit, content paths clean!'));
        }

        Git::commit();

        return $this->info(__('Content committed.'));
    }
}
