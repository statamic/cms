<?php

namespace Tests\Extensions\Pagination;

use Illuminate\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class LengthAwarePaginatorTest extends TestCase
{
    #[Test]
    public function it_appends_the_query_string()
    {
        $this->get('/?foo=bar');

        $paginator = $this->paginator()->withQueryString();

        $this->assertEquals('/?foo=bar&page=2', $paginator->url(2));
    }

    #[Test]
    public function it_doesnt_append_the_recache_token()
    {
        $this->get('/?foo=bar&__recache=abc');

        $paginator = $this->paginator()->withQueryString();

        $this->assertEquals('/?foo=bar&page=2', $paginator->url(2));
    }

    #[Test]
    public function it_uses_the_query_string_resolver()
    {
        Paginator::queryStringResolver(fn () => ['foo' => 'bar']);

        $paginator = $this->paginator()->withQueryString();

        $this->assertEquals('/?foo=bar&page=2', $paginator->url(2));
    }

    private function paginator()
    {
        return new LengthAwarePaginator(collect(['a', 'b', 'c']), 3, 1, 1, ['path' => '/']);
    }
}
