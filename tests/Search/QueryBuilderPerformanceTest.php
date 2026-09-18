<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fakes\Query\HydrationTrackingQueryBuilder;
use Tests\TestCase;

class QueryBuilderPerformanceTest extends TestCase
{
    #[Test]
    public function it_only_hydrates_limited_items_when_no_wheres()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertLessThanOrEqual(10, $hydrationCount, 'Should only hydrate 10 items, not all 10000');
    }

    #[Test]
    public function it_batches_hydration_when_has_wheres()
    {
        $hydrationCount = 0;
        // Create items where only 10% match the filter
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'status' => $i % 10 === 0 ? 'published' : 'draft',
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()
            ->where('status', 'published')
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        // Should hydrate ~100-200 items (enough batches to find 10 matches), not all 10000
        $this->assertLessThan(500, $hydrationCount, 'Should batch hydrate, not hydrate all 10000');
    }

    #[Test]
    public function it_loads_all_when_has_orderby()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'title' => "Title $i",
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()
            ->orderBy('title')
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        // Must load all 100 to sort
        $this->assertEquals(100, $hydrationCount, 'Must hydrate all items to sort');
    }

    #[Test]
    public function it_handles_offset_with_limit_optimization()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->offset(100)->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertLessThanOrEqual(110, $hydrationCount, 'Should only hydrate offset + limit items');
    }

    #[Test]
    public function it_handles_large_limit_efficiently()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->limit(1000)->get();

        $this->assertCount(1000, $results);
        $this->assertEquals(1000, $hydrationCount, 'Should hydrate exactly 1000 items');
    }

    #[Test]
    public function it_handles_wheres_with_very_low_match_rate()
    {
        $hydrationCount = 0;
        // Only 1% of items match (every 100th item)
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'rare' => $i % 100 === 0,
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()
            ->where('rare', true)
            ->limit(5)
            ->get();

        $this->assertCount(5, $results);
        // With 1% match rate, need ~500 items to find 5 matches
        // Should be much less than 10000
        $this->assertLessThan(1500, $hydrationCount, 'Should batch efficiently even with low match rate');
    }

    #[Test]
    public function it_handles_no_matching_items()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'status' => 'draft',
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()
            ->where('status', 'published')
            ->limit(10)
            ->get();

        $this->assertCount(0, $results);
        $this->assertEquals(100, $hydrationCount, 'Should scan all items when no matches');
    }

    #[Test]
    public function it_respects_search_score_ordering_after_optimization()
    {
        $hydrationCount = 0;
        // Items with search scores in descending order
        $items = collect(range(1, 100))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'search_score' => 100 - $i + 1, // 100, 99, 98, ...
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->limit(10)->get();

        $this->assertCount(10, $results);
        // Results should be in original order (by search_score)
        $this->assertEquals(
            [100, 99, 98, 97, 96, 95, 94, 93, 92, 91],
            $results->pluck('search_score')->all()
        );
    }

    #[Test]
    public function it_loads_all_when_randomized()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->inRandomOrder()->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertEquals(100, $hydrationCount, 'Must hydrate all items to randomize');
    }

    #[Test]
    public function it_loads_all_when_no_limit()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->get();

        $this->assertCount(100, $results);
        $this->assertEquals(100, $hydrationCount, 'Should hydrate all items when no limit');
    }

    #[Test]
    public function it_handles_limit_greater_than_total()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 50))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->limit(100)->get();

        $this->assertCount(50, $results);
        $this->assertEquals(50, $hydrationCount, 'Should hydrate all available items');
    }

    #[Test]
    public function it_handles_multiple_wheres()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'status' => $i % 3 === 0 ? 'published' : 'draft',
            'featured' => $i % 5 === 0,
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        // Items matching: divisible by 3 AND divisible by 5 = divisible by 15 (~6.67%)
        $results = $builder->withoutData()
            ->where('status', 'published')
            ->where('featured', true)
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        // Should batch efficiently, not load all 10000
        $this->assertLessThan(1000, $hydrationCount, 'Should batch efficiently with multiple wheres');
    }

    #[Test]
    public function it_handles_whereIn()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'reference' => "entry::item-$i",
            'category' => 'cat-'.($i % 100),
        ]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        // 3% match rate (3 categories out of 100)
        $results = $builder->withoutData()
            ->whereIn('category', ['cat-1', 'cat-2', 'cat-3'])
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        $this->assertLessThan(1000, $hydrationCount, 'Should batch efficiently with whereIn');
    }

    #[Test]
    public function it_handles_offset_near_end()
    {
        $hydrationCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['reference' => "entry::item-$i"]);

        $builder = new HydrationTrackingQueryBuilder($items, $hydrationCount);
        $results = $builder->withoutData()->offset(95)->limit(10)->get();

        $this->assertCount(5, $results);
        $this->assertEquals(100, $hydrationCount, 'Should hydrate up to available items');
    }
}
