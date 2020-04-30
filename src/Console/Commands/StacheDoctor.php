<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\AggregateStore;

class StacheDoctor extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:doctor';
    protected $description = 'Diagnose any problems with the Stache.';

    public function handle()
    {
        $missing = Stache::stores()->flatMap(function ($store) {
            return $store instanceof AggregateStore ? $store->discoverStores() : [$store];
        })->mapWithKeys(function ($store) {
            return [$store->key() => $this->getUnconfiguredIndexes($store)];
        })->filter(function ($item) {
            return ! $item->isEmpty();
        });

        if ($missing->isEmpty()) {
            $this->info('[✓] No unconfigured indexes.');
            $this->line('Indexes are created on demand through regular site usage.');
            $this->line('You could consider trying again after browsing your site.');
        }

        $missing->each(function ($item, $key) {
            $this->line("<fg=red>[✗]</> Unconfigured indexes in <comment>{$key}</comment>");
            $item->each(function ($item) {
                $this->line('- '.$item);
            });
            $this->line('');
        });
    }

    protected function getUnconfiguredIndexes($store)
    {
        $allIndexes = $store->resolveIndexes(true);
        $configuredIndexes = $store->resolveIndexes(false);

        return $allIndexes->reject(function ($index) use ($configuredIndexes) {
            return $configuredIndexes->has($index->name());
        })->map->name()->values();
    }
}
