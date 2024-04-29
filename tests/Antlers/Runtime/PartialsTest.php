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
}
