<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class SelectFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'select',
    ];

    #[Test]
    public function it_scaffolds_select_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '{{ test /}} {{ test:label /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_select_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '{{ root:nested_group:test /}} {{ root:nested_group:test:label /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_select_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
{{ $test }}
{{ $test['label'] }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_select_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ $root->nested_group->test }}
{{ $root->nested_group->test['label'] }}
EXPECTED;

        $this->assertSame(
            $expected,
            $result,
        );
    }
}
