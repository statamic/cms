<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class ChunkTest extends TestCase
{
    #[Test]
    public function it_breaks_a_collection_into_smaller_chunks(): void
    {
        $collection = $this->collectionWithSixItems();

        $modified = $this->modify($collection, [3]);
        $this->assertCount(2, $modified);

        $chunkOne = $modified[0]['chunk'];
        $chunkTwo = $modified[1]['chunk'];

        $this->assertCount(3, $chunkOne);
        $this->assertCount(3, $chunkTwo);
    }

    #[Test]
    public function it_breaks_a_collection_into_six_chunks(): void
    {
        $collection = $this->collectionWithSixItems();

        $modified = $this->modify($collection, [6]);
        $this->assertCount(1, $modified);
    }

    #[Test]
    public function it_return_no_chunks_when_param_is_zero_or_negative(): void
    {
        $collection = $this->collectionWithSixItems();

        $modified = $this->modify($collection, [0]);
        $this->assertCount(0, $modified);

        $modified = $this->modify($collection, [-3]);
        $this->assertCount(0, $modified);
    }

    #[Test]
    public function it_chunks_values_from_query_builder()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn($this->collectionWithSixItems());

        $modified = $this->modify($builder, [3]);
        $this->assertCount(2, $modified);

        $chunkOne = $modified[0]['chunk'];
        $chunkTwo = $modified[1]['chunk'];

        $this->assertCount(3, $chunkOne);
        $this->assertCount(3, $chunkTwo);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->chunk($params)->fetch();
    }

    public function collectionWithSixItems(): Collection
    {
        return collect([
            [
                'url' => '/ideas/book',
                'title' => 'Book: Somehow I Manage',
            ],
            [
                'url' => '/ideas/party',
                'title' => 'Party: Goodbye Toby',
            ],
            [
                'url' => '/ideas/screenplay',
                'title' => 'Screenplay: Threat Level Midnight',
            ],
            [
                'url' => '/ideas/art',
                'title' => 'Art: A Stapler',
            ],
            [
                'url' => '/ideas/poster',
                'title' => 'Poster: Kids Playing Instruments',
            ],
            [
                'url' => '/ideas/mug',
                'title' => 'Mug: World\'s Best Boss',
            ],
        ]);
    }
}
