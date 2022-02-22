<?php

namespace Tests\Antlers\Parser;

use Tests\Antlers\ParserTestCase;

class TernaryGroupsTest extends ParserTestCase
{
    public function test_ternary_groups_will_stop_collecting_condition_when_they_encounter_operators()
    {
        $data = [
            'sizes' => [
                'sm' => 'text-sm',
                'md' => 'text-base',
                'lg' => 'text-lg',
            ],
        ];

        $this->assertSame('text-base', $this->renderString('{{ size = size ? sizes[size] : sizes["md"]; size }}', $data));
        $this->assertSame('text-lg', $this->renderString('{{ size = size ? sizes[size] : sizes["md"]; size }}', array_merge($data, ['size' => 'lg'])));
    }

    public function test_simple_interpolations_within_path_accessor_can_replace_simple_ternary()
    {
        $data = [
            'sizes' => [
                'sm' => 'text-sm',
                'md' => 'text-base',
                'lg' => 'text-lg',
            ],
        ];

        $this->assertSame('text-base', $this->renderString("{{ size = sizes[{size ?? 'md'}]; size }}", $data));
    }
}
