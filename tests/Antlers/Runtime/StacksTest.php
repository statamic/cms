<?php

namespace Tests\Antlers\Runtime;

use Tests\Antlers\ParserTestCase;
use Tests\FakesContent;
use Tests\FakesViews;

class StacksTest extends ParserTestCase
{
    use FakesContent;
    use FakesViews;

    public function test_basic_stacks_work()
    {
        $template = <<<'EOT'
BEFORE{{ stack:scripts }}AFTER

{{ push:scripts }}
Push 1
{{ /push:scripts }}

{{ prepend:scripts }}
Prepend 1
{{ /prepend:scripts }}

{{ push:scripts }}
Push 2
{{ /push:scripts }}

{{ prepend:scripts }}
Prepend 2
{{ /prepend:scripts }}
EOT;

        $this->assertSame('BEFOREPrepend 2Prepend 1Push 1Push 2AFTER', trim($this->renderString($template, [])));
    }

    public function test_stacks_from_partials()
    {
        $template = <<<'EOT'
BEFORE{{ stack:scripts }}AFTER

{{ partial:stacks }}
EOT;

        $this->assertSame('BEFOREPrepend 2Prepend 1Push 1Push 2AFTER', trim($this->renderString($template, [], true)));
    }

    public function test_stacks_and_sections_work_from_partials()
    {
        $template = <<<'EOT'
{{ partial:stacksections }}
{{ stack:the_stack }}
{{ yield:the_section }}

{{ push:the_stack }}<More stack content>{{ /push:the_stack }}

EOT;

        $expected = <<<'EOT'
<The stack content><More stack content>
<Section Content>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        // The section content should be replaced with "Different Section Content" since it is processed _after_ the partial.
        $template = <<<'EOT'
{{ partial:stacksections }}
{{ stack:the_stack }}
{{ yield:the_section }}

{{ push:the_stack }}<More stack content>{{ /push:the_stack }}
{{ section:the_section }}<Different Section Content>{{ /section:the_section }}
EOT;

        $expected = <<<'EOT'
<The stack content><More stack content>
<Different Section Content>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }

    public function test_stacks_can_be_created_out_of_order()
    {
        $layoutTemplate = <<<'LAYOUT'
{{ push:example }}
Layout Push 1
{{ /push:example }}
{{ push:example }}
Layout Push 2
{{ /push:example }}
{{ template_content }}
{{ push:example }}
Layout Push 3
{{ /push:example }}
{{ push:example }}
Layout Push 4
{{ /push:example }}
LAYOUT;

        $templateTemplate = <<<'TEMPLATE'
{{ push:example }}
Template Push 1
{{ /push:example  }}
{{ push:example }}
Template Push 2
{{ /push:example  }}
{{ content }}
{{ stack:example }}

TEMPLATE;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', $layoutTemplate);
        $this->viewShouldReturnRaw('home', $templateTemplate);

        $page = $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'content' => 'This is the home page.',
                'template' => 'home',
            ],
        ]);

        $response = $this->get('/home')
            ->assertStatus(200);

        $expected = <<<'EXPECTED'
<p>This is the home page.</p>

Template Push 1Template Push 2Layout Push 1Layout Push 2Layout Push 3Layout Push 4
EXPECTED;

        $this->assertSame($expected, trim($response->getContent()));
    }

    public function test_stack_replacements_are_removed_if_nothing_is_pushed_to_them()
    {
        $layoutTemplate = <<<'LAYOUT'
{{ stack:head }}
{{ template_content }}
{{ stack:footer }}
LAYOUT;

        $templateTemplate = <<<'TEMPLATE'
{{ title }}
TEMPLATE;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', $layoutTemplate);
        $this->viewShouldReturnRaw('home', $templateTemplate);

        $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'content' => 'This is the home page.',
                'template' => 'home',
            ],
        ]);

        $response = $this->get('/home')
            ->assertStatus(200);

        $this->assertSame('Home Page', trim($response->getContent()));
    }

    public function test_whitespace_can_be_preserved_inside_stacks()
    {
        $template = <<<'EOT'
<body class="{{ stack:modifiers }}">

{{ push:modifiers trim="false" }} class-one{{ /push:modifiers }}
{{ push:modifiers trim="false" }} class-two{{ /push:modifiers }}
</body>
EOT;

        $this->assertStringContainsString('<body class=" class-one class-two">', $this->renderString($template, []));
    }

    public function test_stack_items_can_be_retrieved()
    {
        $template = <<<'EOT'
<body class="{{ stack:modifiers }}{{ unless first }} {{ /unless }}{{ value }}{{ /stack:modifiers }}">

{{ push:modifiers }}class-one{{ /push:modifiers }}
{{ push:modifiers }}class-two{{ /push:modifiers }}
EOT;

        $this->assertStringContainsString('<body class="class-one class-two">', $this->renderString($template, []));
    }

    public function test_array_stack_items_can_be_used_in_multiple_places()
    {
        $template = <<<'EOT'
{{ stack:items }}<a:{{ value }}>{{ /stack:items }}

{{ push:items }}item-one{{ /push:items }}
{{ push:items }}item-two{{ /push:items }}

{{ stack:items }}<b:{{ value }}>{{ /stack:items }}
EOT;

        $result = $this->renderString($template);

        $this->assertStringContainsString('<a:item-one><a:item-two>', $result);
        $this->assertStringContainsString('<b:item-one><b:item-two>', $result);
    }

    public function test_array_stacks_when_not_pushed()
    {
        $template = <<<'EOT'
{{ stack:items }}<a:{{ value }}>{{ /stack:items }}


{{ stack:items }}<b:{{ value }}>{{ /stack:items }}
EOT;

        $this->assertSame('', trim($this->renderString($template)));
    }

    public function test_array_stacks_when_only_pushing_to_one()
    {
        $template = <<<'EOT'
{{ stack:items }}<a:{{ value }}>{{ /stack:items }}

{{ push:items }}item-one{{ /push:items }}
{{ push:items }}item-two{{ /push:items }}

{{ stack:items_two }}<b:{{ value }}>{{ /stack:items_two }}
EOT;

        $this->assertSame('<a:item-one><a:item-two>', trim($this->renderString($template)));
    }

    public function test_stack_replacements_are_removed_if_nothing_is_pushed_to_them_on_not_found()
    {
        $layoutTemplate = <<<'LAYOUT'
{{ stack:head }}
{{ template_content }}
{{ stack:footer }}
LAYOUT;

        $templateTemplate = <<<'TEMPLATE'
{{ not_found }}
TEMPLATE;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', $layoutTemplate);
        $this->viewShouldReturnRaw('errors.404', '404');
        $this->viewShouldReturnRaw('home', $templateTemplate);

        $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'template' => 'home',
            ],
        ]);

        $response = $this->get('/home');

        $this->assertSame('404', trim($response->getContent()));
    }
}
