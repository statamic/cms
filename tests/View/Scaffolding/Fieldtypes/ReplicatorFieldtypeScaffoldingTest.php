<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class ReplicatorFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'replicator',
    ];

    protected array $fieldWithSets = [
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
    public function it_scaffolds_replicator_fieldtype_without_sets_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}

{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_replicator_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field($this->fieldWithSets));

        $expected = <<<'EXPECTED'
{{ test }}
    {{ if type == 'new_set' }}
        {{ bard_field /}}
    {{ /if }}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_replicator_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField($this->fieldWithSets));

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ if type == 'new_set' }}
        {{ bard_field /}}
    {{ /if }}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_replicator_fieldtype_without_sets_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}

{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_replicator_fieldtype_without_sets_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $set)

@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_replicator_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field($this->fieldWithSets));

        $expected = <<<'EXPECTED'
@foreach ($test as $set)
    @if($set->type == 'new_set')
        {!! $set->bard_field !!}
    @endif
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_replicator_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField($this->fieldWithSets));

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $set)
    @if($set->type == 'new_set')
        {!! $set->bard_field !!}
    @endif
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_replicator_fieldtype_without_sets_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $set)

@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
