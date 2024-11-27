<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class IterateTest extends TestCase
{
    #[Test]
    public function it_iterates()
    {
        $template = '{{ foreach:fieldname }}<{{ key }},{{ value }}>{{ /foreach:fieldname }}';

        $this->assertSame('<alfa,one><bravo,two>', $this->tag($template, ['fieldname' => [
            'alfa' => 'one',
            'bravo' => 'two',
        ]]));
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }
}
