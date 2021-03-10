<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class RangeFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_an_integer()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => '7',
                'field' => ['type' => 'range'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'range'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 7,
            'undefined' => null,
        ]);
    }
}
