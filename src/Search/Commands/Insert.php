<?php

namespace Statamic\Search\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Data;
use Statamic\Facades\Search;

class Insert extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:search:insert { id : The ID of the item to insert. }';
    protected $description = 'Insert an item into its search indexes';

    public function handle()
    {
        if (! $item = Data::find($id = $this->argument('id'))) {
            throw new \InvalidArgumentException("Item with id of [{$id}] doesn't exist.");
        }

        Search::indexes()
            ->filter->shouldIndex($item)
            ->each(function ($index) use ($item) {
                if ($index->exists()) {
                    $index->insert($item);
                    $this->info("Inserted into <comment>{$index->name()}</comment> index.");
                } else {
                    $index->update();
                    $this->info("Index <comment>{$index->name()}</comment> was empty, so it has been updated.");
                }
            });
    }
}
