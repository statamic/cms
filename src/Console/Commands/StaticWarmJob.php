<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StaticWarmJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $uniqueId;
    public $tries = 1;

    public function __construct(public Request $request, public array $clientConfig)
    {
        $this->uniqueId = (string) $request->getUri();
    }

    public function handle()
    {
        (new Client($this->clientConfig))->send($this->request);
    }
}
