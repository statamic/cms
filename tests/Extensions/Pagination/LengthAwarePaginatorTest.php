<?php

namespace Tests\Extensions\Pagination;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class LengthAwarePaginatorTest extends TestCase
{
    public function tearDown(): void
    {
        LengthAwarePaginator::queryStringResolver(fn () => request()->query());

        parent::tearDown();
    }

    #[Test]
    public function it_excludes_the_recache_token_from_urls()
    {
        request()->query->replace([
            '__recache' => 'test-token',
            'filter' => 'news',
        ]);

        $paginator = $this->paginator()->withQueryString();

        $this->assertSame('https://example.com/resources?filter=news&page=2', $paginator->url(2));
    }

    #[Test]
    public function it_uses_the_query_string_resolver()
    {
        LengthAwarePaginator::queryStringResolver(fn () => [
            '__recache' => 'test-token',
            'filter' => 'news',
        ]);

        $paginator = $this->paginator()->withQueryString();

        $this->assertSame('https://example.com/resources?filter=news&page=2', $paginator->url(2));
    }

    private function paginator()
    {
        return new LengthAwarePaginator([], 30, 10, 1, [
            'path' => 'https://example.com/resources',
        ]);
    }
}
