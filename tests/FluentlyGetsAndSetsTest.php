<?php

namespace Tests;

use Statamic\Data\ContainsData;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class FluentlyGetsAndSetsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->entry = new Entry;
    }

    /** @test */
    public function it_can_get_and_set_a_protected_property()
    {
        $this->assertNull($this->entry->blueprint());

        $this->entry->blueprint('post');

        $this->assertEquals('post', $this->entry->blueprint());
    }

    /** @test */
    public function it_can_get_and_set_back_to_null()
    {
        $this->assertEquals('Jesse', $this->entry->publishedBy());

        $this->entry->publishedBy(null);

        $this->assertNull($this->entry->publishedBy());
    }

    /** @test */
    public function it_can_get_and_set_with_custom_get_and_set_logic()
    {
        $this->assertNull($this->entry->title());

        $this->entry->title('lol cat');

        $this->assertEquals('Lol Cats', $this->entry->title());
    }

    /** @test */
    public function it_can_get_and_set_into_the_data_property_through_magic_methods_in_parent()
    {
        $entry = new EntryContainingData;
        $this->assertNull($entry->get('template'));
        $this->assertEquals('default', $entry->template());

        $entry->template('foo');

        $this->assertEquals('foo', $entry->get('template'));
        $this->assertEquals('foo', $entry->template());
    }

    /** @test */
    public function it_can_run_custom_after_setter_logic()
    {
        $this->assertNull($this->entry->route());

        $this->entry->route('login');

        $this->assertEquals('login', $this->entry->route());
        $this->assertEquals('login', $this->entry->url);
    }

    /** @test */
    public function it_can_set_fluently()
    {
        $this->entry
            ->title('lol cat')
            ->blueprint('post')
            ->publishedBy('Hoff');

        $this->assertEquals('Lol Cats', $this->entry->title());
        $this->assertEquals('post', $this->entry->blueprint());
        $this->assertEquals('Hoff', $this->entry->publishedBy());
    }
}

class Entry
{
    use FluentlyGetsAndSets;

    protected $blueprint;

    protected $publishedBy = 'Jesse';

    public function blueprint($blueprint = null)
    {
        return $this->fluentlyGetOrSet('blueprint')->value($blueprint);
    }

    public function publishedBy($name = null)
    {
        return $this->fluentlyGetOrSet('publishedBy')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return Str::title($title) ?: null;
            })
            ->setter(function ($title) {
                return Str::plural($title);
            })
            ->value($title);
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')
            ->afterSetter(function ($route) {
                $this->url = $route;
            })
            ->value($route);
    }
}

class EntryContainingData extends Entry
{
    use ContainsData;

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? 'default';
            })
            ->args(func_get_args());
    }
}
