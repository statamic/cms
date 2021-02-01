<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class ToggleFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_a_boolean()
    {
        $this->createEntryWithFields([
            'yup' => [
                'value' => true,
                'field' => ['type' => 'toggle'],
            ],
            'nope' => [
                'value' => false,
                'field' => ['type' => 'toggle'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'toggle'],
            ],
        ]);

        $this->assertGqlEntryHas('yup, nope, undefined', [
            'yup' => true,
            'nope' => false,
            'undefined' => false,
        ]);
    }
}
