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
        $response = (new Client($this->clientConfig))->send($this->request);

        if ($response->hasHeader('X-Statamic-Pagination')) {
            [$currentPage, $totalPages, $pageName] = $response->getHeader('X-Statamic-Pagination');

            collect(range($currentPage, $totalPages))
                ->map(function (int $page) use ($pageName) {
                    return "{$this->request->getUri()}?{$pageName}={$page}";
                })
                ->each(function (string $uri) {
                    StaticWarmJob::dispatch(new Request('GET', $uri));
                });
        }
    }
}
