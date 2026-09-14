<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class ButtonGroupFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'button_group',
    ];

    #[Test]
    public function it_scaffolds_button_group_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $this->assertSame(
            '{{ test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_button_group_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $this->assertSame(
            '{{ root:nested_group:test /}}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_button_group_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $this->assertSame(
            '{{ $test }}',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_button_group_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $this->assertSame(
            '{{ $root->nested_group->test }}',
            $result,
        );
    }
}
