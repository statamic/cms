<?php

namespace Statamic\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\StaticCaching\Cacher;

class StaticRecacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public Request $request;

    public $tries = 1;

    public function __construct($url, $domain)
    {
        $domain ??= app(Cacher::class)->getBaseUrl();

        $url = $domain.$url;

        $param = '__recache='.config('statamic.static_caching.background_recache_token');

        $url .= (str_contains($url, '?') ? '&' : '?').$param;

        $this->request = new Request('GET', $url);
    }

    public function handle(Client $client)
    {
        $client->send($this->request);
    }
}
