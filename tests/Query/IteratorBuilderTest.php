<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\Test;
use Tests\Fakes\Query\TestIteratorBuilder;
use Tests\TestCase;

class IteratorBuilderTest extends TestCase
{
    #[Test]
    public function it_optimizes_limit_without_wheres()
    {
        $loadCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertEquals(10, $loadCount, 'Should only load 10 items, not all 10000');
    }

    #[Test]
    public function it_optimizes_limit_with_offset()
    {
        $loadCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->offset(5)->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertEquals(15, $loadCount, 'Should load offset + limit items');
        $this->assertEquals(6, $results->first()['id'], 'First result should be offset by 5');
    }

    #[Test]
    public function it_batches_with_wheres()
    {
        $loadCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'id' => $i,
            'value' => "item-$i",
            'even' => $i % 2 === 0,
        ]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->where('even', true)->limit(10)->get();

        $this->assertCount(10, $results);
        // With 50% match rate and batch size of 50, should need ~1-2 batches
        $this->assertLessThan(150, $loadCount, 'Should batch and stop early, not load all 10000');
        $this->assertTrue($results->every(fn ($item) => $item['even'] === true));
    }

    #[Test]
    public function it_batches_with_wheres_low_match_rate()
    {
        $loadCount = 0;
        // Only 10% of items match (every 10th item)
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'id' => $i,
            'value' => "item-$i",
            'matches' => $i % 10 === 0,
        ]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->where('matches', true)->limit(10)->get();

        $this->assertCount(10, $results);
        // With 10% match rate, need ~100 items to find 10 matches
        // Batch size is max(50, 10*2) = 50, so ~2-3 batches
        $this->assertLessThan(300, $loadCount, 'Should batch efficiently with low match rate');
        $this->assertTrue($results->every(fn ($item) => $item['matches'] === true));
    }

    #[Test]
    public function it_loads_all_when_has_orderby()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->orderBy('id', 'desc')->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertEquals(100, $loadCount, 'Must load all items to sort');
        $this->assertEquals(100, $results->first()['id'], 'First result should be highest id');
    }

    #[Test]
    public function it_loads_all_when_randomize()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->inRandomOrder()->limit(10)->get();

        $this->assertCount(10, $results);
        $this->assertEquals(100, $loadCount, 'Must load all items to randomize');
    }

    #[Test]
    public function it_loads_all_when_no_limit()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->get();

        $this->assertCount(100, $results);
        $this->assertEquals(100, $loadCount, 'Should load all items when no limit');
    }

    #[Test]
    public function it_handles_limit_greater_than_total()
    {
        $loadCount = 0;
        $items = collect(range(1, 50))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->limit(100)->get();

        $this->assertCount(50, $results);
        $this->assertEquals(50, $loadCount, 'Should load all available items');
    }

    #[Test]
    public function it_handles_offset_near_end()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->offset(95)->limit(10)->get();

        $this->assertCount(5, $results);
        $this->assertEquals(100, $loadCount, 'Should load up to available items');
    }

    #[Test]
    public function it_handles_wheres_with_no_matches()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => [
            'id' => $i,
            'value' => "item-$i",
            'status' => 'draft',
        ]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->where('status', 'published')->limit(10)->get();

        $this->assertCount(0, $results);
        $this->assertEquals(100, $loadCount, 'Should scan all items when no matches found');
    }

    #[Test]
    public function it_handles_complex_wheres()
    {
        $loadCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'id' => $i,
            'value' => "item-$i",
            'status' => $i % 3 === 0 ? 'published' : 'draft',
            'featured' => $i % 5 === 0,
        ]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        // Items matching: divisible by 3 AND divisible by 5 = divisible by 15
        $results = $builder
            ->where('status', 'published')
            ->where('featured', true)
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        // ~6.67% match rate (every 15th item), should need ~150 items
        $this->assertLessThan(500, $loadCount, 'Should batch efficiently with complex wheres');
    }

    #[Test]
    public function it_preserves_item_order_without_orderby()
    {
        $loadCount = 0;
        $items = collect(range(1, 100))->map(fn ($i) => ['id' => $i, 'value' => "item-$i"]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder->limit(10)->get();

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $results->pluck('id')->all());
    }

    #[Test]
    public function it_works_with_whereIn()
    {
        $loadCount = 0;
        $items = collect(range(1, 10000))->map(fn ($i) => [
            'id' => $i,
            'value' => "item-$i",
            'category' => 'cat-'.($i % 100),
        ]);

        $builder = new TestIteratorBuilder($items, $loadCount);
        $results = $builder
            ->whereIn('category', ['cat-1', 'cat-2', 'cat-3'])
            ->limit(10)
            ->get();

        $this->assertCount(10, $results);
        $this->assertLessThan(500, $loadCount);
    }
}
