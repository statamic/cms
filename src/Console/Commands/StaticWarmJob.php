<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StaticWarmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public Request $request;
    public array $options;

    public $tries = 1;

    public function __construct(Request $request, array $options = [])
    {
        $this->request = $request;
        $this->options = $options;
    }

    public function handle(Client $client)
    {
        $client->send($this->request, $this->options);
    }
}
