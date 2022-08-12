<?php

namespace Tests\Modifiers;

use Statamic\Facades\Antlers;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class BardHtmlTest extends TestCase
{
    /** @test */
    public function it_extracts_bard_html()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' and '],
                    ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'italic'],
                    ['type' => 'text', 'text' => ' text.'],
                ],
            ],
            [
                'type' => 'paragraph',
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'values' => [
                        'type' => 'image',
                        'image' => 'test.jpg',
                        'caption' => 'test',
                    ],
                ],
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Another paragraph.'],
                ],
            ],
        ];

        $expected = '<p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p><p></p><p>Another paragraph.</p>';

        $this->assertEquals($expected, $this->modify($data));
    }

    /** @test */
    public function it_extracts_bard_html_from_single_node()
    {
        $data = [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'This is a paragraph.'],
            ],
        ];

        $expected = '<p>This is a paragraph.</p>';

        $this->assertEquals($expected, $this->modify($data));
    }

    /** @test */
    public function it_extracts_bard_html_from_value_object()
    {
        $data = new Value([
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph.'],
                ],
            ],
        ], 'content', new Bard());

        $expected = '<p>This is a paragraph.</p>';

        $this->assertEquals($expected, $this->modify($data));
    }

    /** @test */
    public function it_extracts_bard_html_and_parses_antlers()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => "This is {{ title }}!"],
                ],
            ],
        ];

        $expected = '<p>This is Antlers!</p>';

        $this->assertEquals(
            $expected,
            (string) Antlers::parser()->parse('{{ test }}', [
                'test' => $this->modify($data, true),
                'title' => 'Antlers',
            ])
        );
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->bard_html($args)->fetch();
    }
}
