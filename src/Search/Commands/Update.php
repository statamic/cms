<?php

namespace Statamic\Search\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Search;
use Statamic\Support\Str;

class Update extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:search:update
        { index? : The handle of the index to update. }
        { --all : Update all indexes. }';

    protected $description = 'Update a search index';

    private $indexes;

    public function handle()
    {
        foreach ($this->getIndexes() as $index) {
            $index->update();
            $this->info("Index <comment>{$index->name()}</comment> updated.");
        }
    }

    private function getIndexes()
    {
        if ($requestedIndex = $this->getRequestedIndex()) {
            return $requestedIndex;
        }

        if ($this->option('all')) {
            return $this->indexes();
        }

        $selection = $this->choice(
            'Select an index to update',
            collect(['all'])->merge($this->indexes()->keys())->all(),
            0
        );

        return ($selection == 'all') ? $this->indexes() : [$this->indexes()->get($selection)];
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

        // They might have entered a name as it appears in the config, but if it
        // should be localized we'll get all of the localized versions.
        if (collect(config('statamic.search.indexes'))->has($arg)) {
            return $this->indexes()->filter(fn ($index) => Str::startsWith($index->name(), $arg))->all();
        }

        throw new \InvalidArgumentException("Index [$arg] does not exist.");
    }
}
