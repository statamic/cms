<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class FormFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'form',
    ];

    #[Test]
    public function it_scaffolds_form_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ handle /}}
    {{ title /}}
    {{ api_url /}}
    {{ honeypot /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_form_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ handle /}}
    {{ title /}}
    {{ api_url /}}
    {{ honeypot /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_form_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ form:create :in="test:handle" }}
    {{ if errors }}
        <div class="bg-red-300 text-white p-2">
            {{ errors }}
                {{ value }}<br>
            {{ /errors }}
        </div>
    {{ /if }}

    {{ if success }}
        <div class="bg-green-300 text-white p-2">
            {{ success }}
        </div>
    {{ /if }}

    {{ fields }}
        <div class="p-2">
            <label>
                {{ display }}
                {{ if validate | contains:required }}
                    <sup class="text-red">*</sup>
                {{ /if }}
            </label>
            <div class="p-1">{{ field }}</div>
            {{ if error }}
                <p class="text-gray-500">{{ error }}</p>
            {{ /if }}
        </div>
    {{ /fields }}
{{ /form:create }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_form_fieldtype_max_one_antlers_with_component_syntax()
    {
        $result = $this
            ->preferAntlersComponents()
            ->scaffoldAntlersField($this->field([
                'max_items' => 1,
            ]));

        $expected = <<<'EXPECTED'
<s:form:create :in="test:handle">
    {{ if errors }}
        <div class="bg-red-300 text-white p-2">
            {{ errors }}
                {{ value }}<br>
            {{ /errors }}
        </div>
    {{ /if }}

    {{ if success }}
        <div class="bg-green-300 text-white p-2">
            {{ success }}
        </div>
    {{ /if }}

    {{ fields }}
        <div class="p-2">
            <label>
                {{ display }}
                {{ if validate | contains:required }}
                    <sup class="text-red">*</sup>
                {{ /if }}
            </label>
            <div class="p-1">{{ field }}</div>
            {{ if error }}
                <p class="text-gray-500">{{ error }}</p>
            {{ /if }}
        </div>
    {{ /fields }}
</s:form:create>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_form_fieldtype_max_one_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ form:create :in="root:nested_group:test:handle" }}
    {{ if errors }}
        <div class="bg-red-300 text-white p-2">
            {{ errors }}
                {{ value }}<br>
            {{ /errors }}
        </div>
    {{ /if }}

    {{ if success }}
        <div class="bg-green-300 text-white p-2">
            {{ success }}
        </div>
    {{ /if }}

    {{ fields }}
        <div class="p-2">
            <label>
                {{ display }}
                {{ if validate | contains:required }}
                    <sup class="text-red">*</sup>
                {{ /if }}
            </label>
            <div class="p-1">{{ field }}</div>
            {{ if error }}
                <p class="text-gray-500">{{ error }}</p>
            {{ /if }}
        </div>
    {{ /fields }}
{{ /form:create }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_form_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $form)
    {{ $form->handle }}
    {{ $form->title }}
    {{ $form->api_url }}
    {{ $form->honeypot }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_form_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $form)
    {{ $form->handle }}
    {{ $form->title }}
    {{ $form->api_url }}
    {{ $form->honeypot }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_form_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:form:create :in="$test->handle">
    @if (count($errors) > 0)
        <div class="bg-red-300 text-white p-2">
            @foreach ($errors as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if ($success)
        <div class="bg-green-300 text-white p-2">
            {{ $success }}
        </div>
    @endif

    @foreach ($fields as $field)
        <div class="p-2">
            <label>
                {{ $field['display'] }}
                @if (in_array('required', $field['validate'] ?? []))
                    <sup class="text-red">*</sup>
                @endif
            </label>
            <div class="p-1">{!! $field['field'] !!}</div>
            @if ($field['error'])
                <p class="text-gray-500">{{ $field['error'] }}</p>
            @endif
        </div>
    @endforeach
</s:form:create>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_form_fieldtype_max_one_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
<s:form:create :in="$root->nested_group->test->handle">
    @if (count($errors) > 0)
        <div class="bg-red-300 text-white p-2">
            @foreach ($errors as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if ($success)
        <div class="bg-green-300 text-white p-2">
            {{ $success }}
        </div>
    @endif

    @foreach ($fields as $field)
        <div class="p-2">
            <label>
                {{ $field['display'] }}
                @if (in_array('required', $field['validate'] ?? []))
                    <sup class="text-red">*</sup>
                @endif
            </label>
            <div class="p-1">{!! $field['field'] !!}</div>
            @if ($field['error'])
                <p class="text-gray-500">{{ $field['error'] }}</p>
            @endif
        </div>
    @endforeach
</s:form:create>
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
