<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Http\Message\ResponseInterface;

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

        if ($this->shouldWarmPaginatedPages($response)) {
            [$currentPage, $totalPages, $pageName] = $response->getHeader('X-Statamic-Pagination');

            collect(range($currentPage, $totalPages))
                ->map(function (int $page) use ($pageName): string {
                    $url = $this->request->getUri();

                    return implode('', [
                        $url,
                        str_contains($url, '?') ? '&' : '?',
                        "{$pageName}={$page}",
                    ]);
                })
                ->each(fn (string $uri) => StaticWarmJob::dispatch(
                    new Request('GET', $uri),
                    $this->clientConfig
                ));
        }
    }

    private function shouldWarmPaginatedPages(ResponseInterface $response): bool
    {
        if (! $response->hasHeader('X-Statamic-Pagination')) {
            return false;
        }

        [$currentPage, $totalPages, $pageName] = $response->getHeader('X-Statamic-Pagination');

        return ! str_contains($this->request->getUri()->getQuery(), "{$pageName}=");
    }
}
