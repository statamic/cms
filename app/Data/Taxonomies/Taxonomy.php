<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as Contract;

class Taxonomy implements Contract
{
    use FluentlyGetsAndSets, ExistsAsFile;

    protected $handle;
    protected $route;
    protected $title;
    protected $template;
    protected $layout;
    protected $termBlueprint;

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

    public function termBlueprint($blueprint = null)
    {
        return $this
            ->fluentlyGetOrSet('termBlueprint')
            ->getter(function ($blueprint) {
                return $this->ensureTermBlueprintFields(
                    $blueprint ? Blueprint::find($blueprint) : $this->fallbackTermBlueprint()
                );
            })
            ->args(func_get_args());
    }

    public function ensureTermBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true], 'sidebar');

        return $blueprint;
    }

    public function fallbackTermBlueprint()
    {
        return Blueprint::find(config('statamic.theming.blueprints.default'));
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

    public function save()
    {
        API\Taxonomy::save($this);

        return true;
    }

    public function fileData()
    {
        return Arr::except($this->toArray(), [
            'handle',
        ]);
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'route' => $this->route,
            'template' => $this->template,
            'layout' => $this->layout,
            'term_blueprint' => $this->termBlueprint,
        ];
    }

    public static function __callStatic($method, $parameters)
    {
        return API\Taxonomy::{$method}(...$parameters);
    }
}
