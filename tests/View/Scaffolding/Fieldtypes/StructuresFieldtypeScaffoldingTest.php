<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class StructuresFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'structures',
    ];

    #[Test]
    public function it_scaffolds_structures_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    <ul>
        {{ nav :handle="handle" }}
            <li><a href="{{ url }}">{{ title }}</a></li>
        {{ /nav }}
    </ul>
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_structures_fieldtype_antlers_with_component_syntax()
    {
        $result = $this
            ->preferAntlersComponents()
            ->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    <ul>
        <s:nav :handle="handle">
            <li><a href="{{ url }}">{{ title }}</a></li>
        </s:nav>
    </ul>
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_structures_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    <ul>
        {{ nav :handle="handle" }}
            <li><a href="{{ url }}">{{ title }}</a></li>
        {{ /nav }}
    </ul>
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_structures_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<ul>
    {{ nav :handle="test:handle" }}
        <li><a href="{{ url }}">{{ title }}</a></li>
    {{ /nav }}
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_structures_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<ul>
    {{ nav :handle="root:nested_group:test:handle" }}
        <li><a href="{{ url }}">{{ title }}</a></li>
    {{ /nav }}
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_structures_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $nav)
    <ul>
        <s:nav :handle="nav">
            <li><a href="{{ $url }}">{{ $title }}</a></li>
        </s:nav>
    </ul>
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_structures_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $nav)
    <ul>
        <s:nav :handle="nav">
            <li><a href="{{ $url }}">{{ $title }}</a></li>
        </s:nav>
    </ul>
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_structures_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<ul>
    <s:nav :handle="$test">
        <li><a href="{{ $url }}">{{ $title }}</a></li>
    </s:nav>
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_structures_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<ul>
    <s:nav :handle="$root->nested_group->test">
        <li><a href="{{ $url }}">{{ $title }}</a></li>
    </s:nav>
</ul>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
