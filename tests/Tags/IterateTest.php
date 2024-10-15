<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class IterateTest extends TestCase
{
    #[Test]
    public function arrays_work()
    {
        $template = '{{ foreach:group_field }}{{ key }} - {{ value }} {{ /foreach:group_field }}';

        $this->assertSame(
            'first - One second - Two ',
            $this->tag($template, [
                'group_field' => [
                    'first' => 'One',
                    'second' => 'Two',
                ],
            ])
        );
    }

    #[Test]
    public function values_work()
    {
        $this->markTestSkipped('needs implementation');

        $template = '{{ foreach:group_field }}{{ key }} - {{ value }} {{ /foreach:group_field }}';

        $this->assertSame(
            'first - One second - Two ',
            $this->tag($template, [
                'group_field' => [
                    'first' => 'One',
                    'second' => 'Two',
                ],
            ])
        );
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }
}
