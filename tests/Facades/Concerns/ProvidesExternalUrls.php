<?php

namespace Tests\Facades\Concerns;

trait ProvidesExternalUrls
{
    public static function externalUrlProvider()
    {
        return [
            ['http://this-site.com', false],
            ['http://this-site.com?foo', false],
            ['http://this-site.com#anchor', false],
            ['http://this-site.com/', false],
            ['http://this-site.com/?foo', false],
            ['http://this-site.com/#anchor', false],

            ['http://that-site.com', true],
            ['http://that-site.com/', true],
            ['http://that-site.com/?foo', true],
            ['http://that-site.com/#anchor', true],
            ['http://that-site.com/some-slug', true],
            ['http://that-site.com/some-slug?foo', true],
            ['http://that-site.com/some-slug#anchor', true],

            ['http://subdomain.this-site.com', false],
            ['http://subdomain.this-site.com/', false],
            ['http://subdomain.this-site.com/?foo', false],
            ['http://subdomain.this-site.com/#anchor', false],
            ['http://subdomain.this-site.com/some-slug', false],
            ['http://subdomain.this-site.com/some-slug?foo', false],
            ['http://subdomain.this-site.com/some-slug#anchor', false],

            ['http://absolute-url-resolved-from-request.com', false],
            ['http://absolute-url-resolved-from-request.com/', false],
            ['http://absolute-url-resolved-from-request.com/?foo', false],
            ['http://absolute-url-resolved-from-request.com/?anchor', false],
            ['http://absolute-url-resolved-from-request.com/some-slug', false],
            ['http://absolute-url-resolved-from-request.com/some-slug?foo', false],
            ['http://absolute-url-resolved-from-request.com/some-slug#anchor', false],
            ['/', false],
            ['/?foo', false],
            ['/#anchor', false],
            ['/some-slug', false],
            ['?foo', false],
            ['#anchor', false],
            ['', false],
            [null, false],

            // Protocol-relative URLs are external
            ['//evil.com', true],
            ['//evil.com/', true],
            ['//evil.com/path', true],
            ['//this-site.com', true],

            // External domain that starts with a valid domain.
            ['http://this-site.com.au', true],
            ['http://this-site.com.au/', true],
            ['http://this-site.com.au/?foo', true],
            ['http://this-site.com.au/#anchor', true],
            ['http://this-site.com.au/some-slug', true],
            ['http://this-site.com.au/some-slug?foo', true],
            ['http://this-site.com.au/some-slug#anchor', true],
            ['http://subdomain.this-site.com.au', true],
            ['http://subdomain.this-site.com.au/', true],
            ['http://subdomain.this-site.com.au/?foo', true],
            ['http://subdomain.this-site.com.au/#anchor', true],
            ['http://subdomain.this-site.com.au/some-slug', true],
            ['http://subdomain.this-site.com.au/some-slug?foo', true],
            ['http://subdomain.this-site.com.au/some-slug#anchor', true],

            // Credential injection
            ['http://this-site.com@evil.com', true],
            ['http://this-site.com@evil.com/', true],
            ['http://this-site.com@evil.com/path', true],
            ['http://this-site.com@evil.com/path?query', true],
            ['http://this-site.com:password@evil.com', true],
            ['http://user:pass@evil.com', true],
            ['http://absolute-url-resolved-from-request.com@evil.com', true],
            ['http://absolute-url-resolved-from-request.com@evil.com/path', true],
            ['http://subdomain.this-site.com@evil.com', true],
            ['http://subdomain.this-site.com@evil.com/path', true],
            ['http://this-site.com:8000@evil.com', true],
            ['http://this-site.com:8000@evil.com/path', true],
            ['http://this-site.com:8000@webhook.site/token', true],
        ];
    }
}
