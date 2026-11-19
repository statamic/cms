<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class GridFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'grid',
        'fields' => [
            [
                'handle' => 'text_one',
                'field' => [
                    'type' => 'text',
                    'display' => 'Text one',
                ],
            ],
            [
                'handle' => 'nested_field',
                'field' => [
                    'type' => 'group',
                    'fields' => [
                        [
                            'handle' => 'text_two',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    #[Test]
    public function it_scaffolds_grid_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ text_one /}}
    {{ text_two /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_grid_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ text_one /}}
    {{ text_two /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_grid_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $test_item)
    {{ $test_item->text_one }}
    {{ $test_item->nested_field->text_two }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_grid_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $test_item)
    {{ $test_item->text_one }}
    {{ $test_item->nested_field->text_two }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
