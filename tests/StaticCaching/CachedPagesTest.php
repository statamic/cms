<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\StaticCacheCleared;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\CachedPages;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\WarmableUrls;
use Tests\TestCase;

class CachedPagesTest extends TestCase
{
    #[Test]
    public function it_shares_cached_page_statuses_between_coverage_and_uncached_listings_until_they_expire()
    {
        $this->freezeTime();
        $one = 'http://localhost/one';
        $two = 'http://localhost/two';
        $this->mock(WarmableUrls::class)->shouldReceive('all')->twice()->andReturn(collect([$one, $two]));
        $cacher = $this->mock(Cacher::class);
        $cacher->shouldReceive('hasCachedPage')->withArgs(fn ($request) => $request->getUri() === $one)
            ->twice()->andReturn(true, false);
        $cacher->shouldReceive('hasCachedPage')->withArgs(fn ($request) => $request->getUri() === $two)
            ->twice()->andReturn(false, true);

        $this->assertSame(['cached' => 1, 'total' => 2, 'uncached' => 1], (new CachedPages($cacher))->coverage());
        $this->assertSame([$two], (new CachedPages($cacher))->uncached()->pluck('url')->all());
        $this->assertSame([$two], (new CachedPages($cacher))->uncached()->pluck('url')->all());

        $this->travel(61)->seconds();

        $this->assertSame([$one], (new CachedPages($cacher))->uncached()->pluck('url')->all());
        $this->assertSame(['cached' => 1, 'total' => 2, 'uncached' => 1], (new CachedPages($cacher))->coverage());
    }

    #[Test]
    public function flushing_or_invalidating_static_pages_discards_cached_coverage()
    {
        $url = 'http://localhost/one';
        $this->mock(WarmableUrls::class)->shouldReceive('all')->times(3)->andReturn(collect([$url]));
        $cacher = $this->mock(Cacher::class);
        $cacher->shouldReceive('hasCachedPage')->times(3)->andReturn(true, false, false);
        $pages = new CachedPages($cacher);

        $this->assertSame(['cached' => 1, 'total' => 1, 'uncached' => 0], $pages->coverage());

        StaticCacheCleared::dispatch();

        $this->assertSame(['cached' => 0, 'total' => 1, 'uncached' => 1], $pages->coverage());

        UrlInvalidated::dispatch('/one', 'http://localhost');

        $this->assertSame([$url], $pages->uncached()->pluck('url')->all());
    }
}
