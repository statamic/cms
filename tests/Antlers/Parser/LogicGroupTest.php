<?php

namespace Tests\Antlers\Parser;

use Tests\Antlers\ParserTestCase;

class LogicGroupTest extends ParserTestCase
{
    public function test_logic_groups_can_neighbor_path_terminators()
    {
        $data = [
            'sizes' => [
                'sm' => 'text-sm',
                'md' => 'text-base',
                'lg' => 'text-lg',
            ],
        ];

        $this->assertSame('text-base', $this->renderString('{{ size = (size ? sizes[size] : sizes["md"]); size }}', $data));
        $this->assertSame('text-lg', $this->renderString('{{ size = (size ? sizes[size] : sizes["md"]); size }}', array_merge($data, ['size' => 'lg'])));
    }
}
