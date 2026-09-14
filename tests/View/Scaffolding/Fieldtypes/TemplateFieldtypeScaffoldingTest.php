<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class TemplateFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'template',
    ];

    #[Test]
    public function it_scaffolds_template_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '{{ test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_template_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '{{ root:nested_group:test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_template_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $this->assertSame(
            '{{ $test }}',
            $result,
        );
    }

    public function it_scaffolds_nested_template_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $this->assertSame(
            '{{ $root->nested_group->test }}',
            $result,
        );
    }
}
