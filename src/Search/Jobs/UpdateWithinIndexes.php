<?php

namespace Statamic\Search\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Search;

/**
 * Updates a single searchable within all indexes.
 */
class UpdateWithinIndexes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Searchable $searchable
    ) {
    }

    public function handle(Search $search): void
    {
        $search->indexes()->each(function ($index) {
            $shouldIndex = $index->shouldIndex($this->searchable);
            $exists = $index->exists();

            if ($shouldIndex && $exists) {
                $index->insert($this->searchable);
            } elseif ($shouldIndex && ! $exists) {
                $index->update();
            } elseif ($exists) {
                $index->delete($this->searchable);
            }
        });
    }
}
