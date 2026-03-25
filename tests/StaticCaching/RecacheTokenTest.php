<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\RecacheToken;
use Tests\TestCase;

class RecacheTokenTest extends TestCase
{
    #[Test, DataProvider('removeFromUrlProvider')]
    public function it_removes_recache_token($url, $expected)
    {
        $this->assertSame($expected, RecacheToken::removeFromUrl($url));
    }

    public static function removeFromUrlProvider()
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

    #[Test, DataProvider('addToUrlProvider')]
    public function it_adds_recache_token($url, $expected)
    {
        config(['statamic.static_caching.recache_token' => 'test-token']);

        $this->assertSame($expected, RecacheToken::addToUrl($url));
    }

    public static function addToUrlProvider()
    {
        return [
            // Root without trailing slash
            ['http://example.com', 'http://example.com?__recache=test-token'],
            ['http://example.com?foo=bar', 'http://example.com?foo=bar&__recache=test-token'],

            // Root with trailing slash
            ['http://example.com/', 'http://example.com/?__recache=test-token'],
            ['http://example.com/?foo=bar', 'http://example.com/?foo=bar&__recache=test-token'],

            // Sub-page without trailing slash
            ['http://example.com/page', 'http://example.com/page?__recache=test-token'],
            ['http://example.com/page?foo=bar', 'http://example.com/page?foo=bar&__recache=test-token'],

            // Sub-page with trailing slash
            ['http://example.com/page/', 'http://example.com/page/?__recache=test-token'],
            ['http://example.com/page/?foo=bar', 'http://example.com/page/?foo=bar&__recache=test-token'],
        ];
    }
}
