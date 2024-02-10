<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Support\Arr;

class StaticWarmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public Request $request;

    public $tries = 1;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Client $client)
    {
        $response = $client->send($this->request);

        if ($response->hasHeader('Statamic-Pagination-Next')) {
            StaticWarmJob::dispatch(new Request('GET', Arr::first($response->getHeader('Statamic-Pagination-Next'))));
        }
    }
}
