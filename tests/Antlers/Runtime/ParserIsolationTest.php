<?php

namespace Tests\Antlers\Runtime;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ParserIsolationTest extends TestCase
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
}
