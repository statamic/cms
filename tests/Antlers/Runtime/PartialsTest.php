<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class PartialsTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_nested_partials_render_correctly()
    {
        $template = <<<'EOT'
{{ partial src="wrapper" }}
    {{ partial src="second_wrapper" }}
        {{ partial src="content" /}}
    {{ /partial }}
{{ /partial }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('wrapper', 'outer {{ slot }}');
        $this->viewShouldReturnRaw('second_wrapper', 'inner');
        $this->viewShouldReturnRaw('content', 'My content');

        // Before the fix, "My content" would be rendered at the end of the string.
        $this->assertSame('outer inner', $this->renderString($template));
    }

    public function test_sections_work_inside_the_main_slot_content()
    {
        Collection::make('pages')->routes('{slug}')->save();

        EntryFactory::collection('pages')->id('1')->data(['title' => 'The Title', 'content' => 'The content'])->slug('test')->create();

        $layout = <<<'LAYOUT'
{{ yield:test }}
---
{{ template_content }}
LAYOUT;
        $default = <<<'DEFAULT'
{{ partial:test }}
    {{ section:test }}
        {{ content | upper }}
    {{ /section:test }}
{{ /partial:test }}
DEFAULT;
        $partial = <<<'PARTIAL'
I'm the partial.
PARTIAL;
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', $layout);
        $this->viewShouldReturnRaw('default', $default);
        $this->viewShouldReturnRaw('test', $partial);

        $response = $this->get('test')->assertOk();
        $content = trim(StringUtilities::normalizeLineEndings($response->content()));

        $expected = <<<'EXPECTED'
<P>THE CONTENT</P>

    
---
I'm the partial.
EXPECTED;

        $this->assertSame($expected, $content);
    }

    public function test_double_colons_may_be_used_in_tag_method_part()
    {
        $this->expectExceptionMessage('No hint path defined for [some].');
        $template = <<<'ANTLERS'
{{ partial:some::template/path /}}
ANTLERS;

        $this->renderString($template, [], true);
    }

    public function test_assignments_in_earlier_partials_do_not_blank_a_later_partials_slot()
    {
        $template = <<<'EOT'
{{? $foo = 'bar' ?}}{{ partial:filter /}}{{ partial:card }}KEEPME{{ /partial:card }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('filter', "{{? \$values = 'v' ?}}FILTER");
        $this->viewShouldReturnRaw('card', "{{ trans key='hi' }}<slot>{{ slot }}</slot>");

        $this->assertSame('FILTERhi<slot>KEEPME</slot>', $this->renderString($template, [], true));
    }

    public function test_named_slots_render_inside_a_nested_partial_that_uses_the_default_slot()
    {
        $template = <<<'EOT'
{{ partial:accordion index="1" }}
    {{ slot:title }}<h2>The Title</h2>{{ /slot:title }}
    {{ slot:icon }}<button>+</button>{{ /slot:icon }}
    {{ slot:body }}<p>The Body</p>{{ /slot:body }}
{{ /partial:accordion }}
EOT;

        $accordion = <<<'EOT'
<div id="accordion-{{ index }}">{{ partial:flex_column class="p-6" }}<div class="header">{{ slot:title }}{{ slot:icon }}</div><div class="content">{{ slot:body }}</div>{{ /partial:flex_column }}</div>
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('accordion', $accordion);
        $this->viewShouldReturnRaw('flex_column', '<div class="flex flex-col {{ class }}">{{ slot }}</div>');

        $expected = '<div id="accordion-1"><div class="flex flex-col p-6"><div class="header"><h2>The Title</h2><button>+</button></div><div class="content"><p>The Body</p></div></div></div>';

        $this->assertSame($expected, $this->renderString($template, [], true));
    }
}
