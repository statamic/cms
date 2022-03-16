<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;

class PhpEnabledTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;

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
}
