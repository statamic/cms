<?php

namespace Tests\Antlers\Runtime;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\Tags\Tags;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TagCheckScopeTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    private function createData()
    {
        $blueprint = new Blueprint();
        $blueprint->setContents([
            'fields' => [
                [
                    'handle' => 'title',
                    'field' => [
                        'type' => 'text',
                    ],
                ],
                [
                    'handle' => 'replicator_field',
                    'field' => [
                        'sets' => [
                            'new_set' => [
                                'display' => 'New Set',
                                'fields' => [
                                    [
                                        'handle' => 'bard_field',
                                        'display' => 'Bard Field',
                                        'field' => [
                                            'antlers' => false,
                                            'type' => 'bard',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'type' => 'replicator',
                    ],
                ],
            ],
        ]);
        $blueprint->save();

        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect([
            'pages' => $blueprint->setHandle('pages'),
        ]));

        Collection::make('pages')->routes(['en' => '{slug}'])->save();

        EntryFactory::collection('pages')->id('1')
            ->slug('about')->data([
                'title' => 'About Page',
            ])->create();

        EntryFactory::collection('pages')->id('2')
            ->slug('home')->data([
                'title' => 'Home Page',
                'blueprint' => 'pages',
                'replicator_field' => [
                    [
                        'type' => 'new_set',
                        'enabled' => true,
                        'bard_field' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'I am some text',
                                    ],
                                    [
                                        'type' => 'text',
                                        'marks' => [
                                            [
                                                'type' => 'link',
                                                'attrs' => [
                                                    'href' => 'statamic://entry::1',
                                                    'rel' => null,
                                                    'target' => null,
                                                    'title' => null,
                                                ],
                                            ],
                                        ],
                                        'text' => 'I am the link',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])->create();
    }

    public function test_node_processor_does_not_trash_scope_when_checking_if_something_should_be_a_tag()
    {
        $this->createData();
        $this->withFakeViews();

        $this->viewShouldReturnRaw('wrap', '{{ slot }}');
        $template = <<<'EOT'
<{{ title }}>

{{ replicator_field }}

{{ partial src="wrap" }}
{{ bard_field }}
{{ /partial }}

{{ /replicator_field}}
EOT;

        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', $template);

        $responseOne = $this->get('/home')->assertOk();
        $content = $responseOne->content();

        $this->assertStringContainsString('<Home Page>', $content);
        $this->assertStringContainsString('<p>I am some text<a href="/about">I am the link</a></p>', $content);
    }

    public function test_condition_augmentation_doesnt_reset_up_the_scope()
    {
        (new class extends Tags
        {
            public static $handle = 'just_a_tag';

            public function index()
            {
                return [];
            }
        })::register();

        $this->createData();
        $this->withFakeViews();

        $template = <<<'EOT'

{{ just_a_tag }}
    {{ replicator_field }}
        {{ partial:inner }}
    {{ /replicator_field }}
{{ /just_a_tag }}
EOT;
        $partial = <<<'PARTIAL'
{{ stuff }}

{{ if bard_field }} {{ bard_field }} {{ /if }}
PARTIAL;

        $this->viewShouldReturnRaw('inner', $partial);
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', $template);

        $responseOne = $this->get('/home')->assertOk();
        $content = trim($responseOne->content());

        $this->assertSame('<p>I am some text<a href="/about">I am the link</a></p>', $content);
    }
}
