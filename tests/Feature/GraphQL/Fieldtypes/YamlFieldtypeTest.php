<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class YamlFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_yaml()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => ['foo' => 'bar'],
                'field' => ['type' => 'yaml'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'yaml'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => "foo: bar\n",
            'undefined' => null,
        ]);
    }
}
