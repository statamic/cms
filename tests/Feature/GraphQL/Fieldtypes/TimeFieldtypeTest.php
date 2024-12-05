<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class TimeFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_the_time()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => '13:45',
                'field' => ['type' => 'time'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'time'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => '13:45',
            'undefined' => null,
        ]);
    }
}
