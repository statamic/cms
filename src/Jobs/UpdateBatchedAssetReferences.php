<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Listeners\UpdateAssetReferences;

class UpdateBatchedAssetReferences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  string  $container  The handle of the container the assets are in.
     * @param  array<int, array{0: string, 1: string|null}>  $moves  Original path and new path (null when deleted), in the order they happened.
     */
    public function __construct(public string $container, public array $moves)
    {
    }

    public function handle()
    {
        app(UpdateAssetReferences::class)->handleBatch($this->container, $this->moves);
    }
}
