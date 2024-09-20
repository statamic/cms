<?php

namespace Tests\Antlers\Runtime;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Query\Builder;
use Statamic\Contracts\Support\Boolable;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Entry;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Tags\Tags;
use Statamic\View\Cascade;
use Tests\Antlers\Fixtures\Addon\Tags\RecursiveChildren;
use Tests\Antlers\ParserTestCase;
use Tests\Factories\EntryFactory;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class TemplateTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private $variables;

    public function setUp(): void
    {
        parent::setUp();

        $this->variables = [
            'default_key' => 'two',
            'first_key' => 'three',
            'second_key' => 'deep',
            'good' => true,
            'bad' => false,
            'unknown' => null,
            'string' => 'Hello wilderness',
            'simple' => ['one', 'two', 'three'],
            'complex' => [
                ['string' => 'the first string'],
                ['string' => 'the second string'],
            ],
            'complex_string' => 'Hello wildernesses',
            'associative' => [
                'one' => 'hello',
                'two' => 'wilderness',
                'three' => [
                    'deep' => 'Very deep',
                ],
            ],
            'date' => 'June 19 2012',
            'content' => 'Paragraph',
        ];
    }

    #[Test]
    public function string_variable()
    {
        $template = '{{ string }}';

        $this->assertEquals('Hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function string_variables_with_tight_braces()
    {
        $template = '{{string}}';

        $this->assertEquals('Hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function array_variables()
    {
        $template = <<<'EOT'
before
{{ simple }}
    {{ value }}, {{ count or "0" }}, {{ index or "0" }}, {{ total_results }}
    {{ if first }}first{{ elseif last }}last{{ else }}neither{{ /if }}


{{ /simple }}
after
EOT;

        $expected = <<<'EOT'
before

    one, 1, 0, 3
    first



    two, 2, 1, 3
    neither



    three, 3, 2, 3
    last



after
EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables));

        $this->assertEquals('wilderness', $this->renderString('{{ associative[default_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->renderString('{{ associative[first_key][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->renderString('{{ associative[\'three\'][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->renderString('{{ associative["three"][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->renderString('{{ associative.three[second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->renderString('{{ associative:three[second_key] }}', $this->variables));
    }

    #[Test]
    public function complex_array_variable()
    {
        $template = <<<'EOT'
before
{{ complex }}
    {{ string }}, {{ count or "0" }}, {{ index or "0" }}, {{ total_results }}
    {{ if first }}first{{ elseif last }}last{{ else }}neither{{ /if }}


{{ /complex }}
after
EOT;

        $expected = <<<'EOT'
before

    the first string, 1, 0, 2
    first



    the second string, 2, 1, 2
    last



after
EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables));
    }

    #[Test]
    public function associative_array_variable()
    {
        $template = <<<'EOT'
before
{{ associative }}
    {{ one }}
    {{ two }}
    {{ value or "no value" }}
    {{ key or "no key" }}
    {{ count or "no count" }}
    {{ index or "no index" }}
    {{ total_results or "no total_results" }}
    {{ first or "no first" }}
    {{ last or "no last" }}
{{ /associative }}
after
EOT;

        $expected = <<<'EOT'
before

    hello
    wilderness
    no value
    no key
    no count
    no index
    no total_results
    no first
    no last

after
EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables));
    }

    #[Test]
    public function scope_glue()
    {
        $template = '{{ associative:one }} {{ associative.two }}';

        $this->assertEquals('hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function non_existent_variables_should_be_null()
    {
        $template = '{{ missing }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function accessing_strings_as_arrays_returns_null()
    {
        $this->assertEquals('bar, ><', $this->renderString('{{ foo }}, >{{ foo:test }}<', ['foo' => 'bar']));
    }

    #[Test]
    public function accessing_string_as_array_which_exists_as_callback_calls_the_callback()
    {
        (new class extends Tags
        {
            public static $handle = 'foo';

            public function test()
            {
                return 'callback';
            }
        })::register();

        $this->assertEquals('bar, callback', $this->renderString('{{ foo }}, {{ foo:test }}', ['foo' => 'bar'], true));
    }

    #[Test]
    public function non_arrays_cannot_be_looped()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ string }}', [
                'line' => 1, 'file' => '',
            ]);

        $template = '{{ string }} {{ /string }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function static_strings_with_double_quotes_should_be_left_alone()
    {
        $template = '{{ "Thundercats are Go!" }}';

        $this->assertEquals('Thundercats are Go!', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function static_strings_with_single_quotes_should_be_left_alone()
    {
        $template = "{{ 'Thundercats are Go!' }}";

        $this->assertEquals('Thundercats are Go!', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function static_strings_with_double_quotes_can_be_modified()
    {
        $template = '{{ "Thundercats are Go!" | upper }}';

        $this->assertEquals('THUNDERCATS ARE GO!', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function static_strings_with_single_quotes_can_be_modified()
    {
        $template = "{{ 'Thundercats are Go!' | upper }}";

        $this->assertEquals('THUNDERCATS ARE GO!', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_braces_should_not_be_parsed()
    {
        $template = '{string}';

        $this->assertEquals('{string}', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function modified_non_existent_variables_should_be_null()
    {
        $template = '{{ missing|upper }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function unclosed_array_variable_pairs_should_be_null()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an array variable as a string: {{ simple }}', [
                'line' => 1, 'file' => '',
            ]);

        $template = '{{ simple }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function unclosed_array_variable_does_not_report_warning_if_followed_by_ray_modifier()
    {
        Log::shouldReceive('debug')->never();

        $template = '{{ simple | ray }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function unclosed_array_variable_does_reports_warning_even_if_a_call_before_it_did_not()
    {
        // Test case to ensure the modifier state is cleared correctly.

        Log::shouldReceive('debug')->once()
            ->with('Cannot render an array variable as a string: {{ simple }}', [
                'line' => 1, 'file' => '',
            ]);

        $template = '{{ simple | ray }}{{ simple }}';

        $this->assertEquals('', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_condition()
    {
        $template = '{{ if string == "Hello wilderness" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function multiple_and_conditions()
    {
        $template = '{{ if string == "Hello wilderness" && content }}yes{{ endif }}';

        $this->assertEquals('yes', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function multiple_or_conditions()
    {
        $should_pass = '{{ if string == "failure" || string == "Hello wilderness" }}yes{{ endif }}';
        $should_fail = '{{ if string == "failure" or string == "womp" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->renderString($should_pass, $this->variables));
        $this->assertEquals('', $this->renderString($should_fail, $this->variables));
    }

    #[Test]
    public function or_existence_conditions()
    {
        $should_pass = '{{ if string || strudel }}yes{{ endif }}';
        $should_also_pass = '{{ if strudel or string }}yes{{ endif }}';
        $should_fail = '{{ if strudel || wurst }}yes{{ endif }}';
        $should_also_fail = '{{ if strudel or wurst }}yes{{ endif }}';

        $this->assertEquals('yes', $this->renderString($should_pass, $this->variables));
        $this->assertEquals('yes', $this->renderString($should_also_pass, $this->variables));
        $this->assertEquals('', $this->renderString($should_fail, $this->variables));
        $this->assertEquals('', $this->renderString($should_also_fail, $this->variables));
    }

    #[Test]
    public function conditions_on_overlapping_variable_names()
    {
        $template = '{{ if complex }}{{ complex limit="1" }}{{ string }}{{ /complex }}{{ /if }}';

        $this->assertEquals('the first string', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function loop_with_param_inside_condition_matching_variable_name()
    {
        $template = '{{ if complex_string }}{{ complex_string }}{{ /if }}{{ complex }}{{ /complex }}';

        $this->assertEquals('Hello wildernesses', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition()
    {
        $template = '{{ string ? "Pass" : "Fail" }}';

        $this->assertEquals('Pass', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_with_dynamic_array()
    {
        $template = '{{ associative[default_key] ? "Pass" : "Fail" }}';

        $this->assertEquals('Pass', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_isnt_too_greedy()
    {
        $template = '{{ content }} {{ string ? "Pass" : "Fail" }} {{ content }}';

        $this->assertEquals('Paragraph Pass Paragraph', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_with_a_variable()
    {
        $template = '{{ string ? string : "Fail" }}';

        $this->assertEquals('Hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_with_modifiers()
    {
        $template = '{{ string ? string | upper : "Fail" }}';

        $this->assertEquals('HELLO WILDERNESS', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_with_modifiers_and_dynamic_array()
    {
        $template = '{{ string ? associative[default_key] | upper : "Fail" }}';

        $this->assertEquals('WILDERNESS', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_condition_with_multiple_lines()
    {
        $template = <<<'EOT'
{{ string
    ? "Pass"
    : "Fail" }}
EOT;

        $this->assertEquals('Pass', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function ternary_escapes_quotes_properly()
    {
        $data = ['condition' => true, 'var' => '"Wow" said the man'];
        $template = '{{ condition ? var : "nah" }}';

        $this->assertEquals('"Wow" said the man', $this->renderString($template, $data));
    }

    #[Test]
    public function ternary_condition_inside_parameter()
    {
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\TestTags::class;

        $this->assertEquals('yes', $this->renderString(
            "{{ test variable='{{ good ? 'yes' : 'fail' }}' }}",
            $this->variables,
            true
        ));

        $this->assertEquals('fail', $this->renderString(
            "{{ test variable='{{ bad ? 'yes' : 'fail' }}' }}",
            $this->variables,
            true
        ));

        $this->assertEquals('fail', $this->renderString(
            "{{ test variable='{{ unknown ?? 'fail' }}' }}",
            $this->variables,
            true
        ));

        $this->assertEquals('yes', $this->renderString(
            "{{ test variable='{{ !unknown ? 'yes' : 'fail' }}' }}",
            $this->variables,
            true
        ));
    }

    #[Test]
    #[DataProvider('boolablesInTernaryProvider')]
    public function ternary_condition_with_boolables_supplied_to_tags_resolve_correctly($value, $expected)
    {
        $this->withFakeViews();

        $this->viewShouldReturnRaw('test', "{{ the_field ? 'true' : 'false' }}");

        $template = <<<'EOT'
view: {{ the_field ? 'true' : 'false' }}, partial: {{ partial:test :the_field="the_field" }}
EOT;

        $this->assertSame($expected, $this->renderString($template, ['the_field' => $value], true));
    }

    public static function boolablesInTernaryProvider()
    {
        return [
            'truthy generic boolable' => [
                new class implements Boolable
                {
                    public function toBool(): bool
                    {
                        return true;
                    }
                },
                'view: true, partial: true',
            ],
            'falsey generic boolable' => [
                new class implements Boolable
                {
                    public function toBool(): bool
                    {
                        return false;
                    }
                },
                'view: false, partial: false',
            ],
            'truthy LabeledValue' => [
                new LabeledValue('foo', 'Foo'),
                'view: true, partial: true',
            ],
            'falsey LabeledValue' => [
                new LabeledValue(null, null),
                'view: false, partial: false',
            ],
        ];
    }

    #[Test]
    public function null_coalescence()
    {
        // or, ?:, and ?? are all aliases.
        // while ?: and ?? have slightly different behaviors in php, they work the same in antlers.

        $this->assertEquals('Hello wilderness', $this->renderString('{{ string or "Pass" }}', $this->variables));
        $this->assertEquals('Hello wilderness', $this->renderString('{{ string ?: "Pass" }}', $this->variables));
        $this->assertEquals('Hello wilderness', $this->renderString('{{ string ?? "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing ?? "Pass" }}', $this->variables));

        $this->assertEquals('Pass', $this->renderString('{{ missing or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing ?? "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing[thing] or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing[thing] ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ missing[thing] ?? "Pass" }}', $this->variables));
    }

    #[Test]
    public function truth_coalescing()
    {
        $this->assertEquals('Pass', $this->renderString('{{ string ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ missing[thing] ?= "Pass" }}', $this->variables));

        // Negating with !
        $this->assertEquals('', $this->renderString('{{ !string ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ !associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ !associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ !missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ !missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ !missing[thing] ?= "Pass" }}', $this->variables));

        // and with spaces
        $this->assertEquals('', $this->renderString('{{ ! string ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ ! associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->renderString('{{ ! associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ ! missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ ! missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->renderString('{{ ! missing[thing] ?= "Pass" }}', $this->variables));
    }

    #[Test]
    public function truth_coalescing_inside_loop()
    {
        $template = '{{ complex }}{{ first ?= "Pass" }}{{ /complex }}';

        $this->assertEquals('Pass', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_standard_string_modifier_tight()
    {
        $template = '{{ string|upper }}';

        $this->assertEquals('HELLO WILDERNESS', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_standard_string_modifiers_tight()
    {
        $template = '{{ string|upper|lower }}';

        $this->assertEquals('hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_standard_string_modifier_relaxed()
    {
        $template = '{{ string | upper }}';

        $this->assertEquals('HELLO WILDERNESS', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_standard_string_modifiers_relaxed()
    {
        $template = '{{ string | upper | lower }}';

        $this->assertEquals('hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_standard_string_modifiers_from_dynamic_array_relaxed()
    {
        $template = '{{ associative[default_key] | upper | lower }}';

        $this->assertEquals('wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_parameter_string_modifier()
    {
        $template = "{{ string upper='true' }}";

        $this->assertEquals('HELLO WILDERNESS', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_parameter_string_from_array_modifier()
    {
        $this->assertEquals(
            'WILDERNESS',
            $this->renderString("{{ associative.two upper='true' }}", $this->variables)
        );

        $this->assertEquals(
            'WILDERNESS',
            $this->renderString("{{ associative['two'] upper='true' }}", $this->variables)
        );

        $this->assertEquals(
            'WILDERNESS',
            $this->renderString("{{ associative[default_key] upper='true' }}", $this->variables)
        );
    }

    #[Test]
    public function chained_parameter_string_modifiers()
    {
        $template = "{{ string upper='true' lower='true' }}";

        $this->assertEquals('hello wilderness', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_standard_array_modifier_tight()
    {
        $template = '{{ simple|length }}';

        $this->assertEquals('3', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function single_standard_array_modifier_relaxed()
    {
        $template = '{{ simple | length }}';

        $this->assertEquals('3', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_standard_array_modifiers_tight_on_content()
    {
        $template = '{{ content|markdown|lower }}';

        $this->assertEquals("<p>paragraph</p>\n", $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_standard_modifiers_relaxed_on_content()
    {
        $template = '{{ content | markdown | lower }}';

        $this->assertEquals("<p>paragraph</p>\n", $this->renderString($template, $this->variables));
    }

    #[Test]
    public function chained_parameter_modifiers_on_content()
    {
        $template = "{{ content markdown='true' lower='true' }}";

        $this->assertEquals("<p>paragraph</p>\n", $this->renderString($template, $this->variables));
    }

    #[Test]
    public function conditions_with_modifiers()
    {
        $template = "{{ if string|upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals('yes', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function conditions_with_relaxed_modifiers()
    {
        $template = "{{ if string | upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals('yes', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function tags_with_curlies_in_params_gets_parsed()
    {
        // the variables are inside Test@index
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\TestTags::class;

        $template = "{{ test variable='{string}' }}";

        $this->assertEquals('Hello wilderness', $this->renderString($template, $this->variables, true));
    }

    #[Test]
    public function date_condition_with_chained_relaxed_modifiers_with_spaces_in_arguments()
    {
        $template = '{{ if (date | modify_date:+3 years | format:Y) == "2015" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function array_modifiers_get_parsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', $this->renderString($template, $this->variables));
    }

    #[Test]
    public function array_modifiers_on_collections_get_parsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', $this->renderString($template, [
            'simple' => collect(['one', 'two', 'three']),
        ]));
    }

    #[Test]
    public function recursive_children()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = RecursiveChildren::class;

        $template = '<ul>{{ recursive_children }}<li>{{ title }}.{{ foo }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One.Bar<ul><li>Two.Bar</li><li>Three.Bar<ul><li>Four.Baz</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, $this->renderString($template, ['foo' => 'Bar'], true));
    }

    #[Test]
    public function recursive_children_with_scope()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = RecursiveChildren::class;

        $template = '<ul>{{ recursive_children scope="item" }}<li>{{ item:title }}.{{ item:foo }}.{{ foo }}{{ if item:children }}<ul>{{ *recursive item:children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One..Bar<ul><li>Two..Bar</li><li>Three..Bar<ul><li>Four.Baz.Baz</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, $this->renderString($template, ['foo' => 'Bar'], true));
    }

    #[Test]
    public function empty_values_are_not_overridden_by_previous_iteration()
    {
        $variables = [
            'loop' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]',
                ],
            ],
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            $this->renderString('{{ loop }}{{ one }}{{ two }}{{ /loop }}', $variables)
        );
    }

    #[Test]
    public function empty_values_are_not_overridden_by_previous_iteration_with_parsing()
    {
        $this->app['statamic.tags']['test'] = \Tests\Antlers\Fixtures\Addon\Tags\TestTags::class;

        // Variable name was changed from "loop" to "loopvar" compared to the original test to avoid a
        // headache since the base test class is going to be loading all of the core Statamic Tags.
        $variables = [
            'loopvar' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]',
                ],
            ],
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            $this->renderString('{{ loopvar }}{{ one }}{{ test:some_parsing var="two" }}{{ two }}{{ /test:some_parsing }}{{ /loopvar }}', $variables, true)
        );
    }

    #[Test]
    public function nested_array_syntax()
    {
        $variables = [
            'hello' => [
                'world' => [
                    ['baz' => 'one'],
                    ['baz' => 'two'],
                ],
                'id' => '12345',
            ],
        ];

        $this->assertEquals(
            '[one][two]',
            $this->renderString('{{ hello:world }}[{{ baz }}]{{ /hello:world }}', $variables)
        );

        $this->assertEquals(
            '[one][two]',
            $this->renderString('{{ hello:world scope="s" }}[{{ s:baz }}]{{ /hello:world }}', $variables)
        );
    }

    #[Test]
    public function it_parses_php_when_enabled()
    {
        $this->assertEquals(
            'Hello wilderness!',
            $this->parser($this->variables)->allowPhp()->parse('{{ string }}<?php echo "!"; ?>', $this->variables)
        );

        $this->assertEquals(
            'Hello wilderness&lt;?php echo "!"; ?>',
            $this->parser($this->variables)->allowPhp(false)->parse('{{ string }}<?php echo "!"; ?>', $this->variables)
        );
    }

    #[Test]
    public function it_doesnt_parse_noparse_tags()
    {
        $parsed = $this->renderString('{{ noparse }}{{ string }}{{ /noparse }} {{ string }}', $this->variables);

        $this->assertEquals('{{ string }} Hello wilderness', $parsed);
    }

    #[Test]
    public function it_doesnt_parse_data_in_noparse_modifiers()
    {
        $variables = [
            'string' => 'hello',
            'content' => 'before {{ string }} after',
        ];

        $parsed = $this->renderString('{{ content | noparse }} {{ string }}', $variables);

        $this->assertEquals('before {{ string }} after hello', $parsed);
    }

    #[Test]
    public function it_doesnt_parse_data_in_noparse_modifiers_with_null_coalescence()
    {
        $variables = [
            'string' => 'hello',
            'content' => 'before {{ string }} after',
        ];
        $parsed = $this->renderString('{{ missing or content | noparse }} {{ string }}', $variables);
        $this->assertEquals('before {{ string }} after hello', $parsed);
    }

    #[Test]
    public function it_doesnt_parse_noparse_tags_inside_callbacks()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function array()
            {
                return [];
            }

            public function loop()
            {
                return [
                    ['string' => 'One'],
                    ['string' => 'Two'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ tag:array }}{{ noparse }}{{ string }}{{ /noparse }}{{ /tag:array }}
{{ tag:loop }}
    {{ index }} {{ noparse }}{{ string }}{{ /noparse }} {{ string }}
{{ /tag:loop }}
EOT;

        $expected = <<<'EOT'
{{ string }}

    0 {{ string }} One

    1 {{ string }} Two

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables, true));
    }

    #[Test]
    public function it_doesnt_parse_data_in_noparse_modifiers_inside_callbacks()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function array()
            {
                return [
                    'string' => 'hello',
                    'content' => 'beforesingle {{ string }} aftersingle',
                ];
            }

            public function loop()
            {
                return [
                    [
                        'string' => 'One',
                        'content' => 'beforepair {{ string }} afterpair',
                    ],
                    [
                        'string' => 'Two',
                        'content' => 'beforepair {{ string }} afterpair',
                    ],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ tag:array }}{{ content | noparse }}{{ /tag:array }}
{{ tag:loop }}
    {{ count }} {{ content | noparse }} {{ string }}
{{ /tag:loop }}
EOT;

        $expected = <<<'EOT'
beforesingle {{ string }} aftersingle

    1 beforepair {{ string }} afterpair One

    2 beforepair {{ string }} afterpair Two

EOT;

        $parsed = $this->renderString($template, [], true);
        $this->assertEqualsWithCollapsedNewlines($expected, $parsed);
    }

    #[Test]
    public function it_doesnt_parse_tags_prefixed_with_an_at_symbol()
    {
        $this->assertEquals('foo {{ bar }} baz', $this->renderString('foo @{{ bar }} baz'));
    }

    #[Test]
    public function it_doesnt_parse_tags_prefixed_with_an_at_symbol_over_multiple_lines()
    {
        $template = <<<'EOT'
@{{ foo }}
bar
{{ baz }}
EOT;

        $expected = <<<'EOT'
{{ foo }}
bar
BAZ
EOT;

        $this->assertEquals($expected, $this->renderString($template, ['baz' => 'BAZ']));
    }

    #[Test]
    public function it_doesnt_parse_tags_prefixed_with_an_at_symbol_over_tags_in_multiple_lines()
    {
        $template = <<<'EOT'
@{{ foo }} {{ qux }}
bar
{{ baz }}
EOT;

        $expected = <<<'EOT'
{{ foo }} QUX
bar
BAZ
EOT;

        $this->assertEquals($expected, $this->renderString($template, ['baz' => 'BAZ', 'qux' => 'QUX']));
    }

    #[Test]
    public function it_doesnt_parse_multiline_tags_prefixed_with_an_at_symbol_over_tags_in_multiple_lines()
    {
        $template = <<<'EOT'
@{{ foo
  bar:baz="qux"
}} {{ qux }}
bar
{{ baz }}
EOT;

        $expected = <<<'EOT'
{{ foo
  bar:baz="qux"
}} QUX
bar
BAZ
EOT;

        $this->assertEquals($expected, $this->renderString($template, ['baz' => 'BAZ', 'qux' => 'QUX']));
    }

    #[Test]
    public function it_doesnt_parse_tags_prefixed_with_an_at_symbol_containing_nested_tags()
    {
        $this->assertEquals('{{ foo bar="{baz}" }}', $this->renderString('@{{ foo bar="{baz}" }}'));
    }

    #[Test]
    public function it_accepts_an_arrayable_object()
    {
        $this->assertEquals(
            'Hello World',
            $this->renderString('{{ string }}', new ArrayableObject(['string' => 'Hello World']))
        );
    }

    #[Test]
    public function it_throws_exception_for_non_arrayable_data_object()
    {
        try {
            $this->renderString('{{ string }}', new NonArrayableObject(['string' => 'Hello World']));
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [Tests\Antlers\Runtime\NonArrayableObject]', $e->getMessage());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_throws_exception_for_unsupported_data_value()
    {
        try {
            $this->renderString('{{ string }}', 'string');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [string]', $e->getMessage());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_gets_augmented_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $value = new Value('expected', 'test', $fieldtype);

        $parsed = $this->renderString('{{ test }}', ['test' => $value]);

        $this->assertEquals('augmented expected', $parsed);
    }

    #[Test]
    public function it_expands_augmented_value_when_used_as_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($values)
            {
                return collect($values)->map(function ($value) {
                    return strtoupper($value);
                })->all();
            }
        };

        $value = new Value([
            'one' => 'hello',
            'two' => 'world',
        ], 'test', $fieldtype);

        $parsed = $this->renderString('{{ test }}{{ one }} {{ two }}{{ /test }}', ['test' => $value]);

        $this->assertEquals('HELLO WORLD', $parsed);
    }

    #[Test]
    public function it_gets_nested_values_from_augmentable_objects()
    {
        $value = new AugmentableObject(['foo' => 'bar']);

        $parsed = $this->renderString('{{ test:foo }}', ['test' => $value]);

        $this->assertEquals('bar', $parsed);
    }

    #[Test]
    public function it_loops_over_value_object()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($values)
            {
                return collect($values)->map(function ($value) {
                    return collect($value)->map(function ($v) {
                        return strtoupper($v);
                    });
                })->toArray();
            }
        };

        $value = new Value([
            ['one' => 'uno', 'two' => 'dos'],
            ['one' => 'une', 'two' => 'deux'],
        ], 'test', $fieldtype);

        $parsed = $this->renderString('{{ test }}{{ one }} {{ two }} {{ /test }}', ['test' => $value]);

        $this->assertEquals('UNO DOS UNE DEUX ', $parsed);
    }

    #[Test]
    public function it_gets_nested_values_from_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = $this->renderString('{{ test:foo }}', ['test' => $value]);

        $this->assertEquals('bar', $parsed);
    }

    #[Test]
    public function it_gets_nested_values_from_nested_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = $this->renderString('{{ nested:test:foo }}', [
            'nested' => [
                'test' => $value,
            ],
        ]);

        $this->assertEquals('bar', $parsed);
    }

    #[Test]
    public function it_gets_nested_values_from_within_nested_value_objects()
    {
        $value = new Value([
            'foo' => ['nested' => 'bar'],
        ], 'test');

        $parsed = $this->renderString('{{ nested:test:foo:nested }}', [
            'nested' => [
                'test' => $value,
            ],
        ]);

        $this->assertEquals('bar', $parsed);
    }

    #[Test]
    public function it_parses_value_objects_values_when_configured_to_do_so()
    {
        $fieldtypeOne = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }

            public function config(?string $key = null, $fallback = null)
            {
                return true;
            }

            // fake what's being returned from the field config
        };
        $fieldtypeTwo = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }

            public function config(?string $key = null, $fallback = null)
            {
                return false;
            }

            // fake what's being returned from the field config
        };

        $parseable = new Value('before {{ string }} after', 'parseable', $fieldtypeOne);
        $nonParseable = new Value('before {{ string }} after', 'non_parseable', $fieldtypeTwo);

        $template = <<<'EOT'
{{ parseable }}
{{ non_parseable }}
EOT;

        $expected = <<<'EOT'
augmented before hello after
augmented before {{ string }} after
EOT;

        $variables = [
            'parseable' => $parseable,
            'non_parseable' => $nonParseable,
            'string' => 'hello',
        ];

        $this->assertEquals($expected, (string) $this->renderString($template, $variables));

        $this->assertEquals(
            'shmaugmented before hello after',
            (string) $this->renderString('{{ parseable | replace:aug:shmaug }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before {{ string }} after',
            (string) $this->renderString('{{ non_parseable | replace:aug:shmaug }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before hello after',
            (string) $this->renderString('{{ parseable replace="aug|shmaug" }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before {{ string }} after',
            (string) $this->renderString('{{ non_parseable replace="aug|shmaug" }}', $variables)
        );
    }

    #[Test]
    public function it_casts_objects_to_string_when_using_single_tags()
    {
        $object = new class
        {
            public function __toString()
            {
                return 'string';
            }
        };

        $this->assertEquals(
            'string',
            $this->renderString('{{ object }}', compact('object'))
        );
    }

    #[Test]
    public function it_doesnt_output_anything_if_object_cannot_be_cast_to_a_string()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an object variable as a string: {{ object }}', [
                'line' => 1, 'file' => '',
            ]);

        $object = new class
        {
        };

        $this->assertEquals('', $this->renderString('{{ object }}', compact('object')));
    }

    #[Test]
    public function it_casts_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        $arrayableObject = new ArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'foo bar',
            $this->renderString('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $arrayableObject,
            ])
        );
    }

    #[Test]
    public function it_cannot_cast_non_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ object }}', [
                'line' => 1, 'file' => '',
            ]);

        $nonArrayableObject = new NonArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            '',
            $this->renderString('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $nonArrayableObject,
            ])
        );
    }

    #[Test]
    public function callback_tags_that_return_unparsed_simple_arrays_get_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return ['one' => 'a', 'two' => 'b'];
            }
        })::register();

        $template = <<<'EOT'
{{ tag }}
    {{ one }} {{ two }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'

    a b

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, [], true));
    }

    #[Test]
    public function callback_tags_that_return_unparsed_simple_arrays_get_parsed_with_scope()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return ['one' => 'a', 'two' => 'b'];
            }
        })::register();

        $template = <<<'EOT'
{{ tag scope="foo" }}
    {{ foo:one }} {{ foo:two }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'

    a b

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, [], true));
    }

    #[Test]
    public function callback_tags_that_return_unparsed_multidimensional_arrays_get_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return [
                    ['one' => 'a', 'two' => 'b'],
                    ['one' => 'c', 'two' => 'd'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ string }}
{{ tag }}
    {{ count }} {{ if first }}first{{ else }}not-first{{ /if }} {{ if last }}last{{ else }}not-last{{ /if }} {{ one }} {{ two }} {{ string }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'
Hello wilderness

    1 first not-last a b Hello wilderness

    2 not-first last c d Hello wilderness

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, ['string' => 'Hello wilderness'], true));
    }

    #[Test]
    public function callback_tags_that_return_empty_arrays_get_parsed_with_no_results()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return [];
            }
        })::register();

        $template = <<<'EOT'
{{ tag }}
    {{ if no_results }}no results{{ else }}there are results{{ /if }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'

    no results

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables, true));
    }

    #[Test]
    public function callback_tags_that_return_collections_get_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return collect([
                    ['one' => 'a', 'two' => 'b'],
                    ['one' => 'c', 'two' => 'd'],
                ]);
            }
        })::register();

        $template = <<<'EOT'
{{ string }}
{{ tag }}
    {{ count }} {{ if first }}first{{ else }}not-first{{ /if }} {{ if last }}last{{ else }}not-last{{ /if }} {{ one }} {{ two }} {{ string }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'
Hello wilderness

    1 first not-last a b Hello wilderness

    2 not-first last c d Hello wilderness

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables, true));
    }

    #[Test]
    public function callback_tags_that_return_query_builders_get_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                $builder = Mockery::mock(Builder::class);
                $builder->shouldReceive('get')->andReturn(collect([
                    ['one' => 'a', 'two' => 'b'],
                    ['one' => 'c', 'two' => 'd'],
                ]));

                return $builder;
            }
        })::register();

        $template = <<<'EOT'
{{ string }}
{{ tag }}
    {{ count }} {{ if first }}first{{ else }}not-first{{ /if }} {{ if last }}last{{ else }}not-last{{ /if }} {{ one }} {{ two }} {{ string }}
{{ /tag }}
EOT;

        $expected = <<<'EOT'
Hello wilderness

    1 first not-last a b Hello wilderness

    2 not-first last c d Hello wilderness

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $this->variables, true));
    }

    #[Test]
    public function callback_tags_that_return_value_objects_gets_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype
                {
                    public function augment($value)
                    {
                        return 'augmented '.$value;
                    }
                };

                return new Value('the value', null, $fieldtype);
            }
        })::register();

        $this->assertEquals('augmented the value', $this->renderString('{{ tag }}', [], true));
    }

    #[Test]
    public function callback_tags_that_return_value_objects_with_antlers_gets_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype
                {
                    public function augment($value)
                    {
                        return 'augmented '.$value;
                    }
                };

                $fieldtype->setField(new Field('test', ['antlers' => true]));

                return new Value('the value with {{ var }} in it', null, $fieldtype);
            }
        })::register();

        $this->assertEquals(
            'augmented the value with howdy in it',
            (string) $this->renderString('{{ tag }}', ['var' => 'howdy'], true)
        );
    }

    #[Test]
    public function callback_tags_that_return_value_objects_with_antlers_disabled_does_not_get_parsed()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype
                {
                    public function augment($value)
                    {
                        return 'augmented '.$value;
                    }
                };

                $fieldtype->setField(new Field('test', ['antlers' => false]));

                return new Value('the value with {{ var }} in it', null, $fieldtype);
            }
        })::register();

        $this->assertEquals(
            'augmented the value with {{ var }} in it',
            (string) $this->renderString('{{ tag }}', ['var' => 'howdy'], true)
        );
    }

    #[Test]
    public function value_objects_with_antlers_gets_parsed()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $fieldtype->setField(new Field('test', ['antlers' => true]));

        $value = new Value('the value with {{ var }} in it', null, $fieldtype);

        $this->assertEquals(
            'augmented the value with howdy in it',
            (string) $this->renderString('{{ test }}', [
                'test' => $value,
                'var' => 'howdy',
            ], true)
        );
    }

    #[Test]
    public function value_objects_with_antlers_disabled_do_not_get_parsed()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $fieldtype->setField(new Field('test', ['antlers' => false]));

        $value = new Value('the value with {{ var }} in it', null, $fieldtype);

        $this->assertEquals(
            'augmented the value with {{ var }} in it',
            (string) $this->renderString('{{ test }}', [
                'test' => $value,
                'var' => 'howdy',
            ])
        );
    }

    #[Test]
    public function it_automatically_augments_augmentable_objects_when_using_tag_pairs()
    {
        $augmentable = new AugmentableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'FOO! bar',
            $this->renderString('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $augmentable,
            ])
        );
    }

    #[Test]
    public function it_automatically_augments_augmentable_objects_when_returned_from_a_callback_tag()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return new AugmentableObject([
                    'one' => 'foo',
                    'two' => 'bar',
                ]);
            }
        })::register();

        $this->assertEquals(
            'FOO! bar',
            $this->renderString('{{ tag }}{{ one }} {{ two }}{{ /tag }}', [], true)
        );
    }

    #[Test]
    public function it_automatically_augments_collections_when_using_tag_pairs()
    {
        $augmentable = collect([
            new AugmentableObject(['one' => 'foo', 'two' => 'bar']),
            new AugmentableObject(['one' => 'baz', 'two' => 'qux']),
        ]);

        $this->assertEquals(
            'FOO! bar BAZ! qux ',
            $this->renderString('{{ object }}{{ one }} {{ two }} {{ /object }}', [
                'object' => $augmentable,
            ])
        );
    }

    #[Test]
    public function callback_tag_pair_variables_get_context_merged_in_but_nulls_remain_null()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return [
                    'drink' => 'juice',
                    'activity' => null,
                ];
            }
        })::register();

        $context = [
            'drink' => 'whisky',
            'food' => 'burger',
            'activity' => 'singing',
        ];

        $template = <<<'EOT'
{{ drink }} {{ food }} {{ activity }}
{{ tag }}{{ drink }} {{ food }} -{{ activity }}-{{ /tag }}
EOT;

        $expected = <<<'EOT'
whisky burger singing
juice burger --
EOT;
        $this->assertEquals($expected, $this->renderString($template, $context, true));
    }

    #[Test]
    public function variable_tag_pair_get_context_merged_in_except_for_nulls()
    {
        $context = [
            'drink' => 'whisky',
            'food' => 'burger',
            'activity' => 'singing',
            'array' => [
                'drink' => 'juice',
                'activity' => null,
            ],
        ];

        $template = <<<'EOT'
{{ drink }} {{ food }} {{ activity }}
{{ array }}{{ drink }} {{ food }} -{{ activity }}-{{ /array }}
EOT;

        $expected = <<<'EOT'
whisky burger singing
juice burger --
EOT;
        $this->assertEquals($expected, $this->renderString($template, $context));
    }

    #[Test]
    public function scope_modifier_can_add_scopes()
    {
        $context = [
            'drink' => 'whisky',
            'food' => 'burger',
            'array' => [
                ['drink' => 'juice'],
                ['drink' => 'smoothie'],
            ],
        ];

        $template = <<<'EOT'
{{ food }} {{ drink }}
{{ array scope="s" }}
-{{ s:food }}- {{ s:drink }} {{ food }} {{ drink }}
{{ /array }}
EOT;

        $expected = <<<'EOT'
burger whisky

-- juice burger juice

-- smoothie burger smoothie

EOT;
        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, $context, true));
    }

    #[Test]
    public function it_can_reach_into_the_cascade()
    {
        $cascade = $this->mock(Cascade::class, function ($m) {
            $m->shouldReceive('get')->with('page')->andReturn(['drink' => 'juice']);
            $m->shouldReceive('get')->with('global')->andReturn(['drink' => 'water']);
            $m->shouldReceive('get')->with('menu')->andReturn(['drink' => 'vodka']);
            $m->shouldNotReceive('get')->with('nested');
            $m->shouldNotReceive('get')->with('augmented');
        });

        $fieldtype = new class extends Fieldtype
        {
        };
        $augmented = new Value(['drink' => 'la croix'], 'augmented', $fieldtype);

        $context = [
            'drink' => 'whisky',
            'augmented' => $augmented,
            'nested' => [
                'drink' => 'coke',
                'augmented' => $augmented,
            ],
        ];

        $template = <<<'EOT'
var: {{ drink }}
page: {{ page:drink }}
global: {{ global:drink }}
menu: {{ menu:drink }}
nested: {{ nested:drink }}
augmented: {{ augmented:drink }}
nested augmented: {{ nested:augmented:drink }}
EOT;

        $expected = <<<'EOT'
var: whisky
page: juice
global: water
menu: vodka
nested: coke
augmented: la croix
nested augmented: la croix
EOT;

        $results = (string) $this->parser()->cascade($cascade)->parse($template, $context);
        $this->assertEquals($expected, $results);
    }

    #[Test]
    public function it_can_create_scopes()
    {
        $context = [
            'drink' => 'whisky',
            'food' => 'burger',
            'activity' => 'singing',
            'array' => [
                'drink' => 'juice',
                'activity' => null,
            ],
        ];

        $template = <<<'EOT'
{{ scope:test }}
drink: {{ drink }}
food: {{ food }}
activity: {{ activity }}

{{ array }}
    array:drink: {{ drink }}
    array:food: {{ food }}
    array:activity: -{{ activity }}-
    array:test:drink: {{ test:drink }}
    array:test:food: {{ test:food }}
    array:test:activity: {{ test:activity }}
{{ /array }}
{{ /scope:test }}
EOT;

        $expected = <<<'EOT'
drink: whisky
food: burger
activity: singing


    array:drink: juice
    array:food: burger
    array:activity: --
    array:test:drink: whisky
    array:test:food: burger
    array:test:activity: singing
EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, trim($this->renderString($template, $context, true)));
    }

    #[Test]
    public function it_does_not_accept_sequences()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting an associative array');
        $this->renderString('', ['foo', 'bar']);
    }

    #[Test]
    public function it_does_not_accept_multidimensional_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting an associative array');
        $this->renderString('', [
            ['foo' => 'bar'],
            ['foo' => 'baz'],
        ]);
    }

    #[Test]
    public function it_aliases_array_tag_pairs_using_the_as_modifier()
    {
        $template = <<<'EOT'
{{ array as="stuff" }}
before
{{ stuff }}
{{ foo }}
{{ /stuff }}
after
{{ /array }}
EOT;

        $expected = <<<'EOT'

before

bar

baz

qux

after

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, [
            'array' => [
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux'],
            ],
        ], true));
    }

    #[Test]
    public function it_aliases_callback_tag_pair_loop_using_the_as_param()
    {
        (new class extends Tags
        {
            public static $handle = 'tag';

            public function index()
            {
                return [
                    ['foo' => 'bar'],
                    ['foo' => 'baz'],
                    ['foo' => 'qux'],
                ];
            }
        })::register();

        $template = <<<'EOT'
{{ tag as="stuff" }}
before
{{ stuff }}
{{ foo }}
{{ /stuff }}
after
{{ /tag }}
EOT;

        $expected = <<<'EOT'

before

bar

baz

qux

after

EOT;

        $this->assertEqualsWithCollapsedNewlines($expected, $this->renderString($template, [], true));
    }

    #[Test]
    public function it_counts_query_builder_results_in_conditions()
    {
        (new EntryFactory())->collection('blog')->create();

        $template = '{{ if entries }}yup{{ else }}nope{{ /if }}';

        $this->assertEquals('yup', $this->renderString($template, ['entries' => Entry::query()]));
        $this->assertEquals('yup', $this->renderString($template, ['entries' => Entry::query()->where('collection', 'blog')]));
        $this->assertEquals('nope', $this->renderString($template, ['entries' => Entry::query()->where('collection', 'dunno')]));
    }

    #[Test]
    public function it_applies_modifier_on_different_array_syntax()
    {
        $vars = [
            'key' => 'entries',
            'source' => [
                'entries' => [
                    ['id' => 0],
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ],
            ],
        ];

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source.entries }}[{{ id }}]{{ /source.entries }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source[key] }}[{{ id }}]{{ /source[key] }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source.entries sort="id" }}[{{ id }}]{{ /source.entries }}', $vars)
        );

        $this->assertEquals(
            '[0][1][2][3]',
            $this->renderString('{{ source[key] sort="id" }}[{{ id }}]{{ /source[key] }}', $vars)
        );

        $this->assertEquals(
            '[3][2][1][0]',
            $this->renderString('{{ source[key] sort="id|desc" }}[{{ id }}]{{ /source[key] }}', $vars)
        );
    }

    #[Test]
    public function modifiers_on_tag_pairs_receive_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                $value[1]['type'] = 'yup';

                return $value;
            }
        };

        $value = new Value([
            ['type' => 'yup', 'text' => '1'],
            ['type' => 'nope', 'text' => '2'],
            ['type' => 'yup', 'text' => '3'],
        ], 'test', $fieldtype);

        // unaugmented, the second item would be filtered out.
        // augmenting changes the second item to a yup, so it should be included.
        $this->assertEquals('123', $this->renderString('{{ test where="type:yup" }}{{ text }}{{ /test }}', [
            'test' => $value,
            'hello' => 'there',
        ]));
    }

    #[Test]
    public function it_outputs_the_value_when_a_ArrayableString_object_is_used_as_string()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return new ArrayableString('world', ['label' => 'World']);
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $this->assertEquals('world', $this->renderString('{{ hello }}', [
            'hello' => $value,
        ]));
    }

    #[Test]
    public function it_can_treat_a_ArrayableString_object_as_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return new ArrayableString('world', ['label' => 'World']);
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $this->assertEquals(
            'world, World',
            $this->renderString('{{ hello }}{{ value }}, {{ label }}{{ /hello }}', [
                'hello' => $value,
            ])
        );
    }

    #[Test]
    public function it_can_access_ArrayableString_properties_by_colon_notation()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return new ArrayableString('world', ['label' => 'World']);
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $vars = ['hello' => $value];

        $this->assertEquals('world', $this->renderString('{{ hello:value }}', $vars));
        $this->assertEquals('World', $this->renderString('{{ hello:label }}', $vars));
    }

    #[Test]
    public function it_can_use_ArrayableString_objects_in_conditions()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                $label = is_null($value) ? null : strtoupper($value);

                return new ArrayableString($value, ['label' => $label]);
            }
        };

        $vars = [
            'string' => new Value('foo', 'string', $fieldtype),
            'nully' => new Value(null, 'nully', $fieldtype),
        ];

        $this->assertEquals('true', $this->renderString('{{ if string }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ if nully }}true{{ else }}false{{ /if }}', $vars));

        $this->assertEquals('true', $this->renderString('{{ if string == "foo" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ if nully == "foo" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ if string == "bar" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ if nully == "bar" }}true{{ else }}false{{ /if }}', $vars));

        $this->assertEquals('true', $this->renderString('{{ string ? "true" : "false" }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ nully ? "true" : "false" }}', $vars));

        $this->assertEquals('true', $this->renderString('{{ string == "foo" ? "true" : "false" }}', $vars));
        $this->assertEquals('false', $this->renderString('{{ string == "bar" ? "true" : "false" }}', $vars));

        $this->assertEquals('foo', $this->renderString('{{ string or "fallback" }}', $vars));
        $this->assertEquals('FOO', $this->renderString('{{ string:label or "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->renderString('{{ nully or "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->renderString('{{ nully:label or "fallback" }}', $vars));

        $this->assertEquals('foo', $this->renderString('{{ string ?? "fallback" }}', $vars));
        $this->assertEquals('FOO', $this->renderString('{{ string:label ?? "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->renderString('{{ nully ?? "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->renderString('{{ nully:label ?? "fallback" }}', $vars));

        $this->assertEquals('fallback', $this->renderString('{{ string ?= "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->renderString('{{ string:label ?= "fallback" }}', $vars));
        $this->assertEquals('', $this->renderString('{{ nully ?= "fallback" }}', $vars));
        $this->assertEquals('', $this->renderString('{{ nully:label ?= "fallback" }}', $vars));
    }

    #[Test]
    public function it_can_remove_escaping_characters_from_tenary_output()
    {
        $vars = [
            'seo_title' => "Let's work together",
            'title' => 'Contact',

            'local_office_link' => '',
            'head_office_link' => 'https://statamic.com',
        ];

        $this->assertEquals("Let's work together", $this->renderString('{{ seo_title ? seo_title : title }}', $vars));
        $this->assertEquals('Contact', $this->renderString('{{ title ? title : seo_title }}', $vars));

        $this->assertEquals('https://statamic.com', $this->renderString('{{ local_office_link ? local_office_link : head_office_link }}', $vars));
        $this->assertEquals('https://statamic.com', $this->renderString('{{ head_office_link ? head_office_link : local_office_link }}', $vars));
    }

    #[Test]
    public function it_can_remove_escaping_characters_from_tenary_output_with_truth_coalescence()
    {
        $vars = [
            'truthy' => true,
            'string' => "Let's work together",
            'link' => 'https://statamic.com',
        ];

        $this->assertEquals("Let's work together", $this->renderString('{{ truthy ?= string }}', $vars));
        $this->assertEquals('https://statamic.com', $this->renderString('{{ truthy ?= link }}', $vars));
    }

    #[Test]
    public function empty_collections_are_considered_empty_in_conditions()
    {
        $template = '{{ if stuff }}yes{{ else }}no{{ /if }}';
        $this->assertEquals('no', $this->renderString($template, ['stuff' => collect()]));
        $this->assertEquals('yes', $this->renderString($template, ['stuff' => collect(['one'])]));
    }

    #[Test]
    public function empty_view_error_bags_are_considered_empty_in_conditions()
    {
        $template = '{{ if errors}}yes{{ else }}no{{ /if }}';
        $viewErrorBag = new ViewErrorBag;

        $this->assertEquals('no', $this->renderString($template, ['errors' => $viewErrorBag]));
        $this->assertEquals('yes', $this->renderString($template, ['errors' => $viewErrorBag->put('default', new MessageBag)]));
        $this->assertEquals('yes', $this->renderString($template, ['errors' => $viewErrorBag->put('form.contact', new MessageBag)]));
    }

    #[Test]
    public function objects_are_considered_truthy()
    {
        $this->assertEquals('yes', $this->renderString('{{ if object }}yes{{ else }}no{{ /if }}', [
            'object' => new \stdClass(),
        ]));
    }

    #[Test]
    public function parameter_style_modifier_with_colon_prefix_will_get_the_values_from_context()
    {
        $this->assertEquals('Tes Te', $this->renderString('{{ word :backspace="one" }} {{ word :backspace="two" }}', [
            'word' => 'Test',
            'one' => 1,
            'two' => 2,
        ]));
    }

    #[Test]
    public function variables_starting_with_if_arent_treated_as_if_statements()
    {
        $this->assertEquals('test', $this->renderString('{{ iframe }}', ['iframe' => 'test']));
        $this->assertEquals('test', $this->renderString('{{ unlesses }}', ['unlesses' => 'test']));
        $this->assertEquals('test', $this->renderString('{{ elseifs }}', ['elseifs' => 'test']));
        $this->assertEquals('test', $this->renderString('{{ elseunlessses }}', ['elseunlessses' => 'test']));
    }

    #[Test]
    public function when_a_loop_is_a_value_object_with_an_empty_array_it_get_parsed_as_one()
    {
        $template = <<<'EOT'
before
{{ simple }}
    {{ foo }}
{{ /simple }}
after
EOT;

        $expected = <<<'EOT'
before

after
EOT;
        $this->assertEquals($expected, $this->renderString($template, [
            'simple' => new Value([], null, new class extends \Statamic\Fieldtypes\Replicator
            {
            }),
        ]));
    }

    #[Test]
    public function it_automatically_augments_augmentable_objects_when_looping_with_modifier()
    {
        $loop = [
            new AugmentableObject(['one' => 'foo', 'two' => 'bar']),
            new AugmentableObject(['one' => 'baz', 'two' => 'qux']),
        ];

        $this->assertEquals(
            '<FOO!><bar>',
            (string) $this->renderString('{{ augmentables limit="1" }}<{{ one }}><{{ two }}>{{ /augmentables }}', ['augmentables' => $loop])
        );
    }

    #[Test]
    public function it_uses_tags_with_single_part_in_conditions()
    {
        (new class extends Tags
        {
            public static $handle = 'truthy';

            public function index()
            {
                return true;
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'falsey';

            public function index()
            {
                return false;
            }
        })::register();

        $this->assertEquals('yes', $this->renderString('{{ if {truthy} }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('yes', $this->renderString('{{ if {truthy} == true }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {truthy} == false }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {falsey} }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {falsey} == true }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('yes', $this->renderString('{{ if {falsey} == false }}yes{{ else }}no{{ /if }}', [], true));
    }

    #[Test]
    public function it_uses_tags_with_multiple_parts_in_conditions()
    {
        (new class extends Tags
        {
            public static $handle = 'truthy';

            public function test()
            {
                return true;
            }
        })::register();

        (new class extends Tags
        {
            public static $handle = 'falsey';

            public function test()
            {
                return false;
            }
        })::register();

        $this->assertEquals('yes', $this->renderString('{{ if {truthy:test} }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('yes', $this->renderString('{{ if {truthy:test} == true }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {truthy:test} == false }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {falsey:test} }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('no', $this->renderString('{{ if {falsey:test} == true }}yes{{ else }}no{{ /if }}', [], true));
        $this->assertEquals('yes', $this->renderString('{{ if {falsey:test} == false }}yes{{ else }}no{{ /if }}', [], true));
    }

    #[Test]
    public function it_does_stuff_in_issue_2537()
    {
        $template = '{{ if noindex || segment_1 == "mobile" || get:page > 0 }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->renderString($template, ['noindex' => true]));
    }

    #[Test]
    public function it_does_stuff_in_issue_2456()
    {
        $template = '{{ if publication_venue:publication_venue_types:slug !== "journal" and publication_venue:first_year }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->renderString($template, [
            'publication_venue' => [
                'first_year' => true,
                'publication_venue_types' => [
                    'slug' => 'notjournal',
                ],
            ],
        ]));
    }

    #[Test]
    public function it_compares_to_a_string_that_looks_like_array_access()
    {
        $template = '{{ if test == "price:desc" }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->renderString($template, [
            'test' => 'price:desc',
        ]));
    }

    /**
     * @see https://github.com/statamic/cms/issues/3374
     **/
    #[Test]
    public function it_parses_single_and_tag_pairs_with_modifiers()
    {
        $data = ['items' => ['one', 'two', 'three']];

        $this->assertEquals('<one><two>3', $this->renderString('{{ items limit="2" }}<{{ value }}>{{ /items }}{{ items | count }}', $data));
        $this->assertEquals('3<one><two>', $this->renderString('{{ items | count }}{{ items limit="2" }}<{{ value }}>{{ /items }}', $data));
    }

    #[Test]
    public function it_passes_along_query_builder_values_to_the_query_tag()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Bar'],
        ]));

        $this->assertEquals('<Foo><Bar>', $this->renderString('{{ my_query }}<{{ title }}>{{ /my_query }}', [
            'my_query' => $builder,
        ]));
    }

    #[Test]
    public function it_passes_along_query_builder_augmented_values_to_the_query_tag()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Bar'],
        ]));

        $this->assertEquals('<Foo><Bar>', $this->renderString('{{ my_query }}<{{ title }}>{{ /my_query }}', [
            'my_query' => new Value($builder),
        ]));
    }

    #[Test]
    public function it_can_reach_into_query_builders()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(2)->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Bar'],
        ]));

        $this->assertEquals('<Bar><Foo>', $this->renderString('<{{ my_query:1:title }}><{{ my_query:0:title }}>', [
            'my_query' => $builder,
        ]));
    }

    #[Test]
    public function it_can_reach_into_query_builders_through_values()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->times(2)->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Bar'],
        ]));

        $this->assertEquals('<Bar><Foo>', $this->renderString('<{{ my_query:1:title }}><{{ my_query:0:title }}>', [
            'my_query' => new Value($builder),
        ]));
    }

    #[Test]
    public function it_can_get_nested_query_builders()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect([
            ['title' => 'Foo'],
            ['title' => 'Bar'],
        ]));

        $this->assertEquals('<Foo><Bar>', $this->renderString('{{ nested:my_query }}<{{ title }}>{{ /nested:my_query }}', [
            'nested' => [
                'my_query' => $builder,
            ],
        ]));
    }

    #[Test]
    public function it_loops_over_values_instances()
    {
        $this->assertEquals('<alfa><bravo><charlie><delta>', $this->renderString('{{ grid }}<{{ foo }}><{{ bar }}>{{ /grid }}', [
            'grid' => [
                new Values(['foo' => 'alfa', 'bar' => 'bravo']),
                new Values(['foo' => 'charlie', 'bar' => 'delta']),
            ],
        ]));
    }

    #[Test]
    #[DataProvider('objectInConditionProvider')]
    public function it_uses_entries_as_conditions($object)
    {
        $this->assertEquals('yes', $this->renderString('{{ if the_field }}yes{{ else }}no{{ /if }}', [
            'the_field' => $object,
        ]));
    }

    public static function objectInConditionProvider()
    {
        return [
            'with __toString' => [new class()
            {
                public function __toString()
                {
                    return 'foo';
                }
            }, ],
            'with __call' => [new class()
            {
                public function __call($method, $args)
                {
                    return 'foo';
                }
            }, ],
            'without __call or __toString' => [new class()
            {
                //
            }, ],
        ];
    }

    private function assertEqualsWithCollapsedNewlines($expected, $actual)
    {
        $expected = trim($expected);
        $actual = trim($actual);

        // replace all newlines with a single newline
        $expected = preg_replace('/\n+/', "\n", $expected);
        $actual = preg_replace('/\n+/', "\n", $actual);

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function test_rendering_a_non_array_variable_reports_current_file()
    {
        $this->markTestSkipped(); // todo

        Log::shouldReceive('debug')->once()->with('Cannot render an array variable as a string: {{ an_array_value }}', [
            'line' => 3, 'file' => 'the_partial.antlers.html',
        ]);

        Collection::make('pages')->routes(['en' => '/{{slug}}'])->save();
        EntryFactory::collection('pages')->id('1')->slug('home')->data(['title' => 'Home'])->create();
        $this->withFakeViews();

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $default = <<<'DEFAULT'
Some content
more content
even more content
hey look a wild partial appears! {{ partial:the_partial }}
DEFAULT;
        $this->viewShouldReturnRaw('default', $default);
        $thePartial = <<<'PARTIAL'
{{ an_array_value = [1,2,3,4,5]; }}

Attempt to render as a string: {{ an_array_value }}
PARTIAL;
        $this->viewShouldReturnRaw('the_partial', $thePartial);
        $this->get('/home')->assertOk();
    }

    #[Test]
    public function test_it_passes_data_to_php_when_enabled()
    {
        $this->assertEquals('hello', (string) $this->parser()->allowPhp(true)->parse('{{ associative }}<?php echo $one; ?>{{ /associative }}', $this->variables));
    }

    #[Test]
    public function test_it_returns_escaped_content()
    {
        $input = 'Hey, look at that @{{ noun }}!';
        $this->assertSame('Hey, look at that {{ noun }}!', $this->renderString($input, []));
    }
}

class NonArrayableObject
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}

class ArrayableObject extends NonArrayableObject implements Arrayable
{
    public function toArray()
    {
        return $this->data;
    }
}

class AugmentableObject extends ArrayableObject implements Augmentable
{
    use HasAugmentedData;

    public function augmentedArrayData()
    {
        return $this->data;
    }

    public function blueprint()
    {
        FieldtypeRepository::shouldReceive('find')->andReturn(new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }
        });

        return (new Blueprint)->setContents(['fields' => [
            ['handle' => 'one', 'field' => ['type' => 'test']],
        ]]);
    }
}
