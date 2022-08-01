<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Statamic\Stache\Query\EntryQueryBuilder;
use Tests\TestCase;

class IsQueryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_true_if_input_is_query_builder(): void
    {
        $queryBuilder = $this->mock(EntryQueryBuilder::class);

        $this->assertTrue($this->modify($queryBuilder));
    }

    private function modify($value)
    {
        return Modify::value($value)->isQueryBuilder()->fetch();
    }
}
