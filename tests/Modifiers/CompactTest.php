<?php

namespace Tests\Modifiers;

use Statamic\Facades\Parse;
use Tests\TestCase;

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

    /** @test */
    public function compact_coverts_variables_to_array()
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
