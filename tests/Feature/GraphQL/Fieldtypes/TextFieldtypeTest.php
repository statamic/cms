<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class TextFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
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
