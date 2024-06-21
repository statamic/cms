<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class ArrFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_arrays()
    {
        $keyedConfig = ['type' => 'array', 'keys' => ['foo' => 'Foo', 'bar' => 'Bar']];
        $dynamicConfig = ['type' => 'array'];

        $this->createEntryWithFields([
            'keyed' => [
                'value' => ['foo' => 'bar', 'baz' => 'qux'],
                'field' => $keyedConfig,
            ],
            'keyed_incomplete' => [
                'value' => ['foo' => 'bar'],
                'field' => $keyedConfig,
            ],
            'keyed_undefined' => [
                'value' => null,
                'field' => $keyedConfig,
            ],
            'dynamic' => [
                'value' => ['alfa' => 'bravo', 'charlie' => 'delta'],
                'field' => $dynamicConfig,
            ],
            'dynamic_undefined' => [
                'value' => null,
                'field' => $dynamicConfig,
            ],
        ]);

        $this->assertGqlEntryHas('keyed, keyed_incomplete, keyed_undefined, dynamic, dynamic_undefined', [
            'keyed' => ['foo' => 'bar', 'baz' => 'qux'],
            'keyed_incomplete' => ['foo' => 'bar'],
            'keyed_undefined' => null,
            'dynamic' => ['alfa' => 'bravo', 'charlie' => 'delta'],
            'dynamic_undefined' => null,
        ]);
    }
}
