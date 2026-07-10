<?php

namespace Tests\Antlers\Parser;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Tags\Tags;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class ComponentTagsTest extends ParserTestCase
{
    use FakesViews,
        PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('pages')
            ->routes(['en' => '{slug}'])
            ->save();

        for ($i = 1; $i <= 5; $i++) {
            EntryFactory::collection('pages')
                ->id("page-$i")
                ->data([
                    'title' => "Page: $i",
                ])
                ->slug('page-'.$i)
                ->create();
        }
    }

    public function test_component_tags_are_parsed()
    {
        $template = <<<'ANTLERS'
<s:collection:pages>
    {{ title }}.
</s:collection:pages>
ANTLERS;

        $this->assertSame(
            'Page: 1. Page: 2. Page: 3. Page: 4. Page: 5.',
            (string) str($this->renderString($template))->squish(),
        );
    }

    public function test_component_tags_with_dynamic_parameters_are_parsed()
    {
        $template = <<<'ANTLERS'
<s:collection :from="collection_name">{{ title }}.</s:collection>
ANTLERS;

        $result = $this->renderString($template, [
            'collection_name' => 'pages',
        ]);

        $this->assertSame(
            'Page: 1.Page: 2.Page: 3.Page: 4.Page: 5.',
            $result,
        );
    }

    public function test_component_tags_with_parameters_are_parsed()
    {
        $template = <<<'ANTLERS'
<s:collection:pages limit="1">{{ title }}</s:collection:pages>
ANTLERS;

        $this->assertSame(
            'Page: 1',
            $this->renderString($template),
        );
    }

    public function test_self_closing_component_tags_are_parsed()
    {
        (new class extends Tags
        {
            protected static $handle = 'the_tag';

            public function index()
            {
                if ($this->isPair) {
                    return 'Paired!';
                }

                return 'Self-Closing!';
            }
        })::register();

        $this->assertSame('Self-Closing!', $this->renderString('<s:the_tag />'));
        $this->assertSame('Paired!', $this->renderString('<s:the_tag> </s:the_tag>'));
    }

    public function test_nested_component_tags_are_parsed()
    {
        $template = <<<'ANTLERS'
<s:collection:pages sort="title:asc">
    Before: {{ title }}

    <nested><s:collection:pages sort="title:desc" limit="2">{{ title }}</s:collection:pages></nested>

    After: {{ title }}
</s:collection:pages>
ANTLERS;

        $expected = <<<'RESULT'
Before: Page: 1 <nested>Page: 5Page: 4</nested> After: Page: 1 Before: Page: 2 <nested>Page: 5Page: 4</nested> After: Page: 2 Before: Page: 3 <nested>Page: 5Page: 4</nested> After: Page: 3 Before: Page: 4 <nested>Page: 5Page: 4</nested> After: Page: 4 Before: Page: 5 <nested>Page: 5Page: 4</nested> After: Page: 5
RESULT;

        $this->assertSame(
            $expected,
            (string) str($this->renderString($template))->squish(),
        );
    }

    public function test_component_syntax_works_with_render_text_calls()
    {
        $this->withFakeViews();
        $template = <<<'ANTLERS'
Page Title: {{ title }}
<s:collection:pages>{{ title }}.</s:collection:pages>
ANTLERS;

        $this->viewShouldReturnRaw('default', $template);
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $this->get('/page-1')
            ->assertOk()
            ->assertSee('Page Title: Page: 1')
            ->assertSee('Page: 1.Page: 2.Page: 3.Page: 4.Page: 5.');
    }
}
