<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class RangeFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_processes_integer_values_with_integer_config()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => '7',
                'field' => ['type' => 'range', 'min' => 0, 'max' => 100, 'step' => 1],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'range', 'min' => 0, 'max' => 100, 'step' => 1],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 7,
            'undefined' => null,
        ]);
    }

    #[Test]
    public function it_processes_decimal_values_with_decimal_config()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => '7.5',
                'field' => ['type' => 'range', 'min' => 0, 'max' => 100, 'step' => 0.1],
            ],
            'another' => [
                'value' => '3.14',
                'field' => ['type' => 'range', 'min' => 0, 'max' => 10, 'step' => 0.01],
            ],
        ]);

        $this->assertGqlEntryHas('filled, another', [
            'filled' => 7.5,
            'another' => 3.14,
        ]);
    }
}
