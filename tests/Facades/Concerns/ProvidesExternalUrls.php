<?php

namespace Tests\Facades\Concerns;

trait ProvidesExternalUrls
{
    private static function internalUrls()
    {
        return [
            'http://this-site.com',
            'http://this-site.com?foo',
            'http://this-site.com#anchor',
            'http://this-site.com/',
            'http://this-site.com/?foo',
            'http://this-site.com/#anchor',

            'http://subdomain.this-site.com',
            'http://subdomain.this-site.com/',
            'http://subdomain.this-site.com/?foo',
            'http://subdomain.this-site.com/#anchor',
            'http://subdomain.this-site.com/some-slug',
            'http://subdomain.this-site.com/some-slug?foo',
            'http://subdomain.this-site.com/some-slug#anchor',

            'http://absolute-url-resolved-from-request.com',
            'http://absolute-url-resolved-from-request.com/',
            'http://absolute-url-resolved-from-request.com/?foo',
            'http://absolute-url-resolved-from-request.com/?anchor',
            'http://absolute-url-resolved-from-request.com/some-slug',
            'http://absolute-url-resolved-from-request.com/some-slug?foo',
            'http://absolute-url-resolved-from-request.com/some-slug#anchor',

            '/',
            '/?foo',
            '/#anchor',
            '/some-slug',
            '?foo',
            '#anchor',
            '',
            null,
        ];
    }

    private static function externalUrls()
    {
        return [
            'http://that-site.com',
            'http://that-site.com/',
            'http://that-site.com/?foo',
            'http://that-site.com/#anchor',
            'http://that-site.com/some-slug',
            'http://that-site.com/some-slug?foo',
            'http://that-site.com/some-slug#anchor',

            // Protocol-relative URLs are external
            '//evil.com',
            '//evil.com/',
            '//evil.com/path',
            '//this-site.com',

            // External domain that starts with a valid domain.
            'http://this-site.com.au',
            'http://this-site.com.au/',
            'http://this-site.com.au/?foo',
            'http://this-site.com.au/#anchor',
            'http://this-site.com.au/some-slug',
            'http://this-site.com.au/some-slug?foo',
            'http://this-site.com.au/some-slug#anchor',
            'http://subdomain.this-site.com.au',
            'http://subdomain.this-site.com.au/',
            'http://subdomain.this-site.com.au/?foo',
            'http://subdomain.this-site.com.au/#anchor',
            'http://subdomain.this-site.com.au/some-slug',
            'http://subdomain.this-site.com.au/some-slug?foo',
            'http://subdomain.this-site.com.au/some-slug#anchor',

            // Credential injection
            'http://this-site.com@evil.com',
            'http://this-site.com@evil.com/',
            'http://this-site.com@evil.com/path',
            'http://this-site.com@evil.com/path?query',
            'http://this-site.com:password@evil.com',
            'http://user:pass@evil.com',
            'http://absolute-url-resolved-from-request.com@evil.com',
            'http://absolute-url-resolved-from-request.com@evil.com/path',
            'http://subdomain.this-site.com@evil.com',
            'http://subdomain.this-site.com@evil.com/path',
            'http://this-site.com:8000@evil.com',
            'http://this-site.com:8000@evil.com/path',
            'http://this-site.com:8000@webhook.site/token',

            // Backslash bypass
            'http://evil.com\@this-site.com',
            'http://evil.com\@this-site.com/',
            'http://evil.com\@this-site.com/path',
            'http://evil.com\@subdomain.this-site.com',
            'http://evil.com\@absolute-url-resolved-from-request.com',
            'https://evil.com\@this-site.com',
            'http://evil.com\\@this-site.com',
            'http://evil.com\\\@this-site.com',

            // Percent-encoded backslash bypass
            'http://evil.com%5c@this-site.com',
            'http://evil.com%5c@this-site.com/',
            'http://evil.com%5c@this-site.com/path',
            'http://evil.com%5c@subdomain.this-site.com',
            'http://evil.com%5c@absolute-url-resolved-from-request.com',
            'https://evil.com%5C@this-site.com',

            // Absolute-looking URL with no host (parse_url() returns false)
            'http:///path',

            // Percent-encoded whitespace bypass
            '%20http://evil.com',
            '%09http://evil.com',
            '%0ahttp://evil.com',

            // Dangerous URL schemes
            'javascript:alert(1)',
            'javascript:alert(document.cookie)',
            'javascript://this-site.com/%0aalert(1)',
            'JAVASCRIPT:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
            'DATA:text/html,test',
            'vbscript:msgbox(1)',
            'file:///etc/passwd',

            // Whitespace bypass
            ' http://this-site.com',
            ' http://evil.com',
            '  http://evil.com',
            "\thttp://evil.com",
            "\nhttp://evil.com",
            "\rhttp://evil.com",
            "\r\nhttp://evil.com",
        ];
    }

    public static function externalUrlProvider()
    {
        $keyFn = function ($key) {
            return is_null($key) ? 'null' : $key;
        };

        return [
            ...collect(static::internalUrls())->mapWithKeys(fn ($url) => [$keyFn($url) => [$url, false]])->all(),
            ...collect(static::externalUrls())->mapWithKeys(fn ($url) => [$keyFn($url) => [$url, true]])->all(),
        ];
    }
}
