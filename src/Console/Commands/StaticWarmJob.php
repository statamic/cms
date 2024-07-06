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

    public $tries = 1;
    private $id;

    public function __construct(public Request $request, public array $clientConfig)
    {
        $this->id = $request->getUri()->getHost() . $request->getUri()->getPath();
    }

    public function handle()
    {
        (new Client($this->clientConfig))->send($this->request);
    }

    public function uniqueId(): string
    {
        return $this->id;
    }
}
