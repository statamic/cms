<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class UserGroupsFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'user_groups',
    ];

    #[Test]
    public function it_scaffolds_user_groups_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ handle /}}
    {{ title /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_user_groups_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ handle /}}
    {{ title /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_user_groups_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ user:in :group="test:handle" }}
    User belongs to the {{ test:title /}} group.
{{ /user:in }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_user_groups_fieldtype_max_one_antlers_with_component_syntax()
    {
        $result = $this
            ->preferAntlersComponents()
            ->scaffoldAntlersField($this->field([
                'max_items' => 1,
            ]));

        $expected = <<<'EXPECTED'
<s:user:in :group="test:handle">
    User belongs to the {{ test:title /}} group.
</s:user:in>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_user_groups_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ user:in :group="root:nested_group:test:handle" }}
    User belongs to the {{ root:nested_group:test:title /}} group.
{{ /user:in }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_user_groups_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $group)
    {{ $group->handle }}
    {{ $group->title }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_user_groups_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
@if(Statamic::tag('user:in')->group($test->handle)->fetch())
    User belongs to the {{ $test->title }} group.
@endif
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_user_groups_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $group)
    {{ $group->handle }}
    {{ $group->title }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_user_groups_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
@if(Statamic::tag('user:in')->group($root->nested_group->test->handle)->fetch())
    User belongs to the {{ $root->nested_group->test->title }} group.
@endif
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
