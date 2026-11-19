<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class BardFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'bard',
    ];

    protected array $fieldWithSets = [
        'type' => 'bard',
        'sets' => [
            'new_set_group' => [
                'display' => 'Set Group',
                'sets' => [
                    'new_set' => [
                        'fields' => [
                            [
                                'handle' => 'bard_field',
                                'field' => [
                                    'type' => 'bard',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    #[Test]
    public function it_scaffolds_bard_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '{{ test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_bard_fieldtype_with_sets_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field($this->fieldWithSets));

        $expected = <<<'EXPECTED'
{{ test }}
    {{ if type == 'new_set' }}
        {{ bard_field /}}
    {{ elseif type == 'text' }}
        {{ text /}}
    {{ /if }}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_bard_fieldtype_with_sets_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField($this->fieldWithSets));

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ if type == 'new_set' }}
        {{ bard_field /}}
    {{ elseif type == 'text' }}
        {{ text /}}
    {{ /if }}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_bard_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '{{ root:nested_group:test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_bard_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $this->assertSame(
            '{!! $test !!}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_bard_field_with_sets_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField($this->fieldWithSets));

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $set)
    @if($set->type == 'new_set')
        {!! $set->bard_field !!}
    @elseif($set->type == 'text')
        {!! $set->text !!}
    @endif
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_bard_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $this->assertSame(
            '{!! $root->nested_group->test !!}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_bard_field_with_sets_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField($this->fieldWithSets));

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $set)
    @if($set->type == 'new_set')
        {!! $set->bard_field !!}
    @elseif($set->type == 'text')
        {!! $set->text !!}
    @endif
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
