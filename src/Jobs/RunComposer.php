<?php

namespace Statamic\Jobs;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class RunComposer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Command params.
     *
     * @var string|array
     */
    public $params;

    /**
     * Cache key.
     *
     * @var string
     */
    public $cacheKey;

    /**
     * Create a new job instance.
     *
     * @param  string  $cacheKey
     * @param  string|array  $params
     * @return void
     */
    public function __construct($params, string $cacheKey)
    {
        $this->params = $params;
        $this->cacheKey = $cacheKey;
        if ($queue = config('statamic.system.queue')) {
            $this->onQueue($queue);
        }
        if ($connection = config('statamic.system.queue_connection')) {
            $this->onConnection($connection);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Composer::run($this->params, $this->cacheKey);
    }
}
