<?php

namespace Tests\View\Antlers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Antlers;
use Statamic\Facades\Entry;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ParserTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $variables;

    public function setUp(): void
    {
        parent::setUp();

        $this->variables = [
            'default_key' => 'two',
            'first_key' => 'three',
            'second_key' => 'deep',
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

    private function parse($template, $data = [])
    {
        return (string) Antlers::parse($template, $data);
    }

    public function testStringVariable()
    {
        $template = '{{ string }}';

        $this->assertEquals('Hello wilderness', $this->parse($template, $this->variables));
    }

    public function testStringVariableWithTightBraces()
    {
        $template = '{{string}}';

        $this->assertEquals('Hello wilderness', $this->parse($template, $this->variables));
    }

    public function testArrayVariable()
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

        $this->assertEquals($expected, $this->parse($template, $this->variables));

        $this->assertEquals('wilderness', $this->parse('{{ associative[default_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->parse('{{ associative[first_key][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->parse('{{ associative[\'three\'][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->parse('{{ associative["three"][second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->parse('{{ associative.three[second_key] }}', $this->variables));
        $this->assertEquals('Very deep', $this->parse('{{ associative:three[second_key] }}', $this->variables));
    }

    public function testComplexArrayVariable()
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

        $this->assertEquals($expected, $this->parse($template, $this->variables));
    }

    public function testAssociativeArrayVariable()
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

        $this->assertEquals($expected, $this->parse($template, $this->variables));
    }

    public function testScopeGlue()
    {
        $template = '{{ associative:one }} {{ associative.two }}';

        $this->assertEquals('hello wilderness', $this->parse($template, $this->variables));
    }

    public function testNonExistantVariablesShouldBeNull()
    {
        $template = '{{ missing }}';

        $this->assertEquals('', $this->parse($template, $this->variables));
    }

    /** @test */
    public function accessing_strings_as_arrays_returns_null()
    {
        $this->assertEquals('bar, ><', $this->parse('{{ foo }}, >{{ foo:test }}<', ['foo' => 'bar']));
    }

    /** @test */
    public function accessing_string_as_array_which_exists_as_callback_calls_the_callback()
    {
        (new class extends Tags {
            public static $handle = 'foo';

            public function test()
            {
                return 'callback';
            }
        })::register();

        $this->assertEquals('bar, callback', $this->parse('{{ foo }}, {{ foo:test }}', ['foo' => 'bar']));
    }

    /** @test */
    public function non_arrays_cannot_be_looped()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ string }}');

        $template = '{{ string }} {{ /string }}';

        $this->assertEquals('', $this->parse($template, $this->variables));
    }

    public function testStaticStringsWithDoubleQuotesShouldBeLeftAlone()
    {
        $template = '{{ "Thundercats are Go!" }}';

        $this->assertEquals('Thundercats are Go!', $this->parse($template, $this->variables));
    }

    public function testStaticStringsWithSingleQuotesShouldBeLeftAlone()
    {
        $template = "{{ 'Thundercats are Go!' }}";

        $this->assertEquals('Thundercats are Go!', $this->parse($template, $this->variables));
    }

    public function testStaticStringsWithDoubleQuotesCanBeModified()
    {
        $template = '{{ "Thundercats are Go!" | upper }}';

        $this->assertEquals('THUNDERCATS ARE GO!', $this->parse($template, $this->variables));
    }

    public function testStaticStringsWithSingleQuotesCanBeModified()
    {
        $template = "{{ 'Thundercats are Go!' | upper }}";

        $this->assertEquals('THUNDERCATS ARE GO!', $this->parse($template, $this->variables));
    }

    public function testSingleBracesShouldNotBeParsed()
    {
        $template = '{string}';

        $this->assertEquals('{string}', $this->parse($template, $this->variables));
    }

    public function testModifiedNonExistantVariablesShouldBeNull()
    {
        $template = '{{ missing|upper }}';

        $this->assertEquals('', $this->parse($template, $this->variables));
    }

    public function testUnclosedArrayVariablePairsShouldBeNull()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an array variable as a string: {{ simple }}');

        $template = '{{ simple }}';

        $this->assertEquals('', $this->parse($template, $this->variables));
    }

    public function testSingleCondition()
    {
        $template = '{{ if string == "Hello wilderness" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->parse($template, $this->variables));
    }

    public function testMultipleAndConditions()
    {
        $template = '{{ if string == "Hello wilderness" && content }}yes{{ endif }}';

        $this->assertEquals('yes', $this->parse($template, $this->variables));
    }

    public function testMultipleOrConditions()
    {
        $should_pass = '{{ if string == "failure" || string == "Hello wilderness" }}yes{{ endif }}';
        $should_fail = '{{ if string == "failure" or string == "womp" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->parse($should_pass, $this->variables));
        $this->assertEquals('', $this->parse($should_fail, $this->variables));
    }

    public function testOrExistanceConditions()
    {
        $should_pass = '{{ if string || strudel }}yes{{ endif }}';
        $should_also_pass = '{{ if strudel or string }}yes{{ endif }}';
        $should_fail = '{{ if strudel || wurst }}yes{{ endif }}';
        $should_also_fail = '{{ if strudel or wurst }}yes{{ endif }}';

        $this->assertEquals('yes', $this->parse($should_pass, $this->variables));
        $this->assertEquals('yes', $this->parse($should_also_pass, $this->variables));
        $this->assertEquals('', $this->parse($should_fail, $this->variables));
        $this->assertEquals('', $this->parse($should_also_fail, $this->variables));
    }

    public function testConditionsOnOverlappingVariableNames()
    {
        $template = '{{ if complex }}{{ complex limit="1" }}{{ string }}{{ /complex }}{{ /if }}';

        $this->assertEquals('the first string', $this->parse($template, $this->variables));
    }

    public function testLoopWithParamInsideConditionMatchingVariableName()
    {
        $template = '{{ if complex_string }}{{ complex_string }}{{ /if }}{{ complex }}{{ /complex }}';

        $this->assertEquals('Hello wildernesses', $this->parse($template, $this->variables));
    }

    public function testTernaryCondition()
    {
        $template = '{{ string ? "Pass" : "Fail" }}';

        $this->assertEquals('Pass', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionWithDynamicArray()
    {
        $template = '{{ associative[default_key] ? "Pass" : "Fail" }}';

        $this->assertEquals('Pass', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionIsntTooGreedy()
    {
        $template = '{{ content }} {{ string ? "Pass" : "Fail" }} {{ content }}';

        $this->assertEquals('Paragraph Pass Paragraph', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionWithAVariable()
    {
        $template = '{{ string ? string : "Fail" }}';

        $this->assertEquals('Hello wilderness', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionWithModifiers()
    {
        $template = '{{ string ? string | upper : "Fail" }}';

        $this->assertEquals('HELLO WILDERNESS', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionWithModifiersAndDynamicArray()
    {
        $template = '{{ string ? associative[default_key] | upper : "Fail" }}';

        $this->assertEquals('WILDERNESS', $this->parse($template, $this->variables));
    }

    public function testTernaryConditionWithMultipleLines()
    {
        $template = <<<'EOT'
{{ string
    ? "Pass"
    : "Fail" }}
EOT;

        $this->assertEquals('Pass', $this->parse($template, $this->variables));
    }

    public function testTernaryEscapesQuotesProperly()
    {
        $data = ['condition' => true, 'var' => '"Wow" said the man'];
        $template = '{{ condition ? var : "nah" }}';

        $this->assertEquals('"Wow" said the man', $this->parse($template, $data));
    }

    public function testTernaryConditionInsideParameter()
    {
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\Test::class;

        $template = "{{ test variable='{{ true ? 'Hello wilderness' : 'fail' }}' }}";

        $this->assertEquals('Hello wilderness', $this->parse($template, $this->variables));
    }

    public function testNullCoalescence()
    {
        // or, ?:, and ?? are all aliases.
        // while ?: and ?? have slightly different behaviors in php, they work the same in antlers.

        $this->assertEquals('Hello wilderness', $this->parse('{{ string or "Pass" }}', $this->variables));
        $this->assertEquals('Hello wilderness', $this->parse('{{ string ?: "Pass" }}', $this->variables));
        $this->assertEquals('Hello wilderness', $this->parse('{{ string ?? "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing ?? "Pass" }}', $this->variables));

        $this->assertEquals('Pass', $this->parse('{{ missing or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing ?? "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing[thing] or "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing[thing] ?: "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ missing[thing] ?? "Pass" }}', $this->variables));
    }

    public function testTruthCoalescing()
    {
        $this->assertEquals('Pass', $this->parse('{{ string ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ missing[thing] ?= "Pass" }}', $this->variables));

        // Negating with !
        $this->assertEquals('', $this->parse('{{ !string ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ !associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ !associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ !missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ !missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ !missing[thing] ?= "Pass" }}', $this->variables));

        // and with spaces
        $this->assertEquals('', $this->parse('{{ ! string ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ ! associative:one ?= "Pass" }}', $this->variables));
        $this->assertEquals('', $this->parse('{{ ! associative[default_key] ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ ! missing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ ! missing:thing ?= "Pass" }}', $this->variables));
        $this->assertEquals('Pass', $this->parse('{{ ! missing[thing] ?= "Pass" }}', $this->variables));
    }

    public function testTruthCoalescingInsideLoop()
    {
        $template = '{{ complex }}{{ first ?= "Pass" }}{{ /complex }}';

        $this->assertEquals('Pass', $this->parse($template, $this->variables));
    }

    public function testSingleStandardStringModifierTight()
    {
        $template = '{{ string|upper }}';

        $this->assertEquals('HELLO WILDERNESS', $this->parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersTight()
    {
        $template = '{{ string|upper|lower }}';

        $this->assertEquals('hello wilderness', $this->parse($template, $this->variables));
    }

    public function testSingleStandardStringModifierRelaxed()
    {
        $template = '{{ string | upper }}';

        $this->assertEquals('HELLO WILDERNESS', $this->parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersRelaxed()
    {
        $template = '{{ string | upper | lower }}';

        $this->assertEquals('hello wilderness', $this->parse($template, $this->variables));
    }

    public function testSingleParameterStringModifier()
    {
        $template = "{{ string upper='true' }}";

        $this->assertEquals('HELLO WILDERNESS', $this->parse($template, $this->variables));
    }

    public function testChainedParameterStringModifiers()
    {
        $template = "{{ string upper='true' lower='true' }}";

        $this->assertEquals('hello wilderness', $this->parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierTight()
    {
        $template = '{{ simple|length }}';

        $this->assertEquals('3', $this->parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierRelaxed()
    {
        $template = '{{ simple | length }}';

        $this->assertEquals('3', $this->parse($template, $this->variables));
    }

    public function testChainedStandardArrayModifiersTightOnContent()
    {
        $template = '{{ content|markdown|lower }}';

        $this->assertEquals("<p>paragraph</p>\n", $this->parse($template, $this->variables));
    }

    public function testChainedStandardModifiersRelaxedOnContent()
    {
        $template = '{{ content | markdown | lower }}';

        $this->assertEquals("<p>paragraph</p>\n", $this->parse($template, $this->variables));
    }

    public function testChainedParameterModifiersOnContent()
    {
        $template = "{{ content markdown='true' lower='true' }}";

        $this->assertEquals("<p>paragraph</p>\n", $this->parse($template, $this->variables));
    }

    public function testConditionsWithModifiers()
    {
        $template = "{{ if string|upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals('yes', $this->parse($template, $this->variables));
    }

    public function testConditionsWithRelaxedModifiers()
    {
        $template = "{{ if string | upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals('yes', $this->parse($template, $this->variables));
    }

    public function testTagsWithCurliesInParamsGetsParsed()
    {
        // the variables are inside Test@index
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\Test::class;

        $template = "{{ test variable='{string}' }}";

        $this->assertEquals('Hello wilderness', $this->parse($template, $this->variables));
    }

    public function testDateConditionWithChainedRelaxedModifiersWithSpacesInArguments()
    {
        $template = '{{ if (date | modify_date:+3 years | format:Y) == "2015" }}yes{{ endif }}';

        $this->assertEquals('yes', $this->parse($template, $this->variables));
    }

    public function testArrayModifiersGetParsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', $this->parse($template, $this->variables));
    }

    public function testArrayModifiersOnCollectionsGetParsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', $this->parse($template, [
            'simple' => collect(['one', 'two', 'three']),
        ]));
    }

    public function testRecursiveChildren()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Tests\Fixtures\Addon\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children }}<li>{{ title }}.{{ foo }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One.Bar<ul><li>Two.Bar</li><li>Three.Bar<ul><li>Four.Baz</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, $this->parse($template, ['foo' => 'Bar']));
    }

    public function testRecursiveChildrenWithScope()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Tests\Fixtures\Addon\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children scope="item" }}<li>{{ item:title }}.{{ item:foo }}.{{ foo }}{{ if item:children }}<ul>{{ *recursive item:children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One..Bar<ul><li>Two..Bar</li><li>Three..Bar<ul><li>Four.Baz.Baz</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, $this->parse($template, ['foo' => 'Bar']));
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIteration()
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
            $this->parse('{{ loop }}{{ one }}{{ two }}{{ /loop }}', $variables)
        );
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIterationWithParsing()
    {
        // the variables are inside Test@some_parsing
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\Test::class;

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
            $this->parse('{{ loop }}{{ one }}{{ test:some_parsing var="two" }}{{ two }}{{ /test:some_parsing }}{{ /loop }}', $variables)
        );
    }

    public function testNestedArraySyntax()
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
            $this->parse('{{ hello:world }}[{{ baz }}]{{ /hello:world }}', $variables)
        );

        $this->assertEquals(
            '[one][two]',
            $this->parse('{{ hello:world scope="s" }}[{{ s:baz }}]{{ /hello:world }}', $variables)
        );
    }

    public function testParsesPhpWhenEnabled()
    {
        $this->assertEquals(
            'Hello wilderness!',
            Antlers::parser()->allowPhp()->parse('{{ string }}<?php echo "!"; ?>', $this->variables, [])
        );

        $this->assertEquals(
            'Hello wilderness&lt;?php echo "!"; ?>',
            $this->parse('{{ string }}<?php echo "!"; ?>', $this->variables, [])
        );
    }

    /** @test */
    public function it_doesnt_parse_noparse_tags()
    {
        $parsed = $this->parse('{{ noparse }}{{ string }}{{ /noparse }} {{ string }}', $this->variables);

        $this->assertEquals('{{ string }} Hello wilderness', $parsed);
    }

    /** @test */
    public function it_doesnt_parse_data_in_noparse_modifiers()
    {
        $variables = [
            'string' => 'hello',
            'content' => 'before {{ string }} after',
        ];

        $parsed = $this->parse('{{ content | noparse }} {{ string }}', $variables);

        $this->assertEquals('before {{ string }} after hello', $parsed);
    }

    /** @test */
    public function it_doesnt_parse_data_in_noparse_modifiers_with_null_coalescence()
    {
        $parser = Antlers::parser();

        $variables = [
            'string' => 'hello',
            'content' => 'before {{ string }} after',
        ];
        $parsed = $parser->parse('{{ missing or content | noparse }} {{ string }}', $variables);
        $this->assertEquals('before {{ string }} after hello', $parsed);
    }

    /** @test */
    public function it_doesnt_parse_noparse_tags_inside_callbacks()
    {
        (new class extends Tags {
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

        $parser = Antlers::parser();

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

        $parsed = $parser->parse($template, $this->variables);
        $this->assertEquals($expected, trim($parsed));
    }

    /** @test */
    public function it_doesnt_parse_data_in_noparse_modifiers_inside_callbacks()
    {
        $this->app['statamic.tags']['test'] = \Tests\Fixtures\Addon\Tags\Test::class;

        (new class extends Tags {
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

        $parser = Antlers::parser();

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

        $parsed = $parser->parse($template);
        $this->assertEquals($expected, trim($parsed));
    }

    /** @test */
    public function it_accepts_an_arrayable_object()
    {
        $this->assertEquals(
            'Hello World',
            $this->parse('{{ string }}', new ArrayableObject(['string' => 'Hello World']))
        );
    }

    /** @test */
    public function it_throws_exception_for_non_arrayable_data_object()
    {
        try {
            $this->parse('{{ string }}', new NonArrayableObject(['string' => 'Hello World']));
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [Tests\View\Antlers\NonArrayableObject]', $e->getMessage());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function it_throws_exception_for_unsupported_data_value()
    {
        try {
            $this->parse('{{ string }}', 'string');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Expecting array or object implementing Arrayable. Encountered [string]', $e->getMessage());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function it_gets_augmented_value()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $value = new Value('expected', 'test', $fieldtype);

        $parsed = $this->parse('{{ test }}', ['test' => $value]);

        $this->assertEquals('augmented expected', $parsed);
    }

    /** @test */
    public function it_expands_augmented_value_when_used_as_an_array()
    {
        $fieldtype = new class extends Fieldtype {
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

        $parsed = $this->parse('{{ test }}{{ one }} {{ two }}{{ /test }}', ['test' => $value]);

        $this->assertEquals('HELLO WORLD', $parsed);
    }

    /** @test */
    public function it_gets_nested_values_from_augmentable_objects()
    {
        $value = new AugmentableObject(['foo' => 'bar']);

        $parsed = $this->parse('{{ test:foo }}', ['test' => $value]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    public function it_loops_over_value_object()
    {
        $fieldtype = new class extends Fieldtype {
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

        $parsed = $this->parse('{{ test }}{{ one }} {{ two }} {{ /test }}', ['test' => $value]);

        $this->assertEquals('UNO DOS UNE DEUX ', $parsed);
    }

    /** @test */
    public function it_gets_nested_values_from_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = $this->parse('{{ test:foo }}', ['test' => $value]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    public function it_gets_nested_values_from_nested_value_objects()
    {
        $value = new Value(['foo' => 'bar'], 'test');

        $parsed = $this->parse('{{ nested:test:foo }}', [
            'nested' => [
                'test' => $value,
            ],
        ]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    public function it_gets_nested_values_from_within_nested_value_objects()
    {
        $value = new Value([
            'foo' => ['nested' => 'bar'],
        ], 'test');

        $parsed = $this->parse('{{ nested:test:foo:nested }}', [
            'nested' => [
                'test' => $value,
            ],
        ]);

        $this->assertEquals('bar', $parsed);
    }

    /** @test */
    public function it_parses_value_objects_values_when_configured_to_do_so()
    {
        $fieldtypeOne = new class extends Fieldtype {
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
        $fieldtypeTwo = new class extends Fieldtype {
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

        $this->assertEquals($expected, (string) $this->parse($template, $variables));

        $this->assertEquals(
            'shmaugmented before hello after',
            (string) $this->parse('{{ parseable | replace:aug:shmaug }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before {{ string }} after',
            (string) $this->parse('{{ non_parseable | replace:aug:shmaug }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before hello after',
            (string) $this->parse('{{ parseable replace="aug|shmaug" }}', $variables)
        );

        $this->assertEquals(
            'shmaugmented before {{ string }} after',
            (string) $this->parse('{{ non_parseable replace="aug|shmaug" }}', $variables)
        );
    }

    /** @test */
    public function it_casts_objects_to_string_when_using_single_tags()
    {
        $object = new class {
            public function __toString()
            {
                return 'string';
            }
        };

        $this->assertEquals(
            'string',
            $this->parse('{{ object }}', compact('object'))
        );
    }

    /** @test */
    public function it_doesnt_output_anything_if_object_cannot_be_cast_to_a_string()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot render an object variable as a string: {{ object }}');

        $object = new class {
        };

        $this->assertEquals('', $this->parse('{{ object }}', compact('object')));
    }

    /** @test */
    public function it_casts_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        $arrayableObject = new ArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'foo bar',
            $this->parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $arrayableObject,
            ])
        );
    }

    /** @test */
    public function it_cannot_cast_non_arrayable_objects_to_arrays_when_using_tag_pairs()
    {
        Log::shouldReceive('debug')->once()
            ->with('Cannot loop over non-loopable variable: {{ object }}');

        $nonArrayableObject = new NonArrayableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            '',
            $this->parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $nonArrayableObject,
            ])
        );
    }

    /** @test */
    public function callback_tags_that_return_unparsed_simple_arrays_get_parsed()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template));
    }

    /** @test */
    public function callback_tags_that_return_unparsed_simple_arrays_get_parsed_with_scope()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template));
    }

    /** @test */
    public function callback_tags_that_return_unparsed_multidimensional_arrays_get_parsed()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template, ['string' => 'Hello wilderness']));
    }

    /** @test */
    public function callback_tags_that_return_empty_arrays_get_parsed_with_no_results()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template, $this->variables));
    }

    /** @test */
    public function callback_tags_that_return_collections_get_parsed()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template, $this->variables));
    }

    /** @test */
    public function callback_tags_that_return_value_objects_gets_parsed()
    {
        (new class extends Tags {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype {
                    public function augment($value)
                    {
                        return 'augmented '.$value;
                    }
                };

                return new Value('the value', null, $fieldtype);
            }
        })::register();

        $this->assertEquals('augmented the value', $this->parse('{{ tag }}'));
    }

    /** @test */
    public function callback_tags_that_return_value_objects_with_antlers_gets_parsed()
    {
        (new class extends Tags {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype {
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
            (string) $this->parse('{{ tag }}', ['var' => 'howdy'])
        );
    }

    /** @test */
    public function callback_tags_that_return_value_objects_with_antlers_disabled_does_not_get_parsed()
    {
        (new class extends Tags {
            public static $handle = 'tag';

            public function index()
            {
                $fieldtype = new class extends Fieldtype {
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
            (string) $this->parse('{{ tag }}', ['var' => 'howdy'])
        );
    }

    /** @test */
    public function value_objects_with_antlers_gets_parsed()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $fieldtype->setField(new Field('test', ['antlers' => true]));

        $value = new Value('the value with {{ var }} in it', null, $fieldtype);

        $this->assertEquals(
            'augmented the value with howdy in it',
            (string) $this->parse('{{ test }}', [
                'test' => $value,
                'var' => 'howdy',
            ])
        );
    }

    /** @test */
    public function value_objects_with_antlers_disabled_do_not_get_parsed()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $fieldtype->setField(new Field('test', ['antlers' => false]));

        $value = new Value('the value with {{ var }} in it', null, $fieldtype);

        $this->assertEquals(
            'augmented the value with {{ var }} in it',
            (string) $this->parse('{{ test }}', [
                'test' => $value,
                'var' => 'howdy',
            ])
        );
    }

    /** @test */
    public function it_automatically_augments_augmentable_objects_when_using_tag_pairs()
    {
        $augmentable = new AugmentableObject([
            'one' => 'foo',
            'two' => 'bar',
        ]);

        $this->assertEquals(
            'FOO! bar',
            $this->parse('{{ object }}{{ one }} {{ two }}{{ /object }}', [
                'object' => $augmentable,
            ])
        );
    }

    /** @test */
    public function it_automatically_augments_augmentable_objects_when_returned_from_a_callback_tag()
    {
        (new class extends Tags {
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
            $this->parse('{{ tag }}{{ one }} {{ two }}{{ /tag }}')
        );
    }

    /** @test */
    public function it_automatically_augments_collections_when_using_tag_pairs()
    {
        $augmentable = collect([
            new AugmentableObject(['one' => 'foo', 'two' => 'bar']),
            new AugmentableObject(['one' => 'baz', 'two' => 'qux']),
        ]);

        $this->assertEquals(
            'FOO! bar BAZ! qux ',
            $this->parse('{{ object }}{{ one }} {{ two }} {{ /object }}', [
                'object' => $augmentable,
            ])
        );
    }

    /** @test */
    public function callback_tag_pair_variables_get_context_merged_in_but_nulls_remain_null()
    {
        (new class extends Tags {
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
        $this->assertEquals($expected, $this->parse($template, $context));
    }

    /** @test */
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
        $this->assertEquals($expected, $this->parse($template, $context));
    }

    /** @test */
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
        $this->assertEquals($expected, $this->parse($template, $context));
    }

    /** @test */
    public function it_can_reach_into_the_cascade()
    {
        $cascade = $this->mock(Cascade::class, function ($m) {
            $m->shouldReceive('get')->with('page')->once()->andReturn(['drink' => 'juice']);
            $m->shouldReceive('get')->with('global')->once()->andReturn(['drink' => 'water']);
            $m->shouldReceive('get')->with('menu')->once()->andReturn(['drink' => 'vodka']);
            $m->shouldNotReceive('get')->with('nested');
            $m->shouldNotReceive('get')->with('augmented');
        });

        $parser = Antlers::parser()->cascade($cascade);

        $fieldtype = new class extends Fieldtype {
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

        $this->assertEquals($expected, $parser->parse($template, $context));
    }

    /** @test */
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

        $this->assertEquals($expected, trim($this->parse($template, $context)));
    }

    /** @test */
    public function it_does_not_accept_sequences()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting an associative array');
        $this->parse('', ['foo', 'bar']);
    }

    /** @test */
    public function it_does_not_accept_multidimensional_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting an associative array');
        $this->parse('', [
            ['foo' => 'bar'],
            ['foo' => 'baz'],
        ]);
    }

    /** @test */
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

        $this->assertEquals($expected, $this->parse($template, [
            'array' => [
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux'],
            ],
        ]));
    }

    /** @test */
    public function it_aliases_callback_tag_pair_loop_using_the_as_param()
    {
        (new class extends Tags {
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

        $this->assertEquals($expected, $this->parse($template));
    }

    /** @test */
    public function it_counts_query_builder_results_in_conditions()
    {
        EntryFactory::collection('blog')->create();

        $template = '{{ if entries }}yup{{ else }}nope{{ /if }}';

        $this->assertEquals('yup', $this->parse($template, ['entries' => Entry::query()]));
        $this->assertEquals('yup', $this->parse($template, ['entries' => Entry::query()->where('collection', 'blog')]));
        $this->assertEquals('nope', $this->parse($template, ['entries' => Entry::query()->where('collection', 'dunno')]));
    }

    /** @test */
    public function modifiers_on_tag_pairs_receive_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype {
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
        $this->assertEquals('123', $this->parse('{{ test where="type:yup" }}{{ text }}{{ /test }}', [
            'test' => $value,
            'hello' => 'there',
        ]));
    }

    /** @test */
    public function it_outputs_the_value_when_a_LabeledValue_object_is_used_as_string()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return new LabeledValue('world', 'World');
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $this->assertEquals('world', $this->parse('{{ hello }}', [
            'hello' => $value,
        ]));
    }

    /** @test */
    public function it_can_treat_a_LabeledValue_object_as_an_array()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return new LabeledValue('world', 'World');
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $this->assertEquals(
            'world, world, World',
            $this->parse('{{ hello }}{{ key }}, {{ value }}, {{ label }}{{ /hello }}', [
                'hello' => $value,
            ])
        );
    }

    /** @test */
    public function it_can_access_LabeledValue_properties_by_colon_notation()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                return new LabeledValue('world', 'World');
            }
        };

        $value = new Value('world', 'hello', $fieldtype);

        $vars = ['hello' => $value];

        $this->assertEquals('world', $this->parse('{{ hello:value }}', $vars));
        $this->assertEquals('world', $this->parse('{{ hello:key }}', $vars));
        $this->assertEquals('World', $this->parse('{{ hello:label }}', $vars));
    }

    /** @test */
    public function it_can_use_LabeledValue_objects_in_conditions()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($value)
            {
                $label = is_null($value) ? null : strtoupper($value);

                return new LabeledValue($value, $label);
            }
        };

        $vars = [
            'string' => new Value('foo', 'string', $fieldtype),
            'nully' => new Value(null, 'nully', $fieldtype),
        ];

        $this->assertEquals('true', $this->parse('{{ if string }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->parse('{{ if nully }}true{{ else }}false{{ /if }}', $vars));

        $this->assertEquals('true', $this->parse('{{ if string == "foo" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->parse('{{ if nully == "foo" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->parse('{{ if string == "bar" }}true{{ else }}false{{ /if }}', $vars));
        $this->assertEquals('false', $this->parse('{{ if nully == "bar" }}true{{ else }}false{{ /if }}', $vars));

        $this->assertEquals('true', $this->parse('{{ string ? "true" : "false" }}', $vars));
        $this->assertEquals('false', $this->parse('{{ nully ? "true" : "false" }}', $vars));

        $this->assertEquals('true', $this->parse('{{ string == "foo" ? "true" : "false" }}', $vars));
        $this->assertEquals('false', $this->parse('{{ string == "bar" ? "true" : "false" }}', $vars));

        $this->assertEquals('foo', $this->parse('{{ string or "fallback" }}', $vars));
        $this->assertEquals('FOO', $this->parse('{{ string:label or "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->parse('{{ nully or "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->parse('{{ nully:label or "fallback" }}', $vars));

        $this->assertEquals('foo', $this->parse('{{ string ?? "fallback" }}', $vars));
        $this->assertEquals('FOO', $this->parse('{{ string:label ?? "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->parse('{{ nully ?? "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->parse('{{ nully:label ?? "fallback" }}', $vars));

        $this->assertEquals('fallback', $this->parse('{{ string ?= "fallback" }}', $vars));
        $this->assertEquals('fallback', $this->parse('{{ string:label ?= "fallback" }}', $vars));
        $this->assertEquals('', $this->parse('{{ nully ?= "fallback" }}', $vars));
        $this->assertEquals('', $this->parse('{{ nully:label ?= "fallback" }}', $vars));
    }

    /** @test */
    public function it_can_remove_escaping_characters_from_tenary_output()
    {
        $vars = [
            'seo_title' => "Let's work together",
            'title' => 'Contact',

            'local_office_link' => '',
            'head_office_link' => 'https://statamic.com',
        ];

        $this->assertEquals("Let's work together", $this->parse('{{ seo_title ? seo_title : title }}', $vars));
        $this->assertEquals('Contact', $this->parse('{{ title ? title : seo_title }}', $vars));

        $this->assertEquals('https://statamic.com', $this->parse('{{ local_office_link ? local_office_link : head_office_link }}', $vars));
        $this->assertEquals('https://statamic.com', $this->parse('{{ head_office_link ? head_office_link : local_office_link }}', $vars));
    }

    /** @test */
    public function it_can_remove_escaping_characters_from_tenary_output_with_truth_coalescence()
    {
        $vars = [
            'truthy' => true,
            'string' => "Let's work together",
            'link' => 'https://statamic.com',
        ];

        $this->assertEquals("Let's work together", $this->parse('{{ truthy ?= string }}', $vars));
        $this->assertEquals('https://statamic.com', $this->parse('{{ truthy ?= link }}', $vars));
    }

    /** @test */
    public function empty_collections_are_considered_empty_in_conditions()
    {
        $template = '{{ if stuff }}yes{{ else }}no{{ /if }}';
        $this->assertEquals('no', $this->parse($template, ['stuff' => collect()]));
        $this->assertEquals('yes', $this->parse($template, ['stuff' => collect(['one'])]));
    }

    /** @test */
    public function empty_view_error_bags_are_considered_empty_in_conditions()
    {
        $template = '{{ if errors}}yes{{ else }}no{{ /if }}';
        $viewErrorBag = new ViewErrorBag;
        $messageBag = new MessageBag;

        $this->assertEquals('no', $this->parse($template, ['errors' => $viewErrorBag]));
        $this->assertEquals('yes', $this->parse($template, ['errors' => $viewErrorBag->put('default', new MessageBag)]));
        $this->assertEquals('yes', $this->parse($template, ['errors' => $viewErrorBag->put('form.contact', new MessageBag)]));
    }

    /** @test */
    public function objects_are_considered_truthy()
    {
        $this->assertEquals('yes', $this->parse('{{ if object }}yes{{ else }}no{{ /if }}', ['object' => new \stdClass]));
    }

    /** @test */
    public function parameter_style_modifier_with_colon_prefix_will_get_the_values_from_context()
    {
        $this->assertEquals('Tes Te', $this->parse('{{ word :backspace="one" }} {{ word :backspace="two" }}', [
            'word' => 'Test',
            'one' => 1,
            'two' => 2,
        ]));
    }

    /** @test */
    public function variables_starting_with_if_arent_treated_as_if_statements()
    {
        $this->assertEquals('test', $this->parse('{{ iframe }}', ['iframe' => 'test']));
        $this->assertEquals('test', $this->parse('{{ unlesses }}', ['unlesses' => 'test']));
        $this->assertEquals('test', $this->parse('{{ elseifs }}', ['elseifs' => 'test']));
        $this->assertEquals('test', $this->parse('{{ elseunlessses }}', ['elseunlessses' => 'test']));
    }

    /**
     * @test
     *
     * Somehow when an empty replicator field is used it makes it into the callback array part
     * of the parser with an empty Value object.
     * See https://github.com/statamic/cms/issues/2369
     **/
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

        $this->assertEquals($expected, $this->parse($template, [
            'simple' => new Value([], null, new class extends \Statamic\Fieldtypes\Replicator {
            }),
        ]));
    }

    /** @test */
    public function it_automatically_augments_augmentable_objects_when_looping_with_modifier()
    {
        $loop = [
            new AugmentableObject(['one' => 'foo', 'two' => 'bar']),
            new AugmentableObject(['one' => 'baz', 'two' => 'qux']),
        ];

        $this->assertEquals(
            '<FOO!><bar>',
            (string) Antlers::parse('{{ augmentables limit="1" }}<{{ one }}><{{ two }}>{{ /augmentables }}', ['augmentables' => $loop])
        );
    }

    /** @test */
    public function it_uses_tags_with_single_part_in_conditions()
    {
        (new class extends Tags {
            public static $handle = 'truthy';

            public function index()
            {
                return true;
            }
        })::register();

        (new class extends Tags {
            public static $handle = 'falsey';

            public function index()
            {
                return false;
            }
        })::register();

        $this->assertEquals('yes', $this->parse('{{ if {truthy} }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('yes', $this->parse('{{ if {truthy} == true }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {truthy} == false }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {falsey} }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {falsey} == true }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('yes', $this->parse('{{ if {falsey} == false }}yes{{ else }}no{{ /if }}'));
    }

    /** @test */
    public function it_uses_tags_with_multiple_parts_in_conditions()
    {
        (new class extends Tags {
            public static $handle = 'truthy';

            public function test()
            {
                return true;
            }
        })::register();

        (new class extends Tags {
            public static $handle = 'falsey';

            public function test()
            {
                return false;
            }
        })::register();

        $this->assertEquals('yes', $this->parse('{{ if {truthy:test} }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('yes', $this->parse('{{ if {truthy:test} == true }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {truthy:test} == false }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {falsey:test} }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('no', $this->parse('{{ if {falsey:test} == true }}yes{{ else }}no{{ /if }}'));
        $this->assertEquals('yes', $this->parse('{{ if {falsey:test} == false }}yes{{ else }}no{{ /if }}'));
    }

    /** @test */
    public function it_does_stuff_in_issue_2537()
    {
        $template = '{{ if noindex || segment_1 == "mobile" || get:page > 0 }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->parse($template, ['noindex' => true]));
    }

    /** @test */
    public function it_does_stuff_in_issue_2456()
    {
        $template = '{{ if publication_venue:publication_venue_types:slug !== "journal" and publication_venue:first_year }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->parse($template, [
            'publication_venue' => [
                'first_year' => true,
                'publication_venue_types' => [
                    'slug' => 'notjournal',
                ],
            ],
        ]));
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/2936
     **/
    public function it_compares_to_a_string_that_looks_like_array_access()
    {
        $template = '{{ if test == "price:desc" }}yes{{ else }}no{{ /if }}';

        $this->assertEquals('yes', $this->parse($template, [
            'test' => 'price:desc',
        ]));
    }
}

class NonArrayableObject
{
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
        return $this->toArray();
    }

    public function blueprint()
    {
        FieldtypeRepository::shouldReceive('find')->andReturn(new class extends Fieldtype {
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
