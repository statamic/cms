<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Text;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\Factories\EntryFactory;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class PhpEnabledTest extends ParserTestCase
{
    use FakesViews,
        PreventSavingStacheItemsToDisk;

    public function test_php_has_access_to_scope_data()
    {
        $data = [
            'string' => 'wilderness',
        ];

        $this->assertEquals(
            'Hello wildernessWILDERNESS!',
            (string) $this->parser($data)->allowPhp()->parse('Hello {{ string }}<?php echo strtoupper($string); echo "!"; ?>', $data)
        );
    }

    public function test_php_can_be_used_to_output_evaluated_antlers()
    {
        // This test covers existing Antlers + PHP behavior.

        $template = <<<'EOT'
<?php for ($i = 0; $i < 5; $i++) { ?>
{{ title }}
<?php } ?>
EOT;

        $expected = <<<'EOT'
Hello, there!
Hello, there!
Hello, there!
Hello, there!
Hello, there!

EOT;

        $data = ['title' => 'Hello, there!'];
        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings((string) $this->parser($data)->allowPhp()->parse($template, $data));

        $this->assertSame($expected, $result);
    }

    public function test_php_variable_access_inside_loops()
    {
        $data = [
            'title' => 'Hello, world!',
            'articles' => [
                ['title' => 'Article One'],
                ['title' => 'Article Two'],
                ['title' => 'Article Three'],
            ],
        ];

        $template = <<<'EOT'
{{ title }}

<ul>
{{ articles }}
    <li><?php echo $title; ?></li>
{{ /articles }}
</ul>
EOT;

        $expected = <<<'EOT'
Hello, world!

<ul>
    <li>Article One</li>
    <li>Article Two</li>
    <li>Article Three</li>

</ul>
EOT;
        $expected = StringUtilities::normalizeLineEndings($expected);
        $result = StringUtilities::normalizeLineEndings((string) $this->parser($data)->allowPhp()->parse($template, $data));

        $this->assertSame($expected, $result);
    }

    public function test_php_inside_user_content_when_configured_to_do_so()
    {
        // Allowing PHP in user content feels dangerous as a default
        // behavior, and is now disabled by default. However, it
        // can turned back on through the Antlers config file.

        $config = new RuntimeConfiguration();
        $config->allowPhpInUserContent = true;

        $fieldType = new class extends Fieldtype
        {
            public function augment($value)
            {
                return $value;
            }

            public function config(?string $key = null, $fallback = null)
            {
                return true;
            }
        };

        $value = new Value('<?php echo "HI"; ?>', 'phpvalue', $fieldType);

        $data = [
            'phpvalue' => $value,
            'title' => 'Title!',
            'subtitle' => 'Sub Title!',
        ];

        $template = <<<'EOT'
<?php echo 'Hello!'; ?>
{{ title }}
{{ phpvalue }}
{{ subtitle }}
EOT;

        $expected = <<<'EOT'
Hello!Title!
HI
Sub Title!
EOT;

        $expected = StringUtilities::normalizeLineEndings($expected);

        $results = StringUtilities::normalizeLineEndings(
            (string) $this->parser($data)->setRuntimeConfiguration($config)->allowPhp()->parse($template, $data)
        );

        $this->assertSame($expected, $results);
    }

    public function test_php_inside_user_content_can_be_disabled()
    {
        $config = new RuntimeConfiguration();
        $config->allowPhpInUserContent = false;

        $fieldType = new class extends Fieldtype
        {
            public function augment($value)
            {
                return $value;
            }

            public function config(?string $key = null, $fallback = null)
            {
                return true;
            }
        };

        $value = new Value('<?php echo "HI"; ?>', 'phpvalue', $fieldType);

        $data = [
            'phpvalue' => $value,
            'title' => 'Title!',
            'subtitle' => 'Sub Title!',
        ];

        $template = <<<'EOT'
<?php echo 'Hello!'; ?>
{{ title }}
{{ phpvalue }}
{{ subtitle }}
EOT;

        $expected = <<<'EOT'
Hello!Title!
&lt;?php echo "HI"; ?>
Sub Title!
EOT;

        $expected = StringUtilities::normalizeLineEndings($expected);

        $results = StringUtilities::normalizeLineEndings(
            (string) $this->parser($data)->setRuntimeConfiguration($config)->allowPhp()->parse($template, $data)
        );

        $this->assertSame($expected, $results);
    }

    public function test_implicit_antlers_php_node()
    {
        $template = <<<'EOT'
{{ var_1 = 'blog'; var_2 = 'news'; }}
<p>{{ title }}</p>

{{?
$articles = \Statamic\Facades\Entry::query()->where('collection', $var_1)->limit(3)->get();
$news = \Statamic\Facades\Entry::query()->where('collection', $var_2)->limit(5)->get();
?}}


{{ articles }}
================ Blog ================
{{ id | length }}
Count: {{ count }}
Index: {{ index }}
First: {{ first | bool_string }}
Last: {{ last | bool_string }}
======================================
{{ /articles }}

{{ news }}
================ News ================
{{ id | length }}
Count: {{ count }}
Index: {{ index }}
First: {{ first | bool_string }}
Last: {{ last | bool_string }}
======================================
{{ /news }}
EOT;

        $expected = <<<'EOT'

<p>Antlers PHP Node Test</p>





================ Blog ================
36
Count: 1
Index: 0
First: true
Last: false
======================================

================ Blog ================
36
Count: 2
Index: 1
First: false
Last: false
======================================

================ Blog ================
36
Count: 3
Index: 2
First: false
Last: true
======================================



================ News ================
36
Count: 1
Index: 0
First: true
Last: false
======================================

================ News ================
36
Count: 2
Index: 1
First: false
Last: false
======================================

================ News ================
36
Count: 3
Index: 2
First: false
Last: false
======================================

================ News ================
36
Count: 4
Index: 3
First: false
Last: false
======================================

================ News ================
36
Count: 5
Index: 4
First: false
Last: true
======================================

EOT;

        $data = [
            'title' => 'Antlers PHP Node Test',
        ];

        $entryFactory = new EntryFactory();
        for ($i = 0; $i < 3; $i++) {
            $entryFactory->collection('blog')->create();
        }

        for ($i = 0; $i < 5; $i++) {
            $entryFactory->collection('news')->create();
        }

        $results = StringUtilities::normalizeLineEndings(
            (string) $this->parser($data)->allowPhp()->parse($template, $data)
        );

        $expected = StringUtilities::normalizeLineEndings($expected);

        $this->assertSame($expected, $results);
    }

    public function test_antlers_php_node_can_return_assignments()
    {
        $template = <<<'EOT'
{{ var_1 = 'blog'; var_2 = 'news'; }}
<p>{{ title }}</p>

{{?<?php
$articles = \Statamic\Facades\Entry::query()->where('collection', $var_1)->limit(3)->get();
$news = \Statamic\Facades\Entry::query()->where('collection', $var_2)->limit(5)->get();
?>?}}

{{ articles }}
================ Blog ================
{{ id | length }}
Count: {{ count }}
Index: {{ index }}
First: {{ first | bool_string }}
Last: {{ last | bool_string }}
======================================
{{ /articles }}

{{ news }}
================ News ================
{{ id | length }}
Count: {{ count }}
Index: {{ index }}
First: {{ first | bool_string }}
Last: {{ last | bool_string }}
======================================
{{ /news }}
EOT;

        $expected = <<<'EOT'

<p>Antlers PHP Node Test</p>




================ Blog ================
36
Count: 1
Index: 0
First: true
Last: false
======================================

================ Blog ================
36
Count: 2
Index: 1
First: false
Last: false
======================================

================ Blog ================
36
Count: 3
Index: 2
First: false
Last: true
======================================



================ News ================
36
Count: 1
Index: 0
First: true
Last: false
======================================

================ News ================
36
Count: 2
Index: 1
First: false
Last: false
======================================

================ News ================
36
Count: 3
Index: 2
First: false
Last: false
======================================

================ News ================
36
Count: 4
Index: 3
First: false
Last: false
======================================

================ News ================
36
Count: 5
Index: 4
First: false
Last: true
======================================

EOT;

        $data = [
            'title' => 'Antlers PHP Node Test',
        ];

        $entryFactory = new EntryFactory();
        for ($i = 0; $i < 3; $i++) {
            $entryFactory->collection('blog')->create();
        }

        for ($i = 0; $i < 5; $i++) {
            $entryFactory->collection('news')->create();
        }

        $results = StringUtilities::normalizeLineEndings(
            (string) $this->parser($data)->allowPhp()->parse($template, $data)
        );

        $expected = StringUtilities::normalizeLineEndings($expected);

        $this->assertSame($expected, $results);
    }

    public function test_antlers_php_node_does_not_remove_literal()
    {
        $template = <<<'EOT'
{{? $var_1 = 'blog'; $var_2 = 'news'; ?}}ABC{{ var_2 }}
EOT;

        $this->assertSame('ABCnews', $this->renderString($template));
    }

    public function test_antlers_php_echo_node()
    {
        $template = <<<'EOT'
{{? $var = 'hi!'; ?}}
<p>Literal Content. {{$ $var $}}<END></p>
EOT;

        $this->assertSame('<p>Literal Content. hi!<END></p>', trim($this->renderString($template)));
    }

    public function test_php_node_assignments_within_loops()
    {
        mt_srand(1234);
        $data = [
            'collection' => [
                'articles' => [
                    'one',
                    'two',
                    'three',
                    'four',
                    'five',
                ],
            ],
        ];

        $template = <<<'EOT'
{{ collection:articles }}
{{? $rand = mt_rand(1,4); ?}}
<{{ value }}><{{ rand }}>
{{ /collection:articles }}
EOT;

        $expected = <<<'EOT'
<one><4>


<two><4>


<three><3>


<four><2>


<five><1>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, $data)));
    }

    public function test_assignments_from_php_nodes()
    {
        $template = <<<'EOT'
{{?
    $value_one = 100;
    $value_two = 0;
?}}

{{ loop from="1" to="5" }}
{{? $value_one += 5; ?}}
{{? $value_two += 5; ?}}
{{ /loop }}

{{ value_one += 1000; value_two += 1000; }}

<value_one: {{ value_one }}>
<value_two: {{ value_two }}>
EOT;

        $result = $this->renderString($template, [], true);
        $this->assertStringContainsString('<value_one: 1125>', $result);
        $this->assertStringContainsString('<value_two: 1025>', $result);
    }

    public function test_updating_variables_within_scope_using_php()
    {
        $data = [
            'blocks' => [
                [
                    'type' => 'the_block',
                ],
            ],
        ];

        $outerPartial = <<<'EOT'
Outer Partial Before: {{ view.blocks }}{{ type }}{{ /view.blocks }}
{{ partial:inner_partial :blocks="blocks" /}}
Outer Partial After: {{ view.blocks }}{{ type }}{{ /view.blocks }}
EOT;

        $innerPartial = <<<'EOT'
Inner Partial Before: {{ view.blocks }}{{ type }} {{ /view.blocks }}

{{ if view.blocks.0 && view.blocks.0.type != 'hero_block' }}
    {{?
        array_unshift($view['blocks'], [
            'type' => 'hero_block',
            'simple_bard_field' => [
                'type' => 'text',
                'text' => 'The Text',
            ],
        ]);
    ?}}
{{ /if }}

Inner Partial After: {{ view.blocks }}{{ type }} {{ /view.blocks }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('outer_partial', $outerPartial);
        $this->viewShouldReturnRaw('inner_partial', $innerPartial);

        $expected = <<<'EXPECTED'
Outer Partial Before: the_block
Inner Partial Before: the_block




Inner Partial After: hero_block the_block
Outer Partial After: the_block
EXPECTED;

        $this->assertSame(
            (string) str($expected)->squish(),
            (string) str($this->renderString('{{ partial:outer_partial :blocks="blocks" /}}', $data))->squish(),
        );
    }

    public function test_variables_created_inside_php_do_not_override_injected_values()
    {
        $this->withFakeViews();

        $partial = <<<'EOT'
{{? $title = 'The Title'; ?}}

Partial: {{ title }}
EOT;

        $this->viewShouldReturnRaw('the_partial', $partial);

        $template = <<<'EOT'
Before: {{ title }}
{{ partial:the_partial /}}
After: {{ title }}
EOT;

        $expected = <<<'EXPECTED'
Before: The Original Title


Partial: The Title
After: The Original Title
EXPECTED;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Original Title']),
        );

        $template = <<<'EOT'
Before: {{ title }}
{{ partial:the_partial :title="title" /}}
After: {{ title }}
EOT;

        $this->assertSame(
            $expected,
            $this->renderString($template, ['title' => 'The Original Title']),
        );
    }

    public function test_disabled_php_echo_node_inside_user_values()
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textContent = <<<'TEXT'
Text: {{$ Str::upper('hello, world.') $}}
TEXT;

        $textFieldtype->setField($field);
        $value = new Value($textContent, 'text_field', $textFieldtype);

        Log::shouldReceive('warning')
            ->once()
            ->with("PHP Node evaluated in user content: {{\$ Str::upper('hello, world.') \$}}", [
                'file' => null,
                'trace' => [],
                'content' => " Str::upper('hello, world.') ",
            ]);

        $result = $this->renderString('{{ text_field }}', ['text_field' => $value]);

        $this->assertSame('Text: ', $result);

        GlobalRuntimeState::$allowPhpInContent = true;

        $result = $this->renderString('{{ text_field }}', ['text_field' => $value]);

        $this->assertSame('Text: HELLO, WORLD.', $result);

        GlobalRuntimeState::$allowPhpInContent = false;
    }

    public function test_disabled_php_node_inside_user_values()
    {
        $textFieldtype = new Text();
        $field = new Field('text_field', [
            'type' => 'text',
            'antlers' => true,
        ]);

        $textContent = <<<'TEXT'
Text: {{? echo Str::upper('hello, world.') ?}}
TEXT;

        $textFieldtype->setField($field);
        $value = new Value($textContent, 'text_field', $textFieldtype);

        Log::shouldReceive('warning')
            ->once()
            ->with("PHP Node evaluated in user content: {{? echo Str::upper('hello, world.') ?}}", [
                'file' => null,
                'trace' => [],
                'content' => " echo Str::upper('hello, world.') ",
            ]);

        $result = $this->renderString('{{ text_field }}', ['text_field' => $value]);

        $this->assertSame('Text: ', $result);

        GlobalRuntimeState::$allowPhpInContent = true;

        $result = $this->renderString('{{ text_field }}', ['text_field' => $value]);

        $this->assertSame('Text: HELLO, WORLD.', $result);

        GlobalRuntimeState::$allowPhpInContent = false;
    }
}
