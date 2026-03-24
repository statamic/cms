<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class TaxonomiesFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'taxonomies',
    ];

    #[Test]
    public function it_scaffolds_taxonomies_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ taxonomy from="{test|piped}" }}
    {{ title /}}
{{ /taxonomy }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_taxonomies_fieldtype_antlers_with_component_syntax()
    {
        $result = $this
            ->preferAntlersComponents()
            ->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
<s:taxonomy from="{test|piped}">
    {{ title /}}
</s:taxonomy>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_taxonomies_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ taxonomy from="{test}" }}
    {{ title /}}
{{ /taxonomy }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_taxonomies_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ taxonomy from="{root:nested_group:test|piped}" }}
    {{ title /}}
{{ /taxonomy }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_taxonomies_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ taxonomy from="{root:nested_group:test}" }}
    {{ title /}}
{{ /taxonomy }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_taxonomies_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
<s:taxonomy :from="$test ?? []">
    {{ $title }}
</s:taxonomy>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_taxonomies_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:taxonomy :from="$test">
    {{ $title }}
</s:taxonomy>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_taxonomies_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
<s:taxonomy :from="$root->nested_group->test ?? []">
    {{ $title }}
</s:taxonomy>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_taxonomies_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:taxonomy :from="$root->nested_group->test">
    {{ $title }}
</s:taxonomy>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
