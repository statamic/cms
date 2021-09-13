<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class CodeFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_code()
    {
        $this->createEntryWithFields([
            'foo' => [
                'value' => 'bar',
                'field' => ['type' => 'code'],
            ],
            'bar' => [
                'value' => null,
                'field' => ['type' => 'code'],
            ],
        ]);

        $this->assertGqlEntryHas('foo, bar', [
            'foo' => 'bar',
            'bar' => null,
        ]);
    }
}
