<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class DateFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'date',
    ];

    #[Test]
    public function it_scaffolds_date_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '{{ test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_date_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '{{ root:nested_group:test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_date_fieldtype_range_antlers()
    {
        $field = $this->field([
            'mode' => 'range',
        ]);

        $result = $this->scaffoldAntlersField($field);

        $this->assertSame(
            '{{ test:start /}}{{ test:end /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_date_fieldtype_range_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'mode' => 'range',
        ]));

        $this->assertSame(
            '{{ root:nested_group:test:start /}}{{ root:nested_group:test:end /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_date_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $this->assertSame(
            '{{ $test }}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_date_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $this->assertSame(
            '{{ $root->nested_group->test }}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_date_fieldtype_range_blade()
    {
        $field = $this->field([
            'mode' => 'range',
        ]);

        $result = $this->scaffoldBladeField($field);

        $expected = <<<'EXPECTED'
{{ $test['start'] }}
{{ $test['end'] }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_date_fieldtype_range_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'mode' => 'range',
        ]));

        $expected = <<<'EXPECTED'
{{ $root->nested_group->test['start'] }}
{{ $root->nested_group->test['end'] }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
