<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as Contract;

class Taxonomy implements Contract
{
    use FluentlyGetsAndSets;

    protected $handle;
    protected $route;
    protected $title;
    protected $template;
    protected $layout;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
    }

    public function showUrl()
    {
        return cp_route('taxonomies.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('taxonomies.edit', $this->handle());
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('taxonomies')->directory(), '/'),
            $this->handle
        ]);
    }

    public function termBlueprint()
    {
        return $this->ensureTermBlueprintFields(
            Blueprint::find(config('statamic.theming.blueprints.default'))
        );
    }

    public function ensureTermBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true], 'sidebar');

        return $blueprint;
    }

    public function sortField()
    {
        return 'title'; // todo
    }

    public function sortDirection()
    {
        return 'asc'; // todo
    }

    public function queryTerms()
    {
        return API\Term::query()->where('taxonomy', $this->handle());
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? config('statamic.theming.views.term');
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? config('statamic.theming.views.layout');
            })
            ->args(func_get_args());
    }

    public static function __callStatic($method, $parameters)
    {
        return API\Taxonomy::{$method}(...$parameters);
    }
}
