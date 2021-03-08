<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class TaggableFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_a_taggable()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => ['one', 'two', 'three'],
                'field' => ['type' => 'taggable'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'taggable'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => ['one', 'two', 'three'],
            'undefined' => null,
        ]);
    }
}
