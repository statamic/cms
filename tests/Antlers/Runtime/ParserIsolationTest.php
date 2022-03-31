<?php

namespace Tests\Antlers\Runtime;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ParserIsolationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesViews;

    private function createBlueprintsAndData()
    {
        Taxonomy::make('topics')->save();
        Term::make()->taxonomy('topics')->inDefaultLocale()->slug('dance')->data([])->save();

        $news = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
            'content' => ['type' => 'text'],
            'topic' => [
                'type' => 'terms',
                'taxonomies' => ['topics'],
                'max_items' => 1,
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/news')->andReturn(collect([
            'news' => $news->setHandle('news'),
        ]));

        Collection::make('news')->routes(['en' => '{topic}/{slug}'])->save();

        EntryFactory::collection('news')->id('1')
                ->slug('news-1')->data([
                    'title' => 'News 1',
                    'content' => 'News 1 Content',
                ])->create();
        EntryFactory::collection('news')->id('2')
                ->slug('news-2')->data([
                    'title' => 'News 2',
                    'content' => 'News 2 Content',
                    'topic' => 'dance',
                ])->create();
    }

    public function test_context_data_does_not_leak_when_resolving_augmented_Values()
    {
        $this->createBlueprintsAndData();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $template = <<<'EOT'
{{ collection:news }}{{ /collection:news }}
<{{ title }}><{{ content }}>
EOT;

        // Log::shouldReceive('debug')->times(0)->with('Cannot render an object variable as a string: {{ topic }}');
        $this->viewShouldReturnRaw('default', $template);

        $responseOne = $this->get('dance/news-2')->assertOk();

        $responseTwo = $this->get('news-1')->assertOk();

        $this->assertSame('<News 2><News 2 Content>', trim($responseOne->content()));
        $this->assertSame('<News 1><News 1 Content>', trim($responseTwo->content()));
    }
}
