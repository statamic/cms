<?php

namespace Tests\Modifiers;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class BardItemsTest extends TestCase
{
    #[Test]
    public function it_extracts_bard_items()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' text.'],
                ],
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
        ];

        $expected = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' text.'],
                ],
            ],
            ['type' => 'text', 'text' => 'This is a paragraph with '],
            ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
            ['type' => 'bold', 'node' => ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold']],
            ['type' => 'text', 'text' => ' text.'],
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
        ];

        $this->assertEquals($expected, $this->modify($data));
    }

    #[Test]
    public function it_extracts_bard_items_from_single_node()
    {
        $data = [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'This is a paragraph.'],
            ],
        ];

        $expected = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph.'],
                ],
            ],
            ['type' => 'text', 'text' => 'This is a paragraph.'],
        ];

        $this->assertEquals($expected, $this->modify($data));
    }

    #[Test]
    public function it_extracts_bard_items_from_value_object()
    {
        $data = new Value([
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph.'],
                ],
            ],
        ], 'content', new Bard());

        $expected = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph.'],
                ],
            ],
            ['type' => 'text', 'text' => 'This is a paragraph.'],
        ];

        $this->assertEquals($expected, $this->modify($data));
    }

    #[Test]
    public function it_extracts_bard_items_with_nodes_appended_to_marks()
    {
        $data = [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'This is a paragraph with '],
                $node = ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                ['type' => 'text', 'text' => ' text.'],
            ],
        ];

        $items = $this->modify($data);
        $mark = Arr::first(Arr::where($items, fn ($item) => $item['type'] === 'bold'));
        $this->assertEquals($node, $mark['node']);
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->bard_items($args)->fetch();
    }
}
