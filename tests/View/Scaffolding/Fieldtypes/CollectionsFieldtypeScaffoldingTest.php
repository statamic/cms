<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class CollectionsFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'collections',
    ];

    #[Test]
    public function it_scaffolds_collections_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ collection from="{test|piped}" }}
    {{ title /}}
{{ /collection }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_collections_fieldtype_antlers_with_component_syntax()
    {
        $result = $this
            ->preferAntlersComponents()
            ->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
<s:collection from="{test|piped}">
    {{ title /}}
</s:collection>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_collections_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ collection from="{test}" }}
    {{ title /}}
{{ /collection }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_collections_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ collection from="{root:nested_group:test|piped}" }}
    {{ title /}}
{{ /collection }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_collections_max_one_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ collection from="{root:nested_group:test}" }}
    {{ title /}}
{{ /collection }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_collections_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
<s:collection :from="$test ?? []">
    {{ $title }}
</s:collection>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_collections_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
<s:collection :from="$root->nested_group->test ?? []">
    {{ $title }}
</s:collection>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_collections_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:collection :from="$test">
    {{ $title }}
</s:collection>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_collections_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:collection :from="$root->nested_group->test">
    {{ $title }}
</s:collection>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
