<?php

namespace Statamic\Search\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Events\SearchIndexUpdated;
use Statamic\Facades\Search;

use function Laravel\Prompts\select;

class Update extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:search:update
        { index? : The handle of the index to update. }
        { --all : Update all indexes. }
        { --sync : Index the documents immediately instead of queueing them. }';

    protected $description = 'Update a search index';

    private $indexes;

    public function handle()
    {
        foreach ($this->getIndexes() as $index) {
            if ($this->option('sync')) {
                $index->withoutQueue();
            }

            $index->update();

            SearchIndexUpdated::dispatch($index);

            $this->components->info("Index <comment>{$index->name()}</comment> updated.");
        }
    }

    private function getIndexes()
    {
        if (! is_null($requestedIndex = $this->getRequestedIndex())) {
            return $requestedIndex;
        }

        if ($this->option('all')) {
            return $this->indexes();
        }

        if ($this->indexes()->count() === 1) {
            return $this->indexes();
        }

        $selection = select(
            label: 'Which search index would you like to update?',
            options: collect(['All'])->merge($this->indexes()->keys())->all(),
            default: 'All'
        );

        return ($selection == 'All') ? $this->indexes() : [$this->indexes()->get($selection)];
    }

    private function indexes()
    {
        return $this->indexes = $this->indexes ?? Search::indexes();
    }

    private function getRequestedIndex()
    {
        if (! $arg = $this->argument('index')) {
            return;
        }

        if ($this->indexes()->has($arg)) {
            return [$this->indexes()->get($arg)];
        }

        // They might have entered a handle as it appears in the config, but if it
        // should be localized we'll get all of the localized versions.
        if ($indexes = $this->indexes()->filter(fn ($index) => $index->handle() === $arg)->all()) {
            return $indexes;
        }

        throw new \InvalidArgumentException("Index [$arg] does not exist.");
    }
}
