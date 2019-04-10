<?php

namespace Tests\Fields\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Fieldtypes\Bard;

class BardTest extends TestCase
{
    /** @test */
    function it_augments_prosemirror_structure_to_a_template_friendly_array()
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
                ]
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
                    ]
                ],
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Another paragraph.']
                ]
            ]
        ];

        $expected = [
            [
                'type' => 'text',
                'text' => '<p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p><p></p>',
            ],
            [
                'type' => 'image',
                'image' => 'test.jpg',
                'caption' => 'test',
            ],
            [
                'type' => 'text',
                'text' => '<p>Another paragraph.</p>',
            ]
        ];

        $this->assertEquals($expected, (new Bard)->augment($data));
    }

    /** @test */
    function it_removes_disabled_sets()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'This is a paragraph.']]
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'enabled' => false,
                    'values' => [
                        'type' => 'test',
                        'value' => 'one',
                    ]
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'values' => [
                        'type' => 'test',
                        'value' => 'two',
                    ]
                ],
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Another paragraph.']
                ]
            ]
        ];

        $expected = [
            [
                'type' => 'text',
                'text' => '<p>This is a paragraph.</p>',
            ],
            [
                'type' => 'test',
                'value' => 'two',
            ],
            [
                'type' => 'text',
                'text' => '<p>Another paragraph.</p>',
            ]
        ];

        $this->assertEquals($expected, (new Bard)->augment($data));
    }

    /** @test */
    function it_converts_plain_html_into_prosemirror_structure()
    {
        $data = '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second paragraph.</p>';

        $expected = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' text.'],
                ]
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Second paragraph.'],
                ]
            ],
        ];

        $this->assertEquals($expected, json_decode((new Bard)->preProcess($data), true));
    }
}