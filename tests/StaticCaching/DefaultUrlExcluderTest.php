<?php

namespace Tests\StaticCaching;

use Mockery;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultUrlExcluder;

class DefaultUrlExcluderTest extends \PHPUnit\Framework\TestCase
{
    private function excluder(array $urls, string $baseUrl = 'http://localhost')
    {
        $cacher = Mockery::mock(Cacher::class);
        $cacher->shouldReceive('getBaseUrl')->andReturn($baseUrl);

        return new DefaultUrlExcluder($cacher, $urls);
    }

    /** @test */
    public function excludes_urls()
    {
        $cacher = $this->excluder(['/blog']);

        $this->assertTrue($cacher->isExcluded('/blog'));
        $this->assertFalse($cacher->isExcluded('/blog/post'));
    }

    /** @test */
    public function excludes_wildcard_urls()
    {
        $cacher = $this->excluder([
            '/blog/*', // The slash indicates "only child pages"
            '/news*',   // No slash would get the "news" page, child pages, and any page with the substring.
        ]);

        $this->assertTrue($cacher->isExcluded('/blog/post'));
        $this->assertFalse($cacher->isExcluded('/blog'));

        $this->assertTrue($cacher->isExcluded('/news'));
        $this->assertTrue($cacher->isExcluded('/news/article'));
        $this->assertTrue($cacher->isExcluded('/newspaper'));
    }

    /** @test */
    public function url_exclusions_ignore_query_strings()
    {
        $cacher = $this->excluder(['/blog']);

        $this->assertTrue($cacher->isExcluded('/blog?page=1'));
    }

    /** @test */
    public function url_exclusions_trim_the_base_url()
    {
        $cacher = $this->excluder(['/blog'], 'http://example.com');

        $this->assertTrue($cacher->isExcluded('http://example.com/blog'));
    }
}
