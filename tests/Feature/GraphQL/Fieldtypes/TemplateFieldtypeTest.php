<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class TemplateFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
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
