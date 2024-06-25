<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class TableFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_a_table()
    {
        $this->createEntryWithFields([
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'table'],
            ],
            'filled' => [
                'field' => ['type' => 'table'],
                'value' => [
                    ['cells' => ['r1c1', 'r1c2', 'r1c3']],
                    ['cells' => ['r2c1', 'r2c2', 'r2c3']],
                    ['cells' => ['r3c1', 'r3c2', 'r3c3']],
                ],
            ],
        ]);

        $query = <<<'GQL'
undefined {
    cells
}
filled {
    cells
}
GQL;

        $this->assertGqlEntryHas($query, [
            'undefined' => null,
            'filled' => [
                ['cells' => ['r1c1', 'r1c2', 'r1c3']],
                ['cells' => ['r2c1', 'r2c2', 'r2c3']],
                ['cells' => ['r3c1', 'r3c2', 'r3c3']],
            ],
        ]);
    }
}
