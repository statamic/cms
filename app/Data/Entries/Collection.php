<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Blueprint;
use Statamic\Contracts\Data\Entries\Collection as Contract;

class Collection implements Contract
{
    protected $handle;
    protected $route;
    protected $title;
    protected $template;
    protected $layout;
    protected $sites = [];
    protected $data = [];
    protected $blueprints = [];

    public function get($key)
    {
        return array_get($this->data, $key);
    }

    public function has($key)
    {
        return $this->get($key) != null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function route($route = null)
    {
        if (func_num_args() === 0) {
            return $this->route;
        }

        $this->route = $route;

        return $this;
    }

    public function order($order = null)
    {
        if (func_num_args() === 0) {
            return $this->order ?? 'alphabetical';
        }

        if (in_array($order, ['numeric', 'numerical', 'numbers', 'numbered'])) {
            $order = 'number';
        }

        $this->order = $order;

        return $this;
    }

    public function title($title = null)
    {
        if (func_num_args() === 0) {
            return $this->title ?? ucfirst($this->handle());
        }

        $this->title = $title;

        return $this;
    }

    public function editUrl()
    {
        return cp_route('collections.edit', $this->handle());
    }

    public function queryEntries()
    {
        return API\Entry::query()->where('collection', $this->handle());
    }

    public function entryBlueprints($blueprints = null)
    {
        if (func_num_args() === 0) {
            return collect($this->blueprints)->map(function ($blueprint) {
                return Blueprint::find($blueprint);
            });
        }

        $this->blueprints = $blueprints;

        return $this;
    }

    public function entryBlueprint()
    {
        return $this->entryBlueprints()->first();
    }

    public function sites($sites = null)
    {
        if (func_num_args() === 0) {
            return collect($this->sites);
        }

        $this->sites = $sites;

        return $this;
    }

    public function template($template = null)
    {
        if (func_num_args() === 0) {
            return $this->template ?? config('statamic.theming.views.entry');
        }

        $this->template = $template;

        return $this;
    }

    public function layout($layout = null)
    {
        if (func_num_args() === 0) {
            return $this->layout ?? config('statamic.theming.views.layout');
        }

        $this->layout = $layout;

        return $this;
    }

    public function save()
    {
        API\Collection::save($this);

        return $this;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(config('statamic.stache.stores.collections.directory'), '/'),
            $this->handle
        ]);
    }
}
