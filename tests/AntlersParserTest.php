<?php namespace Tests;

use Statamic\API\Term;
use Statamic\API\Taxonomy;
use Statamic\View\Antlers\Template as Antlers;

class AntlersParserTest extends TestCase
{
    private $variables;

    public function setUp()
    {
        parent::setUp();

        $this->variables = [
            'string' => 'Hello wilderness',
            'simple' => ['one', 'two', 'three'],
            'complex' => [
                ['string' => 'the first string'],
                ['string' => 'the second string']
            ],
            'date' => 'June 19 2012',
            'content' => "Paragraph"
        ];
    }

    public function testStringVariable()
    {
        $template = "{{ string }}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testStringVariableWithTightBraces()
    {
        $template = "{{string}}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testListVariable()
    {
        $template = "{{ simple }}{{ value }}{{ /simple }}";

        $this->assertEquals('onetwothree', Antlers::parse($template, $this->variables));
    }

    public function testNonExistantVariablesShouldBeNull()
    {
        $template = "{{ missing }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
    }

    public function testSingleBracesShouldNotBeParsed()
    {
        $template = "{string}";

        $this->assertEquals('{string}', Antlers::parse($template, $this->variables));
    }

    public function testModifiedNonExistantVariablesShouldBeNull()
    {
        $template = "{{ missing|upper }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
    }

    public function testUnclosedArrayVariablePairsShouldBeNull()
    {
        $template = "{{ simple }}";

        $this->assertEquals(null, Antlers::parse($template, $this->variables));
    }

    public function testSingleCondition()
    {
        $template = '{{ if string == "Hello wilderness" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testMultipleAndConditions()
    {
        $template = '{{ if string == "Hello wilderness" && content }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testMultipleOrConditions()
    {
        $should_pass = '{{ if string == "failure" || string == "Hello wilderness" }}yes{{ endif }}';
        $should_fail = '{{ if string == "failure" or string == "womp" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($should_pass, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_fail, $this->variables));
    }

    public function testOrExistanceConditions()
    {
        $should_pass = '{{ if string || strudel }}yes{{ endif }}';
        $should_also_pass = '{{ if strudel or string }}yes{{ endif }}';
        $should_fail = '{{ if strudel || wurst }}yes{{ endif }}';
        $should_also_fail = '{{ if strudel or wurst }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($should_pass, $this->variables));
        $this->assertEquals('yes', Antlers::parse($should_also_pass, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_fail, $this->variables));
        $this->assertEquals(null, Antlers::parse($should_also_fail, $this->variables));
    }

    public function testSingleStandardStringModifierTight()
    {
        $template = "{{ string|upper }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersTight()
    {
        $template = "{{ string|upper|lower }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardStringModifierRelaxed()
    {
        $template = "{{ string | upper }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardStringModifiersRelaxed()
    {
        $template = "{{ string | upper | lower }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleParameterStringModifier()
    {
        $template = "{{ string upper='true' }}";

        $this->assertEquals('HELLO WILDERNESS', Antlers::parse($template, $this->variables));
    }

    public function testChainedParameterStringModifiers()
    {
        $template = "{{ string upper='true' lower='true' }}";

        $this->assertEquals('hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierTight()
    {
        $template = "{{ simple|length }}";

        $this->assertEquals(3, Antlers::parse($template, $this->variables));
    }

    public function testSingleStandardArrayModifierRelaxed()
    {
        $template = "{{ simple | length }}";

        $this->assertEquals(3, Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardArrayModifiersTightOnContent()
    {
        $template = "{{ content|markdown|lower }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testChainedStandardModifiersRelaxedOnContent()
    {
        $template = "{{ content | markdown | lower }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testChainedParameterModifiersOnContent()
    {
        $template = "{{ content markdown='true' lower='true' }}";

        $this->assertEquals("<p>paragraph</p>".PHP_EOL, Antlers::parse($template, $this->variables));
    }

    public function testConditionsWithModifiers()
    {
        $template = "{{ if string|upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals("yes", Antlers::parse($template, $this->variables));
    }

    public function testConditionsWithRelaxedModifiers()
    {
        $template = "{{ if string | upper == 'HELLO WILDERNESS' }}yes{{ endif }}";

        $this->assertEquals("yes", Antlers::parse($template, $this->variables));
    }

    public function testTagsWithCurliesInParamsGetsParsed()
    {
        // the variables are inside Test@index
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $template = "{{ test variable='{string}' }}";

        $this->assertEquals('Hello wilderness', Antlers::parse($template, $this->variables));
    }

    public function testDateConditionWithChainedRelaxedModifiersWithSpacesInArguments()
    {
        $template = '{{ if (date | modify_date:+3 years | format:Y) == "2015" }}yes{{ endif }}';

        $this->assertEquals('yes', Antlers::parse($template, $this->variables));
    }

    public function testArrayModifiersGetParsed()
    {
        $template = '{{ simple limit="1" }}{{ value }}{{ /simple }}';

        $this->assertEquals('one', Antlers::parse($template, $this->variables));
    }

    public function testRecursiveChildren()
    {
        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Foo\Bar\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children }}<li>{{ title }}{{ if children }}<ul>{{ *recursive children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One<ul><li>Two</li><li>Three<ul><li>Four</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, Antlers::parse($template, []));
    }

    public function testRecursiveChildrenWithScope()
    {
        $this->markTestIncomplete();

        // the variables are inside RecursiveChildren@index
        $this->app['statamic.tags']['recursive_children'] = \Foo\Bar\Tags\RecursiveChildren::class;

        $template = '<ul>{{ recursive_children scope="item" }}<li>{{ item:title }}{{ if item:children }}<ul>{{ *recursive item:children* }}</ul>{{ /if }}</li>{{ /recursive_children }}</ul>';

        $expected = '<ul><li>One<ul><li>Two</li><li>Three<ul><li>Four</li></ul></li></ul></li></ul>';

        $this->assertEquals($expected, Antlers::parse($template, []));
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIteration()
    {
        $context = [
            'loop' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]'
                ]
            ]
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            Antlers::parse('{{ loop }}{{ one }}{{ two }}{{ /loop }}', [], $context)
        );
    }

    public function testEmptyValuesAreNotOverriddenByPreviousIterationWithParsing()
    {
        // the variables are inside Test@some_parsing
        $this->app['statamic.tags']['test'] = \Foo\Bar\Tags\Test::class;

        $context = [
            'loop' => [
                [
                    'one' => '[1.1]',
                    'two' => '[1.2]',
                ],
                [
                    'one' => '[2.1]'
                ]
            ]
        ];

        $this->assertEquals(
            '[1.1][1.2][2.1]',
            Antlers::parse('{{ loop }}{{ one }}{{ test:some_parsing of="two" }}{{ two }}{{ /test:some_parsing }}{{ /loop }}', [], $context)
        );
    }

    public function testTermsAreConvertedToArrays()
    {
        $this->markTestSkipped(); // Until taxonomies are reimplemented.

        Taxonomy::shouldReceive('whereHandle')->with('tags')->andReturn(
            new \Statamic\Data\Taxonomies\Taxonomy
        );

        $variables = [
            'tags' => [
                Term::create('foo')->taxonomy('tags')->get(),
                Term::create('bar')->taxonomy('tags')->get(),
            ]
        ];

        $this->assertEquals(
            '[foo][bar]',
            Antlers::parse('{{ tags }}[{{ slug }}]{{ /tags }}', $variables)
        );
    }

    public function testNestedArraySyntax()
    {
        $this->markTestIncomplete();

        $variables = [
            'hello' => [
                'world' => [
                    ['baz' => 'one'],
                    ['baz' => 'two'],
                ],
                'id' => '12345'
            ]
        ];

        $this->assertEquals(
            '[one][two]',
            Antlers::parse('{{ hello:world }}[{{ baz }}]{{ /hello:world }}', $variables)
        );

        $this->assertEquals(
            '[one][two]',
            Antlers::parse('{{ hello:world scope="s" }}[{{ s:baz }}]{{ /hello:world }}', $variables)
        );
    }
}
