<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class IncludeNocacheTest extends TestCase
{
    use FakesContent,
        FakesViews,
        PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'file');
        $app['config']->set('statamic.static_caching.strategy', 'half');
        $app['config']->set('statamic.antlers.guardedVariables', ['secret_thing']);
    }

    private function setUpViews(string $layout, array $views)
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', $layout);

        foreach ($views as $name => $contents) {
            $this->viewShouldReturnRaw($name, $contents);
        }

        return $this->createPage('about', ['with' => ['title' => 'Existing']]);
    }

    private function setUpBladeViews(array $views)
    {
        foreach ($views as $name => $contents) {
            $this->viewShouldReturnRaw($name, $contents, 'blade.php');
        }

        view()->addNamespace('compiled__views', storage_path('framework/views'));
    }

    private function renderTwice(string $layout, array $views): array
    {
        $page = $this->setUpViews($layout, $views);

        $first = $this->get('/about')->assertOk()->content();

        $page->set('title', 'Updated')->saveQuietly();

        $second = $this->get('/about')->assertOk()->content();

        return [trim($first), trim($second)];
    }

    #[Test]
    public function a_nocache_region_inside_an_included_view_stays_dynamic()
    {
        $this->assertSame(
            ['W[Existing]', 'W[Updated]'],
            $this->renderTwice('{{ include:w }}', ['w' => 'W{{ nocache }}[{{ title }}]{{ /nocache }}'])
        );
    }

    #[Test]
    public function a_nocache_region_around_an_include_stays_dynamic()
    {
        $this->assertSame(
            ['W[Existing]', 'W[Updated]'],
            $this->renderTwice('{{ nocache }}{{ include:w :t="title" }}{{ /nocache }}', ['w' => 'W[{{ t }}]'])
        );
    }

    #[Test]
    public function a_nocache_region_inside_slot_contents_stays_dynamic()
    {
        $this->assertSame(
            ['W<s>[Existing]</s>', 'W<s>[Updated]</s>'],
            $this->renderTwice(
                '{{ include:w }}{{ nocache }}[{{ title }}]{{ /nocache }}{{ /include:w }}',
                ['w' => 'W<s>{{ slot }}</s>']
            )
        );
    }

    #[Test]
    public function a_slot_inside_a_nocache_region_renders_with_its_captured_scope()
    {
        $this->assertSame(
            ['W<s>[Existing]</s>', 'W<s>[Existing]</s>'],
            $this->renderTwice(
                '{{ include:w }}[{{ title }}]{{ /include:w }}',
                ['w' => 'W{{ nocache }}<s>{{ slot }}</s>{{ /nocache }}']
            )
        );
    }

    #[Test]
    public function a_named_slot_inside_an_antlers_nocache_region_stays_dynamic()
    {
        $page = $this->setUpViews(
            '{{ include:w :title="title" }}{{ slot:row }}{{ if label }}[{{ label | upper }}]{{ /if }}{{ /slot:row }}{{ /include:w }}',
            ['w' => '{{ nocache }}{{ slot:row :label="title" }}{{ /nocache }}']
        );

        $this->assertSame('[EXISTING]', trim($this->get('/about')->assertOk()->content()));

        $page->set('title', 'Updated')->saveQuietly();

        $this->assertSame('[UPDATED]', trim($this->get('/about')->assertOk()->content()));
    }

    #[Test]
    public function a_scoped_slot_inside_a_nocache_region_stays_dynamic()
    {
        $page = $this->setUpViews(
            '{{ include:w :title="title" }}{{ slot:row }}{{ if label }}[{{ label | upper }}]{{ /if }}{{ /slot:row }}{{ /include:w }}',
            []
        );
        $this->setUpBladeViews(['w' => '<s:nocache><s:slot:row :label="$title" /></s:nocache>']);

        $this->assertSame('[EXISTING]', trim($this->get('/about')->assertOk()->content()));

        $page->set('title', 'Updated')->saveQuietly();

        $this->assertSame('[UPDATED]', trim($this->get('/about')->assertOk()->content()));
    }

    #[Test]
    public function escaped_antlers_inside_a_revived_slot_stays_escaped()
    {
        $this->assertSame(
            ['A{{ x }}B', 'A{{ x }}B'],
            $this->renderTwice(
                '{{ include:w }}{{ slot:row }}A@{{ x }}B{{ /slot:row }}{{ /include:w }}',
                ['w' => '{{ nocache }}{{ slot:row }}{{ /nocache }}']
            )
        );
    }

    #[Test]
    public function a_tag_inside_a_revived_slot_can_parse_its_content()
    {
        $this->assertSame(
            ['A[Existing]B', 'A[Existing]B'],
            $this->renderTwice(
                '{{ include:w }}{{ slot:row }}A{{ cache }}[{{ title }}]{{ /cache }}B{{ /slot:row }}{{ /include:w }}',
                ['w' => '{{ nocache }}{{ slot:row }}{{ /nocache }}']
            )
        );
    }

    #[Test]
    public function a_revived_slot_keeps_runtime_variable_guards()
    {
        $page = $this->setUpViews(
            '{{ include:w }}{{ slot:row }}X{{ secret_thing }}Y{{ /slot:row }}{{ /include:w }}',
            []
        );
        $page->set('secret_thing', 'S3CRET')->saveQuietly();
        $this->setUpBladeViews(['w' => '<s:nocache><s:slot:row /></s:nocache>']);

        $this->assertSame('XY', trim($this->get('/about')->assertOk()->content()));

        // Simulate a fresh process, where nothing has resolved the parser binding yet.
        GlobalRuntimeState::$bannedVarPaths = [];
        GlobalRuntimeState::$bannedContentVarPaths = [];

        $this->assertSame('XY', trim($this->get('/about')->assertOk()->content()));
    }

    #[Test]
    public function a_forwarded_slot_inside_a_nocache_region_survives_replay()
    {
        $page = $this->setUpViews(
            '{{ include:middle }}{{ slot:leaf }}[{{ title }}]{{ /slot:leaf }}{{ /include:middle }}',
            []
        );
        $this->setUpBladeViews([
            'middle' => '<s:include:inner><s:slot:row><s:slot:leaf /></s:slot:row></s:include:inner>',
            'inner' => '<s:nocache><s:slot:row /></s:nocache>',
        ]);

        $this->assertSame('[Existing]', trim($this->get('/about')->assertOk()->content()));

        $page->set('title', 'Updated')->saveQuietly();

        $this->assertSame('[Existing]', trim($this->get('/about')->assertOk()->content()));
    }
}
