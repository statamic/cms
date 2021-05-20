<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class StaticWarm extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:static:warm';
    protected $description = "Warms the static cache by visiting all entry URL's.";

    public function handle()
    {
        $this->info('Warming the static cache.');

        Http::pool(fn (Pool $pool) => Entry::query()
            ->where('status', 'published')
            ->get()
            ->map->absoluteUrl()
            ->unique()
            ->map(fn (string $url) => $pool->get($url)));
    }
}
