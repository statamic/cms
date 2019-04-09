<?php

namespace Tests\Fields\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Fieldtypes\Tiptap;

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

        $this->assertEquals($expected, (new Tiptap)->augment($data));
    }
}