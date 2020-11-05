<?php

namespace Statamic\Search\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Search;

class Update extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:search:update
        { index? : The handle of the index to update. }
        { --all : Update all indexes. }';

    protected $description = 'Update a search index';

    public function handle()
    {
        foreach ($this->getIndexes() as $index) {
            Search::in($index)->update();
            $this->info("Index <comment>{$index}</comment> updated.");
        }
    }

    private function getIndexes()
    {
        if ($index = $this->argument('index')) {
            if (! $this->indexExists($index)) {
                throw new \InvalidArgumentException("Index [$index] does not exist.");
            }

            return [$index];
        }

        if ($this->option('all')) {
            return $this->indexes();
        }

        $selection = $this->choice(
            'Select an index to update',
            collect(['all'])->merge($this->indexes())->all(),
            0
        );

        return ($selection == 'all') ? $this->indexes() : [$selection];
    }

    private function indexes()
    {
        return Search::indexes()->keys();
    }

    private function indexExists($index)
    {
        return $this->indexes()->contains($index);
    }
}
