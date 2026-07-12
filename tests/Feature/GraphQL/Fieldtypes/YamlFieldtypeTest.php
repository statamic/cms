<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class YamlFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
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
