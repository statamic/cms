<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class LinkFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'link',
    ];

    #[Test]
    public function it_scaffolds_link_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '<a href="{{ test }}">{{ test:title }}</a>',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_link_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '<a href="{{ root:nested_group:test }}">{{ root:nested_group:test:title }}</a>',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_link_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
<a href="{{ $test }}">{{ $test['title'] }}</a>
EXPECTED;

        $this->assertSame(
            $expected,
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_link_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
<a href="{{ $root->nested_group->test }}">{{ $root->nested_group->test['title'] }}</a>
EXPECTED;

        $this->assertSame(
            $expected,
            $result,
        );
    }
}
