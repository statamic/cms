<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class ListFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_a_list()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => ['one', 'two', 'three'],
                'field' => ['type' => 'list'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'list'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => ['one', 'two', 'three'],
            'undefined' => null,
        ]);
    }
}
