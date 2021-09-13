<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Support\Str;

class StacheDoctor extends Command
{
    use RunsInPlease, EnhancesCommands;

    protected $signature = 'statamic:stache:doctor';
    protected $description = 'Diagnose any problems with the Stache';
    protected $stores;
    protected $hasDuplicateIds = false;

    public function handle()
    {
        $this->stores = Stache::stores()->flatMap(function ($store) {
            return $store instanceof AggregateStore ? $store->discoverStores() : [$store];
        });

        $this->outputDuplicateIds();
        $this->outputUnconfiguredIndexes();
    }

    protected function outputUnconfiguredIndexes()
    {
        $missing = $this->stores->mapWithKeys(function ($store) {
            return [$store->key() => $this->getUnconfiguredIndexes($store)];
        })->filter(function ($item) {
            return ! $item->isEmpty();
        });

        if ($missing->isEmpty()) {
            $this->checkLine('No unconfigured indexes.');
            $this->output->text('Indexes are created on demand through regular site usage.');
            $this->output->text('You could consider trying again after browsing your site.');

            return;
        }

        if (! $this->hasDuplicateIds) {
            $this->output->newLine();
        }

        $missing->each(function ($item, $key) {
            $this->line("<fg=red>[✗]</> Unconfigured indexes in <comment>{$key}</comment>");
            $this->output->listing($item->all());
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

    protected function outputDuplicateIds()
    {
        $this->stores->each->clearCachedPaths();

        $duplicates = Stache::duplicates()->clear()->find()->all();

        $this->hasDuplicateIds = $duplicates->isNotEmpty();

        if (! $this->hasDuplicateIds) {
            $this->checkLine('No duplicate IDs detected.');

            return;
        }

        $duplicates->flatMap(function ($duplicates) {
            return $duplicates;
        })->each(function ($paths, $id) {
            $this->line("<fg=red>[✗]</> Duplicate ID <comment>$id</comment>");

            $this->output->listing(collect($paths)->map(function ($path) {
                return Str::after($path, base_path().'/');
            })->all());
        });

        return $duplicates->isNotEmpty();
    }
}
