<?php

namespace Tests\Antlers\Components;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Str;
use Statamic\Facades\Collection;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class ComponentsCascadeTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function createEntry()
    {
        Collection::make('blog')->routes(['en' => '{slug}'])->save();
        EntryFactory::collection('blog')->id('1')->slug('one')->data(['title' => 'One'])->create();
    }

    public function test_cascade_does_not_leak_into_components()
    {
        $this->createEntry();

        $template = <<<'ANTLERS'
Outer: {{ title }}
Component: <x-scope.cascade />
ANTLERS;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('components.scope.cascade', '{{ title }}');
        $this->viewShouldReturnRaw('default', $template);

        $response = $this->get('one')->assertOk();

        $this->assertSame(
            'Outer: One Component:',
            Str::squish($response->getContent())
        );
    }

    public function test_data_can_be_inherited_using_the_cascade_directive()
    {
        $this->createEntry();

        $template = <<<'ANTLERS'
Outer: {{ title }}
Component: <x-scope.cascade />
ANTLERS;

        $component = <<<'ANTLERS'
@cascade (['title'])

{{ title }}
ANTLERS;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('components.scope.cascade', $component);
        $this->viewShouldReturnRaw('default', $template);

        $response = $this->get('one')->assertOk();

        $this->assertSame(
            'Outer: One Component: One',
            Str::squish($response->getContent())
        );
    }

    public function test_multiple_keys_can_be_inherited_using_the_cascade_directive()
    {
        $this->createEntry();

        $template = <<<'ANTLERS'
Outer: {{ title }}
Component: <x-scope.cascade />
ANTLERS;

        $component = <<<'ANTLERS'
@cascade (['title'])

{{ title }} - {{ id }}
ANTLERS;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('components.scope.cascade', $component);
        $this->viewShouldReturnRaw('default', $template);

        $this->assertSame(
            'Outer: One Component: One -',
            Str::squish($this->get('one')->assertOk()->getContent())
        );

        $component = <<<'ANTLERS'
@cascade (['title', 'id'])

{{ title }} - {{ id }}
ANTLERS;

        $this->viewShouldReturnRaw('components.scope.cascade', $component);
        $this->assertSame(
            'Outer: One Component: One - 1',
            Str::squish($this->get('one')->assertOk()->getContent())
        );
    }

    public function test_all_keys_can_be_injected_using_the_cascade_directive()
    {
        $this->createEntry();

        $template = <<<'ANTLERS'
Outer: {{ title }}
Component: <x-scope.cascade />
ANTLERS;

        $component = <<<'ANTLERS'
@cascade

{{ title }} - {{ id }}
ANTLERS;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('components.scope.cascade', $component);
        $this->viewShouldReturnRaw('default', $template);

        $this->assertSame(
            'Outer: One Component: One - 1',
            Str::squish($this->get('one')->assertOk()->getContent())
        );
    }
}
