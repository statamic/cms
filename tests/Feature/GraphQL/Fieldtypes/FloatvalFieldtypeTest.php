<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class FloatvalFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_a_float()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => 7.34,
                'field' => ['type' => 'float'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'float'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 7.34,
            'undefined' => null,
        ]);
    }
}
