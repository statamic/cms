<?php

namespace Tests\StaticCaching;

use Statamic\Contracts\Data\Content\Content;
use Statamic\Contracts\Entries\Entry;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\DefaultInvalidator as Invalidator;

class DefaultInvalidatorTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        parent::tearDown();
    }

    /** @test */
    public function specifying_all_as_invalidation_rule_will_just_flush_the_cache()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher, 'all');
        $content = \Mockery::mock(Content::class);

        $invalidator->invalidate($content);

        $cacher->shouldHaveReceived('flush');
    }

    /** @test */
    public function the_items_url_gets_invalidated()
    {
        $cacher = \Mockery::spy(Cacher::class);
        $invalidator = new Invalidator($cacher);

        $entry = tap(\Mockery::mock(Entry::class), function ($m) {
            $m->shouldReceive('url')->andReturn('/my/test/entry');
        });

        $invalidator->invalidate($entry);

        $cacher->shouldNotHaveReceived('flush');
        $cacher->shouldHaveReceived('invalidateUrl')->with('/my/test/entry');
    }

    /** @test */
    public function collection_urls_can_be_invralidated()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function taxonomy_urls_can_be_invalidated()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function page_urls_can_be_invalidated()
    {
        $this->markTestIncomplete();
    }
}
