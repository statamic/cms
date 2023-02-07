<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Bard\Augmentor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tiptap\Core\Node;

class BardTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    // Mocking method_exists, courtesy of https://stackoverflow.com/a/37928161
    public static $functions;

    public function tearDown(): void
    {
        parent::tearDown();
        static::$functions = null;
    }

    /** @test */
    public function it_augments_prosemirror_structure_to_a_template_friendly_array()
    {
        (new class extends Fieldtype
        {
            public static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        })::register();

        $data = [
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'test1',
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
                    // id intentionally omitted
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

        $expected = [
            [
                'id' => 'test1',
                'type' => 'image',
                'image' => 'test.jpg (augmented)',
                'caption' => 'test (augmented)',
            ],
            [
                'type' => 'text',
                'text' => '<p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p><p></p>',
            ],
            [
                'id' => null,
                'type' => 'image',
                'image' => 'test.jpg (augmented)',
                'caption' => 'test (augmented)',
            ],
            [
                'type' => 'text',
                'text' => '<p>Another paragraph.</p>',
            ],
        ];

        $augmented = $this->bard([
            'sets' => [
                'image' => [
                    'fields' => [
                        ['handle' => 'image', 'field' => ['type' => 'test']],
                        ['handle' => 'caption', 'field' => ['type' => 'test']],
                    ],
                ],
            ],
        ])->augment($data);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals($expected, collect($augmented)->toArray());
    }

    /** @test */
    public function it_doesnt_augment_when_saved_as_html()
    {
        $this->assertEquals('<p>Paragraph</p>', $this->bard()->augment('<p>Paragraph</p>'));
    }

    /** @test */
    public function it_augments_tiptap_v1_snake_case_types_to_v2_camel_case_types()
    {
        Augmentor::addExtension('customNode', new class extends Node
        {
            public static $name = 'customNode';

            public function renderHTML($node, $HTMLAttributes = [])
            {
                return [
                    'div',
                    ['type' => $node->attrs->type],
                    0,
                ];
            }
        });

        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'This is ',
                    ],
                    [
                        'type' => 'text',
                        'marks' => [
                            ['type' => 'bold'],
                        ],
                        'text' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => ' text.',
                    ],
                ],
            ],
            [
                'type' => 'custom_node',
                'attrs' => [
                    'type' => 'custom_type_attribute', // shouldn't be camel cased
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => '123',
                    'values' => [
                        'type' => 'my_set',
                        'text' => 'test',
                    ],
                ],
            ],
            [
                'type' => 'bullet_list',
                'content' => [
                    [
                        'type' => 'list_item',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'This is a list item.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            [
                'type' => 'text',
                'text' => '<p>This is <strong>bold</strong> text.</p><div type="custom_type_attribute"></div>',
            ],
            [
                'id' => '123',
                'type' => 'my_set',
                'text' => 'test',
            ],
            [
                'type' => 'text',
                'text' => '<ul><li><p>This is a list item.</p></li></ul>',
            ],
        ];

        $augmented = $this->bard([
            'sets' => [
                'my_set' => [
                    'fields' => [
                        ['handle' => 'text', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ])->augment($data);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals($expected, collect($augmented)->toArray());
    }

    /** @test */
    public function it_augments_to_html_when_there_are_no_sets()
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
        ];

        $expected = '<p>This is a paragraph with <strong>bold</strong> and <em>italic</em> text.</p>';

        $this->assertEquals($expected, $this->bard(['sets' => []])->augment($data));
        $this->assertEquals($expected, $this->bard(['sets' => null])->augment($data));
    }

    /** @test */
    public function augmenting_an_empty_value_when_not_using_sets_returns_null()
    {
        $this->assertNull($this->bard(['sets' => null])->augment(null));
    }

    /** @test */
    public function augmenting_an_empty_value_when_using_sets_returns_an_empty_array()
    {
        $this->assertSame([], $this->bard(['sets' => ['one' => []]])->augment(null));
    }

    /** @test */
    public function augmenting_an_empty_value_when_saving_as_html_returns_null()
    {
        $bard = $this->bard(['save_html' => true, 'sets' => null]);

        $this->assertNull($bard->augment(null));
    }

    /** @test */
    public function it_removes_disabled_sets()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'This is a paragraph.']],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'test1',
                    'enabled' => false,
                    'values' => [
                        'type' => 'test',
                        'value' => 'one',
                    ],
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'test2',
                    'values' => [
                        'type' => 'test',
                        'value' => 'two',
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

        $expected = [
            [
                'type' => 'text',
                'text' => '<p>This is a paragraph.</p>',
            ],
            [
                'id' => 'test2',
                'type' => 'test',
                'value' => 'two',
            ],
            [
                'type' => 'text',
                'text' => '<p>Another paragraph.</p>',
            ],
        ];

        $augmented = $this->bard()->augment($data);

        $this->assertEveryItemIsInstanceOf(Values::class, $augmented);
        $this->assertEquals($expected, collect($augmented)->toArray());
    }

    /** @test */
    public function it_converts_plain_html_into_prosemirror_structure()
    {
        $data = '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second <a href="statamic://entry::foo">paragraph</a>. <img src="statamic://asset::assets::lagoa.jpg"></p>';

        $expected = [
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' text.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'Second '],
                    ['type' => 'text', 'text' => 'paragraph', 'marks' => [
                        ['type' => 'link', 'attrs' => ['href' => 'entry::foo']],
                    ]],
                    ['type' => 'text', 'text' => '. '],
                    ['type' => 'image', 'attrs' => [
                        'src' => 'asset::assets::lagoa.jpg',
                    ]],
                ],
            ],
        ];

        $this->assertEquals($expected, json_decode($this->bard()->preProcess($data), true));
    }

    /** @test */
    public function it_detects_v2_formatted_content()
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
            ],
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
    public function it_transforms_v2_formatted_content_into_prosemirror_structure()
    {
        RowId::shouldReceive('generate')->andReturn('random-string-1');

        $data = [
            ['type' => 'text', 'text' => '<p>This is a paragraph with <strong>bold</strong> text.</p><p>Second paragraph.</p>'],
            ['type' => 'myset', 'foo' => 'bar', 'baz' => 'qux'],
            ['type' => 'text', 'text' => '<p>Another paragraph.</p>'],
        ];

        $expected = [
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'This is a paragraph with '],
                    ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
                    ['type' => 'text', 'text' => ' text.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'Second paragraph.'],
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'random-string-1',
                    'enabled' => true,
                    'values' => [
                        'type' => 'myset',
                        'foo' => 'bar',
                        'baz' => 'qux',
                    ],
                ],
            ],
            [
                'type' => 'paragraph',
                'attrs' => ['textAlign' => 'left'],
                'content' => [
                    ['type' => 'text', 'text' => 'Another paragraph.'],
                ],
            ],
        ];

        $bard = $this->bard([
            'sets' => [
                'myset' => [],
            ],
        ]);

        $this->assertEquals($expected, json_decode($bard->preProcess($data), true));
    }

    /** @test */
    public function it_transforms_v2_formatted_content_with_only_sets_into_prosemirror_structure()
    {
        RowId::shouldReceive('generate')->andReturn('random-string-1');

        $data = [
            ['type' => 'myset', 'foo' => 'bar', 'baz' => 'qux'],
        ];

        $expected = [
            [
                'type' => 'set',
                'attrs' => [
                    'id' => 'random-string-1',
                    'enabled' => true,
                    'values' => [
                        'type' => 'myset',
                        'foo' => 'bar',
                        'baz' => 'qux',
                    ],
                ],
            ],
        ];

        $bard = new Bard;
        $bard->setField(new Field('test', [
            'sets' => [
                'myset' => [],
            ],
        ]));

        $this->assertEquals($expected, json_decode($bard->preProcess($data), true));
    }

    /** @test */
    public function it_saves_an_empty_field_as_null()
    {
        // When a Bard field is emptied and submitted, it's not actually null, it's a single empty paragraph.
        $bard = new Bard;
        $this->assertNull($bard->process('[{"type":"paragraph"}]'));

        // When it is actually null (eg. when it was not in the front matter to begin with, and was never touched), it's an empty array.
        $this->assertNull($bard->process('[]'));
    }

    /** @test */
    public function it_removes_empty_nodes()
    {
        $content = '[
            {"type":"paragraph"},
            {"type":"heading"},
            {"type":"paragraph", "content": "foo"},
            {"type":"heading"},
            {"type":"paragraph"},
            {"type":"heading", "content": "foo"},
            {"type":"paragraph"},
            {"type":"heading"}
        ]';

        $containsAllEmptyNodes = $this->bard(['remove_empty_nodes' => false])->process($content);

        $this->assertEquals($containsAllEmptyNodes, [
            ['type' => 'paragraph'],
            ['type' => 'heading'],
            ['type' => 'paragraph', 'content' => 'foo'],
            ['type' => 'heading'],
            ['type' => 'paragraph'],
            ['type' => 'heading', 'content' => 'foo'],
            ['type' => 'paragraph'],
            ['type' => 'heading'],
        ]);

        $removedAllEmptyNodes = $this->bard(['remove_empty_nodes' => true])->process($content);

        $this->assertEquals($removedAllEmptyNodes, [
            ['type' => 'paragraph', 'content' => 'foo'],
            ['type' => 'heading', 'content' => 'foo'],
        ]);

        $trimmedEmptyNodes = $this->bard(['remove_empty_nodes' => 'trim'])->process($content);

        $this->assertEquals($trimmedEmptyNodes, [
            ['type' => 'paragraph', 'content' => 'foo'],
            ['type' => 'heading'],
            ['type' => 'paragraph'],
            ['type' => 'heading', 'content' => 'foo'],
        ]);
    }

    /** @test */
    public function it_preloads_preprocessed_default_values()
    {
        $field = (new Field('test', [
            'type' => 'bard',
            'sets' => [
                'main' => [
                    'fields' => [
                        ['handle' => 'things', 'field' => ['type' => 'array']],
                    ],
                ],
            ],
        ]))->setValue('[]'); // what an empty value would get preprocessed into.

        $expected = [
            'things' => [],
        ];

        $this->assertEquals($expected, $field->fieldtype()->preload()['defaults']['main']);
    }

    /** @test */
    public function it_preloads_new_meta_with_preprocessed_values()
    {
        RowId::shouldReceive('generate')->andReturn('random1', 'random2');

        // For this test, use a grid field with min_rows.
        // It doesn't have to be, but it's a fieldtype that would
        // require preprocessed values to be provided down the line.
        // https://github.com/statamic/cms/issues/3481

        $field = (new Field('test', [
            'type' => 'bard',
            'sets' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'things',
                            'field' => [
                                'type' => 'grid',
                                'min_rows' => 2,
                                'fields' => [
                                    ['handle' => 'one', 'field' => ['type' => 'text']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->setValue('[]'); // what an empty value would get preprocessed into.

        $expected = [
            '_' => '_',
            'things' => [ // this array is the preloaded meta for the grid field
                'defaults' => [
                    'one' => null, // default value for the text field
                ],
                'new' => [
                    'one' => null, // meta for the text field
                ],
                'existing' => [
                    'random1' => ['one' => null],
                    'random2' => ['one' => null],
                ],
            ],
        ];

        $this->assertEquals($expected, $field->fieldtype()->preload()['new']['main']);
    }

    /** @test */
    public function it_gets_link_data()
    {
        tap(Facades\Collection::make('pages')->routes('/{slug}'))->save();
        EntryFactory::collection('pages')->id('1')->slug('about')->data(['title' => 'About'])->create();
        EntryFactory::collection('pages')->id('2')->slug('articles')->data(['title' => 'Articles'])->create();
        EntryFactory::collection('pages')->id('3')->slug('contact')->data(['title' => 'Contact'])->create();
        EntryFactory::collection('pages')->id('4')->slug('unused')->data(['title' => 'Unused'])->create();

        $bard = $this->bard(['save_html' => true, 'sets' => null]);

        $html = <<<'EOT'
<p>
    Paragraph
    <a href="http://google.com">External Link</a>
    <a href="statamic://entry::1">Internal Link One</a>
    <a href="statamic://entry::2">Internal Link Two</a>
    <strong>
        <a href="statamic://entry::3">Internal Link Three inside another element</a>
        <a href="statamic://entry::1">Internal Link Four thats a repeat</a>
    </strong>
</p>
EOT;

        $prosemirror = (new Augmentor($this))->renderHtmlToProsemirror($html)['content'];

        $this->assertEquals([
            'entry::1' => ['title' => 'About', 'permalink' => 'http://localhost/about'],
            'entry::2' => ['title' => 'Articles', 'permalink' => 'http://localhost/articles'],
            'entry::3' => ['title' => 'Contact', 'permalink' => 'http://localhost/contact'],
        ], $bard->getLinkData($prosemirror));
    }

    /** @test */
    public function it_doesnt_convert_statamic_asset_urls_when_saving_as_html()
    {
        $content = '[
            {"type":"text","text":"one","marks":[{"type":"link","attrs":{"target":"_blank","href":"http://google.com"}}]},
            {"type":"text","text":"two","marks":[{"type":"link","attrs":{"href":"entry::8e4b4e60-5dfb-47b0-a2d7-a904d64aeb80"}}]},
            {"type":"text","text":"three","marks":[{"type":"link","attrs":{"target":"_blank","href":"statamic://asset::assets::myst.jpeg"}}]}
        ]';

        $expected = <<<'EOT'
<a target="_blank" href="http://google.com">one</a><a href="entry::8e4b4e60-5dfb-47b0-a2d7-a904d64aeb80">two</a><a target="_blank" href="statamic://asset::assets::myst.jpeg">three</a>
EOT;

        $this->assertEquals($expected, $this->bard(['save_html' => true, 'sets' => null])->process($content));
    }

    /** @test */
    public function it_augments_statamic_asset_urls_when_stored_as_html()
    {
        Storage::fake('test', ['url' => '/assets']);
        $file = UploadedFile::fake()->image('foo/hoff.jpg', 30, 60);
        Storage::disk('test')->putFileAs('foo', $file, 'hoff.jpg');

        tap(Facades\AssetContainer::make()->handle('test_container')->disk('test'))->save();
        tap(Facades\Asset::make()->container('test_container')->path('foo/hoff.jpg'))->save();

        $bard = $this->bard(['save_html' => true, 'sets' => null]);

        $html = <<<'EOT'
<p>
    Actual asset...
    <img src="statamic://asset::test_container::foo/hoff.jpg" alt="Asset" />
    <a href="statamic://asset::test_container::foo/hoff.jpg">Asset Link</a>

    Non-existent asset...
    <a href="statamic://asset::test_container::nope.jpg">Asset Link</a>
    <img src="statamic://asset::test_container::nope.jpg" alt="Asset" />
</p>
EOT;

        $expected = <<<'EOT'
<p>
    Actual asset...
    <img src="/assets/foo/hoff.jpg" alt="Asset" />
    <a href="/assets/foo/hoff.jpg">Asset Link</a>

    Non-existent asset...
    <a href="">Asset Link</a>
    <img src="" alt="Asset" />
</p>
EOT;

        $this->assertEquals($expected, $bard->augment($html));
    }

    /** @test */
    public function it_converts_a_queryable_value()
    {
        $this->assertNull((new Bard)->toQueryableValue(null));
        $this->assertNull((new Bard)->toQueryableValue([]));
        $this->assertEquals([['foo' => 'bar']], (new Bard)->toQueryableValue([['foo' => 'bar']]));
    }

    /** @test */
    public function it_augments_inline_value()
    {
        $data = [
            ['type' => 'text', 'text' => 'This is inline text with '],
            ['type' => 'text', 'marks' => [['type' => 'bold']], 'text' => 'bold'],
            ['type' => 'text', 'text' => ' and '],
            ['type' => 'text', 'marks' => [['type' => 'italic']], 'text' => 'italic'],
            ['type' => 'text', 'text' => ' text.'],
        ];

        $expected = 'This is inline text with <strong>bold</strong> and <em>italic</em> text.';

        $this->assertEquals($expected, $this->bard(['inline' => true, 'sets' => null])->augment($data));
    }

    /** @test */
    public function it_processes_inline_value()
    {
        $data = '[{"type":"paragraph","content":[{"type":"text","text":"This is inline text."}]}]';

        $expected = [
            ['type' => 'text', 'text' => 'This is inline text.'],
        ];

        $this->assertEquals($expected, $this->bard(['inline' => true, 'sets' => null])->process($data));
    }

    /** @test */
    public function it_preprocesses_inline_value()
    {
        $data = [
            ['type' => 'text', 'text' => 'This is inline text.'],
        ];

        $expected = '[{"type":"paragraph","content":[{"type":"text","text":"This is inline text."}]}]';

        $this->assertEquals($expected, $this->bard(['inline' => true, 'sets' => null])->preProcess($data));
    }

    /** @test */
    public function it_preprocesses_inline_value_to_block_value()
    {
        $data = [
            ['type' => 'text', 'text' => 'This is inline text.'],
        ];

        $expected = '[{"type":"paragraph","content":[{"type":"text","text":"This is inline text."}]}]';

        $this->assertEquals($expected, $this->bard(['input_mode' => 'block', 'sets' => null])->preProcess($data));
    }

    /** @test */
    public function it_preprocesses_block_value_to_inline_value()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is block text.'],
                ],
            ],
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'This is some more block text.'],
                ],
            ],
        ];

        $expected = '[{"type":"paragraph","content":[{"type":"text","text":"This is block text."}]}]';

        $this->assertEquals($expected, $this->bard(['inline' => true, 'sets' => null])->preProcess($data));
    }

    /** @test */
    public function it_converts_tiptap_v1_snake_case_types_to_v2_camel_case_types()
    {
        $data = [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'This is ',
                    ],
                    [
                        'type' => 'text',
                        'marks' => [
                            ['type' => 'bold'],
                        ],
                        'text' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => ' text.',
                    ],
                ],
            ],

            [
                'type' => 'custom_node',
                'attrs' => [
                    'type' => 'custom_type_attribute', // shouldn't be camel cased
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => '123',
                    'values' => [
                        'type' => 'my_set',
                        'text' => 'test',
                    ],
                ],
            ],
            [
                'type' => 'bullet_list',
                'content' => [
                    [
                        'type' => 'list_item',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'This is a list item.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'This is ',
                    ],
                    [
                        'type' => 'text',
                        'marks' => [
                            ['type' => 'bold'],
                        ],
                        'text' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => ' text.',
                    ],
                ],
            ],
            [
                'type' => 'customNode',
                'attrs' => [
                    'type' => 'custom_type_attribute',
                ],
            ],
            [
                'type' => 'set',
                'attrs' => [
                    'id' => '123',
                    'values' => [
                        'type' => 'my_set',
                        'text' => 'test',
                    ],
                    'enabled' => true,
                ],
            ],
            [
                'type' => 'bulletList',
                'content' => [
                    [
                        'type' => 'listItem',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'This is a list item.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, json_decode($this->bard()->preProcess($data), true));
    }

    private function bard($config = [])
    {
        return (new Bard)->setField(new Field('test', array_merge(['type' => 'bard', 'sets' => ['one' => []]], $config)));
    }
}
