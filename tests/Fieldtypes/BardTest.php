<?php

namespace Tests\Fieldtypes;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Bard;

class BardTest extends TestCase
{
    /** @test */
    function it_augments_prosemirror_structure_to_a_template_friendly_array()
    {
        $data = [
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
                'type' => 'image',
                'image' => 'test.jpg',
                'caption' => 'test',
            ],
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

        $this->assertEquals($expected, $this->bard()->augment($data));
    }

    /** @test */
    function it_doesnt_augment_when_saved_as_html()
    {
        $this->assertEquals('<p>Paragraph</p>', $this->bard()->augment('<p>Paragraph</p>'));
    }

    /** @test */
    function it_doesnt_augment_when_empty()
    {
        $this->assertEquals('', $this->bard()->augment(null));
    }

    /** @test */
    function it_augments_to_html_when_there_are_no_sets()
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
            ]
        ];

        $expected = '<p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p>';

        $this->assertEquals($expected, $this->bard(['sets' => []])->augment($data));
        $this->assertEquals($expected, $this->bard(['sets' => null])->augment($data));
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

        $this->assertEquals($expected, $this->bard()->augment($data));
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

        $this->assertEquals($expected, json_decode($this->bard()->preProcess($data), true));
    }

    /** @test */
    function it_detects_v2_formatted_content()
    {
        $textOnly = [
            ['type' => 'text', 'text' => '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second paragraph.</p>'],
        ];

        $setsOnly = [
            ['type' => 'one', 'foo' => 'bar', 'baz' => 'qux'],
            ['type' => 'two', 'foo' => 'bar', 'baz' => 'qux'],
        ];

        $mixed = [
            ['type' => 'text', 'text' => '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second paragraph.</p>'],
            ['type' => 'one', 'foo' => 'bar', 'baz' => 'qux'],
            ['type' => 'text', 'text' => '<p>Another paragraph.</p>'],
        ];

        $invalidSets = [
            ['type' => 'unknown', 'foo' => 'bar', 'baz' => 'qux'],
            ['type' => 'unknown_two', 'foo' => 'bar', 'baz' => 'qux'],
        ];

        $prosemirrorMixed = [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This is a paragraph with ']]],
            ['type' => 'set', 'attrs' => ['values' => ['type' => 'myset', 'foo' => 'bar', 'baz' => 'qux']]],
        ];

        $prosemirrorTextOnly = [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This is a paragraph with ']]],
        ];

        $prosemirrorSetsOnly = [
            ['type' => 'set', 'attrs' => ['values' => ['type' => 'one', 'foo' => 'bar', 'baz' => 'qux']]],
            ['type' => 'set', 'attrs' => ['values' => ['type' => 'two', 'foo' => 'bar', 'baz' => 'qux']]],
        ];

        $bard = new Bard;
        $bard->setField(new Field('test', [
            'sets' => [
                'one' => [],
                'two' => [],
            ]
        ]));

        $this->assertTrue($bard->isLegacyData($textOnly));
        $this->assertTrue($bard->isLegacyData($setsOnly));
        $this->assertTrue($bard->isLegacyData($mixed));
        $this->assertFalse($bard->isLegacyData($invalidSets));
        $this->assertFalse($bard->isLegacyData($prosemirrorMixed));
        $this->assertFalse($bard->isLegacyData($prosemirrorTextOnly));
        $this->assertFalse($bard->isLegacyData($prosemirrorSetsOnly));
    }

    /** @test */
    function it_transforms_v2_formatted_content_into_prosemirror_structure()
    {
        $data = [
            ['type' => 'text', 'text' => '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second paragraph.</p>'],
            ['type' => 'myset', 'foo' => 'bar', 'baz' => 'qux'],
            ['type' => 'text', 'text' => '<p>Another paragraph.</p>'],
        ];

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
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'set-2',
                    'enabled' => true,
                    'values' => [
                        'type' => 'myset',
                        'foo' => 'bar',
                        'baz' => 'qux',
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

        $bard = $this->bard([
            'sets' => [
                'myset' => [],
            ]
        ]);

        $this->assertEquals($expected, json_decode($bard->preProcess($data), true));
    }

    /** @test */
    function it_transforms_v2_formatted_content_with_only_sets_into_prosemirror_structure()
    {
        $data = [
            ['type' => 'myset', 'foo' => 'bar', 'baz' => 'qux'],
        ];

        $expected = [
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'set-0',
                    'enabled' => true,
                    'values' => [
                        'type' => 'myset',
                        'foo' => 'bar',
                        'baz' => 'qux',
                    ]
                ],
            ],
        ];

        $bard = new Bard;
        $bard->setField(new Field('test', [
            'sets' => [
                'myset' => [],
            ]
        ]));

        $this->assertEquals($expected, json_decode($bard->preProcess($data), true));
    }

    private function bard($config = [])
    {
        return (new Bard)->setField(new Field('test', array_merge(['type' => 'bard', 'sets' => ['one' => []]], $config)));
    }
}
