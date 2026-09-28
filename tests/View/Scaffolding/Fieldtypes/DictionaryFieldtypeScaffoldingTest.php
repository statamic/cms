<?php

namespace Tests\View\Scaffolding\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\View\Scaffolding\ScaffoldingTestCase;

class DictionaryFieldtypeScaffoldingTest extends ScaffoldingTestCase
{
    protected array $field = [
        'type' => 'dictionary',
        'dictionary' => [
            'type' => 'countries',
        ],
    ];

    #[Test]
    public function it_scaffolds_dictionary_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->field());

        $expected = <<<'EXPECTED'
{{ test }}
    {{ name /}}
    {{ iso3 /}}
    {{ iso2 /}}
    {{ region /}}
    {{ subregion /}}
    {{ emoji /}}
    {{ label /}}
    {{ value /}}
{{ /test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_dictionary_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ test:name /}}
{{ test:iso3 /}}
{{ test:iso2 /}}
{{ test:region /}}
{{ test:subregion /}}
{{ test:emoji /}}
{{ test:label /}}
{{ test:value /}}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_dictionary_fieldtype_antlers()
    {
        $result = $this->scaffoldAntlersField($this->nestedField());

        $expected = <<<'EXPECTED'
{{ root:nested_group:test }}
    {{ name /}}
    {{ iso3 /}}
    {{ iso2 /}}
    {{ region /}}
    {{ subregion /}}
    {{ emoji /}}
    {{ label /}}
    {{ value /}}
{{ /root:nested_group:test }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_dictionary_fieldtype_antlers_max_one()
    {
        $result = $this->scaffoldAntlersField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ root:nested_group:test:name /}}
{{ root:nested_group:test:iso3 /}}
{{ root:nested_group:test:iso2 /}}
{{ root:nested_group:test:region /}}
{{ root:nested_group:test:subregion /}}
{{ root:nested_group:test:emoji /}}
{{ root:nested_group:test:label /}}
{{ root:nested_group:test:value /}}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_dictionary_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->field());

        $expected = <<<'EXPECTED'
@foreach ($test as $country)
    {{ $country->name }}
    {{ $country->iso3 }}
    {{ $country->iso2 }}
    {{ $country->region }}
    {{ $country->subregion }}
    {{ $country->emoji }}
    {{ $country->label }}
    {{ $country->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_dictionary_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->field([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $test->name }}
{{ $test->iso3 }}
{{ $test->iso2 }}
{{ $test->region }}
{{ $test->subregion }}
{{ $test->emoji }}
{{ $test->label }}
{{ $test->value }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_dictionary_fieldtype_blade()
    {
        $result = $this->scaffoldBladeField($this->nestedField());

        $expected = <<<'EXPECTED'
@foreach ($root->nested_group->test as $country)
    {{ $country->name }}
    {{ $country->iso3 }}
    {{ $country->iso2 }}
    {{ $country->region }}
    {{ $country->subregion }}
    {{ $country->emoji }}
    {{ $country->label }}
    {{ $country->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_nested_dictionary_fieldtype_blade_max_one()
    {
        $result = $this->scaffoldBladeField($this->nestedField([
            'max_items' => 1,
        ]));

        $expected = <<<'EXPECTED'
{{ $root->nested_group->test->name }}
{{ $root->nested_group->test->iso3 }}
{{ $root->nested_group->test->iso2 }}
{{ $root->nested_group->test->region }}
{{ $root->nested_group->test->subregion }}
{{ $root->nested_group->test->emoji }}
{{ $root->nested_group->test->label }}
{{ $root->nested_group->test->value }}
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_countries_dictionary()
    {
        $result = $this->scaffoldBladeField($this->field([
            'dictionary' => [
                'type' => 'countries',
            ],
        ]));

        $expected = <<<'EXPECTED'
@foreach ($test as $country)
    {{ $country->name }}
    {{ $country->iso3 }}
    {{ $country->iso2 }}
    {{ $country->region }}
    {{ $country->subregion }}
    {{ $country->emoji }}
    {{ $country->label }}
    {{ $country->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_currencies_dictionary()
    {
        $result = $this->scaffoldBladeField($this->field([
            'dictionary' => [
                'type' => 'currencies',
            ],
        ]));

        $expected = <<<'EXPECTED'
@foreach ($test as $currency)
    {{ $currency->code }}
    {{ $currency->name }}
    {{ $currency->symbol }}
    {{ $currency->decimals }}
    {{ $currency->label }}
    {{ $currency->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_languages_dictionary()
    {
        $result = $this->scaffoldBladeField($this->field([
            'dictionary' => [
                'type' => 'languages',
            ],
        ]));

        $expected = <<<'EXPECTED'
@foreach ($test as $language)
    {{ $language->code }}
    {{ $language->name }}
    {{ $language->label }}
    {{ $language->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_locales_dictionary()
    {
        $result = $this->scaffoldBladeField($this->field([
            'dictionary' => [
                'type' => 'locales',
            ],
        ]));

        $expected = <<<'EXPECTED'
@foreach ($test as $locale)
    {{ $locale->name }}
    {{ $locale->label }}
    {{ $locale->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }

    #[Test]
    public function it_scaffolds_timezones_dictionary()
    {
        $result = $this->scaffoldBladeField($this->field([
            'dictionary' => [
                'type' => 'timezones',
            ],
        ]));

        $expected = <<<'EXPECTED'
@foreach ($test as $timezone)
    {{ $timezone->name }}
    {{ $timezone->offset }}
    {{ $timezone->label }}
    {{ $timezone->value }}
@endforeach
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            $result,
        );
    }
}
