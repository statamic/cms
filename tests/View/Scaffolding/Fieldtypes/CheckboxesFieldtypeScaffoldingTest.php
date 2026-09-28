<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class CheckboxesFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'checkboxes',
    ];

    #[Test]
    public function it_scaffolds_checkboxes_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ value /}}
    {{ label /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function test_it_scaffolds_nested_checkboxes_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ value /}}
    {{ label /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_checkboxes_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $test_item)
    {{ $test_item['value'] }}
    {{ $test_item['label'] }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_checkboxes_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $test_item)
    {{ $test_item['value'] }}
    {{ $test_item['label'] }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
