<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class IterateTest extends TestCase
{
    protected $data = [
        'group_field' => [
            'first' => 'One',
            'second' => 'Two',
        ],
    ];

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }

    #[Test]
    public function arrays_work()
    {
        $template = <<<'EOT'
{{ foreach:group_field }}{{ key }} - {{ value }} {{ /foreach:group_field }}
EOT;

        $this->assertSame(
            'first - One second - Two ',
            $this->tag($template, $this->data)
        );
    }
}
