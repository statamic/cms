<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\DefaultUrlExcluder;

class DefaultUrlExcluderTest extends \PHPUnit\Framework\TestCase
{
    private function excluder(array $urls, string $baseUrl = 'http://localhost')
    {
        return new DefaultUrlExcluder($baseUrl, $urls);
    }

    #[Test]
    public function excludes_urls()
    {
        $excluder = $this->excluder(['/blog', '/events/', '/']);

        $this->assertTrue($excluder->isExcluded('/blog'));
        $this->assertTrue($excluder->isExcluded('/blog/'));
        $this->assertFalse($excluder->isExcluded('/blog/post'));
        $this->assertTrue($excluder->isExcluded('/events'));
        $this->assertTrue($excluder->isExcluded('/events/'));
        $this->assertFalse($excluder->isExcluded('/events/statameet'));
        $this->assertTrue($excluder->isExcluded('/'));
        $this->assertTrue($excluder->isExcluded(''));
    }

    #[Test]
    public function excludes_wildcard_urls()
    {
        $excluder = $this->excluder([
            '/blog/*', // The slash indicates "only child pages"
            '/news*',   // No slash would get the "news" page, child pages, and any page with the substring.
        ]);

        $this->assertTrue($excluder->isExcluded('/blog/post'));
        $this->assertTrue($excluder->isExcluded('/blog/post/'));
        $this->assertFalse($excluder->isExcluded('/blog'));
        $this->assertFalse($excluder->isExcluded('/blog/'));

        $this->assertTrue($excluder->isExcluded('/news'));
        $this->assertTrue($excluder->isExcluded('/news/'));
        $this->assertTrue($excluder->isExcluded('/news/article'));
        $this->assertTrue($excluder->isExcluded('/news/article/'));
        $this->assertTrue($excluder->isExcluded('/newspaper'));
        $this->assertTrue($excluder->isExcluded('/newspaper/'));
    }

    #[Test]
    public function url_exclusions_ignore_query_strings()
    {
        $excluder = $this->excluder(['/blog']);

        $this->assertTrue($excluder->isExcluded('/blog?page=1'));
        $this->assertTrue($excluder->isExcluded('/blog/?page=1'));
    }

    #[Test]
    public function url_exclusions_trim_the_base_url()
    {
        $excluder = $this->excluder(['/blog'], 'http://example.com');

        $this->assertTrue($excluder->isExcluded('http://example.com/blog'));
        $this->assertTrue($excluder->isExcluded('http://example.com/blog/'));
    }
}
