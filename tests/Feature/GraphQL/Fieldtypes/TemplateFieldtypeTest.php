<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class TemplateFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_the_template()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => 'foo',
                'field' => ['type' => 'video'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'video'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 'foo',
            'undefined' => null,
        ]);
    }
}
