<?php

namespace Statamic\Console\Commands\Concerns;

use Psr\Http\Message\ResponseInterface;

trait NormalizesPaginationHeader
{
    /**
     * @return array{0: int, 1: int, 2: string}
     */
    protected function paginationHeader(ResponseInterface $response): array
    {
        // A proxy or CDN may fold the repeated X-Statamic-Pagination header into a single
        // comma-joined line, so we normalize both framings before destructuring. The limit
        // keeps a page name that itself contains a comma intact.
        [$current, $total, $name] = array_map(
            'trim',
            explode(',', implode(',', $response->getHeader('X-Statamic-Pagination')), 3)
        );

        return [(int) $current, (int) $total, $name];
    }
}
