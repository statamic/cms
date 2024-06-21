<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class IntegerFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_an_integer()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => '7',
                'field' => ['type' => 'integer'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'integer'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 7,
            'undefined' => null,
        ]);
    }
}
