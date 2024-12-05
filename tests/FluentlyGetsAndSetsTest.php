<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class FluentlyGetsAndSetsTest extends TestCase
{
    private $entry;

    public function setUp(): void
    {
        parent::setUp();

        $this->entry = new Entry;
    }

    #[Test]
    public function it_can_get_and_set_a_protected_property()
    {
        $this->assertNull($this->entry->blueprint());

        $this->entry->blueprint('post');

        $this->assertEquals('post', $this->entry->blueprint());
    }

    #[Test]
    public function it_can_get_and_set_back_to_null()
    {
        $this->assertEquals('Jesse', $this->entry->publishedBy());

        $this->entry->publishedBy(null);

        $this->assertNull($this->entry->publishedBy());
    }

    #[Test]
    public function it_can_get_and_set_with_custom_get_and_set_logic()
    {
        $this->assertNull($this->entry->title());

        $this->entry->title('lol cat');

        $this->assertEquals('Lol Cats', $this->entry->title());
    }

    #[Test]
    public function it_can_get_and_set_via_magic_getter()
    {
        // A class that has __get and __set methods.
        // We'll use them to store the values in a "data" array property.
        $entry = new EntryWithMagicGetter;

        $this->assertEquals([], $entry->data);
        $this->assertEquals('default', $entry->template());

        $entry->template('foo');

        $this->assertEquals(['template' => 'foo'], $entry->data);
        $this->assertEquals('foo', $entry->template());
    }

    #[Test]
    public function it_can_run_custom_after_setter_logic()
    {
        $this->assertNull($this->entry->route());

        $this->entry->route('login');

        $this->assertEquals('login', $this->entry->route());
        $this->assertEquals('login', $this->entry->url);
    }

    #[Test]
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

    public $title;
    public $route;
    public $url;
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
                return $title ? Str::title($title) : null;
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

class EntryWithMagicGetter extends Entry
{
    public $data = [];

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? 'default';
            })
            ->args(func_get_args());
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
