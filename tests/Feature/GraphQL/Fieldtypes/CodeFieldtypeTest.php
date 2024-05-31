<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class CodeFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_code()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => 'bar',
                'field' => ['type' => 'code'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'code'],
            ],
            'selectable_string' => [
                'value' => 'bar',
                'field' => ['type' => 'code', 'mode_selectable' => true],
            ],
            'selectable_array' => [
                'value' => ['code' => 'bar', 'mode' => 'php'],
                'field' => ['type' => 'code', 'mode_selectable' => true],
            ],
            'selectable_undefined' => [
                'value' => null,
                'field' => ['type' => 'code', 'mode_selectable' => true],
            ],
        ]);

        $this->assertGqlEntryHas('
            filled
            undefined
            selectable_string { code, mode }
            selectable_array { code, mode }
            selectable_undefined { code, mode }
        ', [
            'filled' => 'bar',
            'undefined' => null,
            'selectable_string' => ['code' => 'bar', 'mode' => 'htmlmixed'],
            'selectable_array' => ['code' => 'bar', 'mode' => 'php'],
            'selectable_undefined' => null,
        ]);
    }
}
