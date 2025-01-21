<?php

namespace Tests\Antlers\Runtime;

use Carbon\Carbon;
use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\Antlers\ParserTestCase;

class CoreModifiersTest extends ParserTestCase
{
    protected $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'value' => 5,
            'base_url' => 'https://www.google.com/search',
            'list' => ['one', 'two', 'three'],
            'focus' => '50-30',
            'phrase' => 'test phrase',
            'count_substr' => 'testest',
            'count_substr_var_ref' => 'est',
            'title' => 'As the World Turns',
            'chunks' => [
                ['name' => 'one'],
                ['name' => 'two'],
                ['name' => 'three'],
            ],
            'collapse' => [
                ['one'],
                ['two'],
                ['three'],
            ],
            'chunkSize' => 3,
            'checklist' => [
                'zebra',
                'hippo',
                'hyena',
                'giraffe',
                'zebra',
                'hippo',
                'hippo',
                'hippo',
                'hippo',
            ],
            'complex' => [
                [
                    'last_name' => 'Zebra',
                    'first_name' => 'Zealous',
                ],
                [
                    'last_name' => 'Alpha',
                    'first_name' => 'Altruistic',
                ],
                [
                    'last_name' => 'Bravo',
                    'first_name' => 'Blathering',
                ],
            ],
            'games' => [
                [
                    'feeling' => 'love',
                    'title' => 'Dominion',
                ],
                [
                    'feeling' => 'love',
                    'title' => 'Netrunner',
                ],
                [
                    'feeling' => 'hate',
                    'title' => 'Chutes and Ladders',
                ],
            ],
            'remove_left_var' => 'https://',
            'test_currency_symbol' => '£32.00',
        ];
    }

    protected function resultOf($text)
    {
        return $this->renderString($text, $this->data, true);
    }

    public function test_starts_with_accepts_special_characters()
    {
        // Issue: https://github.com/statamic/cms/issues/5128
        $this->assertSame('yes', $this->resultOf('{{ if test_currency_symbol|starts_with:£ }}yes{{else}}no{{/if}}'));
        $this->assertSame('yes', $this->resultOf('{{ if test_currency_symbol|starts_with("£") }}yes{{else}}no{{/if}}'));
        $this->assertSame('no', $this->resultOf('{{ if remove_left_var|starts_with:£ }}yes{{else}}no{{/if}}'));
    }

    public function test_math_add()
    {
        $this->assertSame('10', $this->resultOf('{{ value | add: 5 }}'));
    }

    public function test_query_param()
    {
        $this->assertSame('https://www.google.com/search?q=test', $this->resultOf('{{ base_url|add_query_param:q:test }}'));
        $this->assertSame('https://www.google.com/search?q=test', $this->resultOf('{{ base_url|add_query_param:{"q"}:{"test"} }}'));
    }

    public function test_ampersand_list()
    {
        $this->assertSame('one, two, and three', $this->resultOf('{{ list |ampersand_list:and:true }}'));
        $this->assertSame('one, two, and three', $this->resultOf('{{ list |ampersand_list:{"and"}:{"true"} }}'));
    }

    public function test_ascii()
    {
        $this->assertSame('u', $this->resultOf('{{ "ü" |ascii }}'));
    }

    public function test_at()
    {
        $this->assertSame('e', $this->resultOf('{{ "Test"|at:1 }}'));
        $this->assertSame('e', $this->resultOf('{{ "Test"|at:{1+0} }}'));
    }

    public function test_background_position()
    {
        $this->assertSame('50% 30%', $this->resultOf('{{ focus | background_position }}'));
    }

    public function test_backspace()
    {
        $this->assertSame('te', $this->resultOf('{{ "test" | backspace: 2 }}'));
        $this->assertSame('te', $this->resultOf('{{ "test" | backspace:2 }}'));
        $this->assertSame('te', $this->resultOf('{{ "test" | backspace: {(2-2)+2} }}'));
    }

    public function test_camelize()
    {
        $this->assertSame('testPhrase', $this->resultOf('{{ phrase | camelize }}'));
    }

    public function test_cdata()
    {
        $this->assertSame('<![CDATA[test phrase]]>', $this->resultOf('{{ phrase | cdata }}'));
    }

    public function test_ceil()
    {
        $this->assertSame('5', $this->resultOf('{{ 4.3 | ceil }}'));
    }

    public function test_chunk()
    {
        $this->assertSame('onetwothree', $this->resultOf('{{ chunks chunk="3" }}{{ chunk }}{{ name }}{{ /chunk }}{{ /chunks }}'));
        $this->assertSame('onetwothree', $this->resultOf('{{ chunks chunk="{2 + 1}" }}{{ chunk }}{{ name }}{{ /chunk }}{{ /chunks }}'));
        $this->assertSame('onetwothree', $this->resultOf('{{ chunks :chunk="chunkSize" }}{{ chunk }}{{ name }}{{ /chunk }}{{ /chunks }}'));
    }

    public function test_collapse()
    {
        $this->assertSame('onetwothree', $this->resultOf('{{ collapse | collapse }}{{ value }}{{ /collapse }}'));
    }

    public function test_collapse_whitespace()
    {
        $this->assertSame('te st', $this->resultOf('{{ "te    st" | collapse_whitespace }}'));
    }

    public function test_contains()
    {
        $template = <<<'EOT'
{{ if list|contains:one }}yep{{ else }}no{{/if}}
EOT;

        $this->assertSame('yep', $this->resultOf($template));
    }

    public function test_contains_all()
    {
        $template = <<<'EOT'
{{ if list|contains:one:two:three }}yep{{ else }}no{{/if}}
EOT;

        $this->assertSame('yep', $this->resultOf($template));
    }

    public function test_contains_any()
    {
        $template = <<<'EOT'
{{ if list|contains:one:three }}yep{{ else }}no{{/if}}
EOT;

        $this->assertSame('yep', $this->resultOf($template));
    }

    public function test_count()
    {
        $this->assertSame('3', $this->resultOf('{{ list | count }}'));
    }

    public function test_count_substring()
    {
        $this->assertSame('2', $this->resultOf('{{ count_substr count_substring="est" }}'));
        $this->assertSame('2', $this->resultOf('{{ count_substr :count_substring="count_substr_var_ref" }}'));
        $this->assertSame('2', $this->resultOf('{{ count_substr count_substring="{"est"}" }}'));
    }

    public function test_dashify()
    {
        $this->assertSame('test-phrase', $this->resultOf('{{ phrase |dashify }}'));
    }

    public function test_wrap()
    {
        $this->assertSame('<h1 class="fast furious">As the World Turns</h1>', $this->resultOf('{{ title | wrap:h1.fast.furious }}'));
        $this->assertSame('<h1 class="fast furious">As the World Turns</h1>', $this->resultOf('{{ title | wrap:{"h1.fast.furious"} }}'));
    }

    public function test_word_count()
    {
        $this->assertSame('8', $this->resultOf('{{  "There are probably seven words in this sentence."|word_count }}'));
    }

    public function test_where()
    {
        $template = <<<'EOT'
{{ games where="feeling|love" }}{{ title}}{{ /games }}
EOT;

        $this->assertSame('DominionNetrunner', $this->resultOf($template));

        $template = <<<'EOT'
{{ games where="{"feeling"}|{"love"}" }}{{ title}}{{ /games }}
EOT;

        $this->assertSame('DominionNetrunner', $this->resultOf($template));
    }

    public function test_unique()
    {
        $this->assertSame('zebra, hippo, hyena, giraffe', $this->resultOf('{{ checklist | unique | list }}'));
    }

    public function test_underscored()
    {
        $this->assertSame('test_phrase', $this->resultOf('{{ phrase |underscored }}'));
    }

    public function test_sort()
    {
        $this->assertSame('AltruisticAlphaBlatheringBravoZealousZebra', $this->resultOf('{{ complex sort="last_name" }}{{ first_name }}{{ last_name }}{{ /complex }}'));

        $this->assertSame('ZealousZebraBlatheringBravoAltruisticAlpha', $this->resultOf('{{ complex sort="last_name|desc" }}{{ first_name }}{{ last_name }}{{ /complex }}'));
    }

    public function test_repeat()
    {
        $this->assertSame('testtesttest', $this->resultOf('{{ "test" | repeat:3 }}'));
    }

    public function test_remove_right()
    {
        $this->assertSame('https://laravel.com', $this->resultOf('{{ "https://laravel.com/" |remove_right:/ }}'));
    }

    public function test_remove_left()
    {
        $this->assertSame('laravel.com/', $this->resultOf('{{ "https://laravel.com/" |remove_left:"https://" }}'));
        $this->assertSame('laravel.com/', $this->resultOf('{{ "https://laravel.com/" |remove_left:{remove_left_var} }}'));
    }

    public function test_lclast()
    {
        $this->assertSame('tEST', $this->resultOf('{{ "TEST" | lcfirst }}'));
    }

    public function test_is_numeric()
    {
        $this->assertSame('yep', $this->resultOf('{{ if "123456"|is_numeric }}yep{{ /if}}'));
    }

    public function test_shorthand_syntax_accepts_space_inside_strings()
    {
        $this->assertSame('test', $this->resultOf('{{ phrase | explode:" " | first }}'));
        $this->assertSame('test', $this->resultOf('{{ phrase | explode: " " | first }}'));
        $this->assertSame('test', $this->resultOf("{{ phrase | explode: ' ' | first }}"));
        $this->assertSame('test', $this->resultOf("{{ phrase | explode:' ' | first }}"));
    }

    public function test_shorthand_syntax_accepts_strings_with_leading_whitespace()
    {
        $this->assertSame('test', $this->resultOf('{{ phrase | explode:                   " " | first }}'));
        $this->assertSame('test', $this->resultOf("{{ phrase | explode:                   ' ' | first }}"));
    }

    public function test_shorthand_syntax_can_handle_string_escape_sequences()
    {
        $template = <<<'EOT'
{{ "\"\n\t|||||:::::||:||\\'\\" }}
EOT;
        $this->assertSame("\"\n\t|||||:::::||:||\\'\\", $this->renderString($template));

        $modifierTemplate = <<<'EOT'
{{ "\"\n\t|||||hello:::::||:||\\'\\" | remove_right: "\"\n\t|||||hello:::::||:||\\'\\" }}
EOT;

        $this->assertSame('', $this->renderString($modifierTemplate));

        $modifierTemplateTwo = <<<'EOT'
{{ "\"\n\t|||||hello:::::||:||\\'\\" | remove_right: "\"\n\t|||||hello!:::::||:||\\'\\" }}
EOT;
        $this->assertSame("\"\n\t|||||hello:::::||:||\\'\\", $this->renderString($modifierTemplateTwo));
    }

    public function test_group_by_modifier()
    {
        $data = [
            'field' => [
                [
                    'title' => 'Test',
                    'collection' => [
                        'title' => 'a',
                    ],
                ],
                [
                    'title' => 'Test 2',
                    'collection' => [
                        'title' => 'a',
                    ],
                ],
                [
                    'title' => 'Test 3',
                    'collection' => [
                        'title' => 'b',
                    ],
                ],
                [
                    'title' => 'Test 4',
                    'collection' => [
                        'title' => 'a',
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
{{ field group_by="collection:title" }}
{{ groups }}
{{ group }}-
{{ items }}
{{ title }}--
{{ /items }}
{{ /groups }}
{{ /field }}
EOT;

        $expected = <<<'EOT'
a-

Test--

Test 2--

Test 4--


b-

Test 3--
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data, true)));
    }

    public function test_modifiers_in_dynamic_bindings_with_tight_spacing_doesnt_error()
    {
        $template = <<<'EOT'
{{ foreach :array="'one,two,three' | explode:," }}{{ value }}-{{ /foreach }}
EOT;

        $this->assertSame('one-two-three-', $this->renderString($template, [], true));
    }

    public function test_raw_shorthand_modifier_returns_raw_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }

            public function shallowAugment($data)
            {
                return $data.' shallow';
            }
        };

        $value = new Value('test', null, $fieldtype);

        $this->assertSame('TEST!', $this->renderString('{{ variable }}', ['variable' => $value]));
        $this->assertSame('test', $this->renderString('{{ variable | raw }}', ['variable' => $value], true));
    }

    public function test_raw_modifier_returns_raw_value_on_antlers_enabled_field()
    {
        // using markdown in this test just because its augmentation is simple.
        $fieldtype = FieldtypeRepository::find('markdown');
        $fieldtype->setField(new Field('test', ['antlers' => true]));

        $value = new Value("# heading\nparagraph {{ foo }}", 'test', $fieldtype);

        $vars = [
            'test' => $value,
            'foo' => 'bar',
        ];

        $this->assertSame("<h1>heading</h1>\n<p>paragraph bar</p>\n", $this->renderString('{{ test }}', $vars));
        $this->assertSame("# heading\nparagraph {{ foo }}", $this->renderString('{{ test | raw }}', $vars));
        // Ensure other modifiers still work
        $this->assertSame("<H1>HEADING</H1>\n<P>PARAGRAPH BAR</P>\n", $this->renderString('{{ test | upper }}', $vars));
        $this->assertSame("# HEADING\nPARAGRAPH {{ FOO }}", $this->renderString('{{ test | raw | upper }}', $vars));
    }

    public function test_explode_on_tag_pairs()
    {
        // Issue: https://github.com/statamic/cms/issues/4979

        // Shorthand from context.
        $template = <<<'EOT'
Start:{{ string | explode:, }}<{{ value }}>{{ /string }}:End
EOT;

        $this->assertSame(
            'Start:<testing><explode><modifiers>:End',
            $this->renderString($template, [
                'string' => 'testing,explode,modifiers',
            ], true)
        );

        // Shorthand from literal.
        $template = <<<'EOT'
{{#
    Test from string literal. The self-closing here
    is important since it removes this from the
    tag-pairing algorithm's list. Tag matching
    prefers non-modifier start tags.
#}}
{{ string = 'testing,explode,modifiers'; /}}

{{ string | explode:, }}<{{ value }}>{{ /string }}
EOT;

        $this->assertSame(
            '<testing><explode><modifiers>',
            trim($this->renderString($template, [], true))
        );

        // Param-style from context.
        $template = <<<'EOT'
Start:{{ string explode="," }}<{{ value }}>{{ /string }}:End
EOT;

        $this->assertSame(
            'Start:<testing><explode><modifiers>:End',
            $this->renderString($template, [
                'string' => 'testing,explode,modifiers',
            ], true)
        );

        // Param-style space delimited.
        $template = <<<'EOT'
Start:{{ string explode=" " }}<{{ value }}>{{ /string }}:End
EOT;

        $this->assertSame(
            'Start:<testing><explode><modifiers>:End',
            $this->renderString($template, [
                'string' => 'testing explode modifiers',
            ], true)
        );

        // Method-style space delimited.
        $template = <<<'EOT'
Start:{{ string | explode(' ') }}<{{ value }}>{{ /string }}:End
EOT;

        $this->assertSame(
            'Start:<testing><explode><modifiers>:End',
            $this->renderString($template, [
                'string' => 'testing explode modifiers',
            ], true)
        );
    }

    public function test_runtime_maintains_arrays_of_objects()
    {
        $entryOne = new SimpleEntryObject();
        $entryOne->date = Carbon::parse('October 1st, 2012');
        $entryOne->title = 'Title One';

        $entryTwo = new SimpleEntryObject();
        $entryTwo->date = Carbon::parse('November 1st, 2012');
        $entryTwo->title = 'The Second Title';

        $template = <<<'EOT'
{{ entries group_by="date|M" }}
{{ groups scope="month" }}
<{{ month:group }}>
{{ items }}
<{{ title }}><{{ title_length }}>
{{ /items }}
{{ /groups }}
{{ /entries }}
EOT;

        $expected = <<<'EOT'
<Oct>

<Title One><9>


<Nov>

<The Second Title><16>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [
            'entries' => [
                $entryOne, $entryTwo,
            ],
        ], true)));
    }

    public function test_dynamic_binding_is_resolved_on_modifier_parameters()
    {
        $template = <<<'EOT'
{{ title :ensure_right="title | upper" }}
EOT;

        $this->assertSame('As the World TurnsAS THE WORLD TURNS', $this->renderString($template, $this->data, true));
    }

    public function test_null_values_on_count_does_not_trigger_error()
    {
        $template = <<<'EOT'
{{ if variable|count > 0 }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('No', $this->renderString($template, [], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => null], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => []], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => collect()], true));
        $this->assertSame('Yes', $this->renderString($template, ['variable' => ['One']], true));

        $template = <<<'EOT'
{{ if {variable count="count"} > 0 }}Yes{{ else }}No{{ /if }}
EOT;

        $this->assertSame('No', $this->renderString($template, [], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => null], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => []], true));
        $this->assertSame('No', $this->renderString($template, ['variable' => collect()], true));
        $this->assertSame('Yes', $this->renderString($template, ['variable' => ['One']], true));
    }
}

class SimpleEntryObject implements Arrayable
{
    public $title = '';

    public $date = null;

    public function toAugmentedArray()
    {
        return [
            'title' => $this->title,
            'date' => $this->date,
            'title_length' => strlen($this->title),
        ];
    }

    public function toDeferredAugmentedArray()
    {
        return $this->toAugmentedArray();
    }

    public function toArray()
    {
        return $this->toAugmentedArray();
    }
}
