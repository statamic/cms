<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class TimeFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
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
