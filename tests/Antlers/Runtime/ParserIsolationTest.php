<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class ParserIsolationTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesViews;

    private function createBlueprintsAndData()
    {
        Collection::make('news')->routes('{foo}/{slug}')->save();

        EntryFactory::collection('news')->id('1')->slug('news-1')->data([
            'title' => 'News 1',
            'foo' => 'alfa',
        ])->create();
        EntryFactory::collection('news')->id('2')->slug('news-2')->data([
            'title' => 'News 2',
            'foo' => 'bravo',
        ])->create();
        EntryFactory::collection('news')->id('3')->slug('news-3')->data([
            'title' => 'News 3',
        ])->create();
    }

    public function test_context_data_does_not_leak_when_resolving_augmented_Values()
    {
        $this->createBlueprintsAndData();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $template = <<<'EOT'
Entry: <{{ title }}><{{ url }}>
Loop: {{ collection:news }}<{{ title }}><{{ uri }}>{{ /collection:news }}
EOT;

        $this->app['config']->set('statamic.antlers.fatalErrorOnPrintObjects', true);
        $this->viewShouldReturnRaw('default', $template);

        $expected = <<<'EOT'
Entry: <News 2></bravo/news-2>
Loop: <News 1></alfa/news-1><News 2></bravo/news-2><News 3></news-3>
EOT;

        $response = $this
            ->get('bravo/news-2')
            ->assertOk();

        $this->assertSame($expected, StringUtilities::normalizeLineEndings($response->content()));
    }

    public function test_parser_isolation_considers_all_options_after_taxonomy()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->routes(['en' => '{slug}'])->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['title' => 'One', 'tags' => ['rad', 'test', 'test-two']])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Two', 'tags' => ['rad', 'two']])->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => 'Three', 'tags' => ['meh']])->create();
        EntryFactory::collection('blog')->id('4')->create();

        $template = <<<'EOT'
{{ collection:blog paginate="1" as="posts" }}
{{ posts }}
<{{ title }}>
{{ if tags }}{{ tags }}<{{ url }}:{{ title }}>{{ /tags }}{{ /if }}
{{ /posts }}
{{ paginate }}
<total_pages:{{ total_pages }}>
<a href="{{ prev_page }}">Previous</a>
<a href="{{ next_page }}">Next</a>
{{ /paginate }}
{{ /collection:blog }}
EOT;
;

        $expected = <<<'EOT'
<One>
</blog/tags/rad:rad></blog/tags/test:test></blog/tags/test-two:test-two>


<total_pages:4>
<a href="">Previous</a>
<a href="http://localhost?page=2">Next</a>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [])));
    }
}
