<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\RemoveRecacheToken;
use Tests\TestCase;

class RemoveRecacheTokenTest extends TestCase
{
    #[Test, DataProvider('urlProvider')]
    public function it_removes_recache_token($url, $expected)
    {
        $this->assertSame($expected, (new RemoveRecacheToken())($url));
    }

    public static function urlProvider()
    {
        return [
            // Root without trailing slash
            ['http://example.com', 'http://example.com'],
            ['http://example.com?__recache=test-token', 'http://example.com'],
            ['http://example.com?__recache=test-token&foo=bar', 'http://example.com?foo=bar'],
            ['http://example.com?__recache=test-token&foo=bar&bar=baz', 'http://example.com?foo=bar&bar=baz'],
            ['http://example.com?foo=bar&__recache=test-token', 'http://example.com?foo=bar'],
            ['http://example.com?foo=bar&__recache=test-token&bar=baz', 'http://example.com?foo=bar&bar=baz'],

            // Root with trailing slash
            ['http://example.com/', 'http://example.com/'],
            ['http://example.com/?__recache=test-token', 'http://example.com/'],
            ['http://example.com/?__recache=test-token&foo=bar', 'http://example.com/?foo=bar'],
            ['http://example.com/?__recache=test-token&foo=bar&bar=baz', 'http://example.com/?foo=bar&bar=baz'],
            ['http://example.com/?foo=bar&__recache=test-token', 'http://example.com/?foo=bar'],
            ['http://example.com/?foo=bar&__recache=test-token&bar=baz', 'http://example.com/?foo=bar&bar=baz'],

            // Sub-page without trailing slash
            ['http://example.com/page', 'http://example.com/page'],
            ['http://example.com/page?__recache=test-token', 'http://example.com/page'],
            ['http://example.com/page?__recache=test-token&foo=bar', 'http://example.com/page?foo=bar'],
            ['http://example.com/page?__recache=test-token&foo=bar&bar=baz', 'http://example.com/page?foo=bar&bar=baz'],
            ['http://example.com/page?foo=bar&__recache=test-token', 'http://example.com/page?foo=bar'],
            ['http://example.com/page?foo=bar&__recache=test-token&bar=baz', 'http://example.com/page?foo=bar&bar=baz'],

            // Sub-page with trailing slash
            ['http://example.com/page/', 'http://example.com/page/'],
            ['http://example.com/page/?__recache=test-token', 'http://example.com/page/'],
            ['http://example.com/page/?__recache=test-token&foo=bar', 'http://example.com/page/?foo=bar'],
            ['http://example.com/page/?__recache=test-token&foo=bar&bar=baz', 'http://example.com/page/?foo=bar&bar=baz'],
            ['http://example.com/page/?foo=bar&__recache=test-token', 'http://example.com/page/?foo=bar'],
            ['http://example.com/page/?foo=bar&__recache=test-token&bar=baz', 'http://example.com/page/?foo=bar&bar=baz'],
        ];
    }
}
