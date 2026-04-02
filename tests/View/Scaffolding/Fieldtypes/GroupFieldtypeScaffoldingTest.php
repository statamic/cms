<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class GroupFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'group',
        'fields' => [
            [
                'handle' => 'text_one',
                'field' => [
                    'type' => 'text',
                    'display' => 'Text one',
                ],
            ],
            [
                'handle' => 'nested_group',
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
    public function it_scaffolds_group_fieldtype_antlers()
    {
        $field = $this->field();

        $result = $this->scaffoldAntlersField($field);

        $expected = <<<'EXPECTED'
{{ test:text_one /}}
{{ test:nested_group:text_two /}}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_group_fieldtype_blade()
    {
        $field = $this->field();

        $result = $this->scaffoldBladeField($field);

        $expected = <<<'EXPECTED'
{{ $test->text_one }}
{{ $test->nested_group->text_two }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
