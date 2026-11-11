<?php

namespace Tests\StaticCaching;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\StacheCleared;
use Statamic\StaticCaching\WarmableUrls;
use Tests\TestCase;

class WarmableUrlsTest extends TestCase
{
    #[Test]
    public function it_reuses_discovered_urls_for_a_minute_across_instances_and_membership_checks()
    {
        $this->freezeTime();
        $first = Mockery::mock(WarmableUrls::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $first->shouldReceive('discover')->once()->andReturn(collect(['http://localhost/one']));

        $this->assertSame(['http://localhost/one'], $first->all()->all());
        $this->assertTrue((new WarmableUrls)->contains('http://localhost/one'));
        $this->assertFalse((new WarmableUrls)->contains('http://localhost/two'));
        $this->assertSame(['http://localhost/one'], (new WarmableUrls)->all()->all());

        $this->travel(61)->seconds();
        $second = Mockery::mock(WarmableUrls::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $second->shouldReceive('discover')->once()->andReturn(collect(['http://localhost/two']));

        $this->assertSame(['http://localhost/two'], $second->all()->all());
        $this->assertFalse((new WarmableUrls)->contains('http://localhost/one'));
    }

    #[Test]
    public function clearing_the_stache_also_discards_discovered_urls()
    {
        $warmableUrls = Mockery::mock(WarmableUrls::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $warmableUrls->shouldReceive('discover')->twice()->andReturn(
            collect(['http://localhost/one']),
            collect(['http://localhost/two'])
        );

        $this->assertSame(['http://localhost/one'], $warmableUrls->all()->all());

        StacheCleared::dispatch();

        $this->assertSame(['http://localhost/two'], $warmableUrls->all()->all());
    }
}
