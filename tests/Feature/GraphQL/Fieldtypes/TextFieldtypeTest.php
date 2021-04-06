<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class TextFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_text()
    {
        $this->createEntryWithFields([
            'foo' => [
                'value' => 'bar',
                'field' => ['type' => 'text'],
            ],
            'bar' => [
                'value' => null,
                'field' => ['type' => 'text'],
            ],
        ]);

        $this->assertGqlEntryHas('foo, bar', [
            'foo' => 'bar',
            'bar' => null,
        ]);
    }
}
