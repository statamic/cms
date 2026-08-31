<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class UsersFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'users',
    ];

    protected function makeUserBlueprint(): void
    {
        $user = Blueprint::makeFromFields([
            'name' => [
                'type' => 'text',
            ],
            'coworker' => [
                'type' => 'users',
                'max_items' => 1,
            ],
        ]);

        BlueprintRepository::shouldReceive('find')
            ->with('user')->andReturn($user);
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}

{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_users_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}

{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_antlers_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ name /}}
    {{ coworker }}
        {{# Recursive user fields for coworker #}}
    {{ /coworker }}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_users_fieldtype_antlers_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ name /}}
    {{ coworker }}
        {{# Recursive user fields for coworker #}}
    {{ /coworker }}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ test }}

{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_uers_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}

{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_max_one_antlers_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ test }}
    {{ name /}}
    {{ coworker }}
        {{# Recursive user fields for coworker #}}
    {{ /coworker }}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_uers_fieldtype_max_one_antlers_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ name /}}
    {{ coworker }}
        {{# Recursive user fields for coworker #}}
    {{ /coworker }}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $user)

@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_blade_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $user)
    {{ $user->name }}
    {{-- Recursive user fields for $user->coworker --}}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $this->assertSame(
            '',
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_users_fieldtype_max_one_blade_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $test->name }}
{{-- Recursive user fields for $test->coworker --}}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_users_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $user)

@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_uers_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_scaffolds_nested_users_fieldtype_blade_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $user)
    {{ $user->name }}
    {{-- Recursive user fields for $user->coworker --}}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_uers_fieldtype_max_one_blade_with_user_blueprint()
    {
        $this->makeUserBlueprint();
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $root->nested_group->test->name }}
{{-- Recursive user fields for $root->nested_group->test->coworker --}}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
