<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Stache;

class StacheWarmStores extends Command
{
    protected $signature = 'statamic:stache:warm-stores {--stores=}';
    protected $description = 'Warm specific stache stores (used internally for parallel processing)';

    public function handle()
    {
        $storeKeys = explode(',', $this->option('stores'));

        foreach ($storeKeys as $key) {
            if ($store = Stache::store($key)) {
                $store->warm();
            }
        }
    }
}
