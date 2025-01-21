<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

#[Group('array')]
class CompactTest extends TestCase
{
    protected $data = [
        'view' => [
            'var_one' => 'value one',
            'var_two' => 'value two',
        ],
        'title' => 'Hello, there!',
        'nested' => [
            'variable' => [
                'path' => 'nested-value',
            ],
        ],
    ];

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    #[Test]
    public function compact_converts_variables_to_array()
    {
        $template = <<<'EOT'
{{ foreach :array="'view:var_one, view:var_two, title, nested:variable:path'|compact" }}<{{ value }}>{{ /foreach }}
EOT;

        $this->assertSame(
            '<value one><value two><Hello, there!><nested-value>',
            $this->tag($template, $this->data)
        );
    }
}
