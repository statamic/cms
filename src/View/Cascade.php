<?php

namespace Statamic\View;

use Illuminate\Http\Request;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Facades;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\URL;
use Statamic\Sites\Site;
use Statamic\Support\Arr;

class Cascade
{
    protected $request;
    protected $site;
    protected $data;
    protected $content;

    public function __construct(Request $request, Site $site, array $data = [])
    {
        $this->request = $request;
        $this->site = $site;
        $this->data($data);
    }

    public function instance()
    {
        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function withRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function withSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function withContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function content()
    {
        return $this->content;
    }

    public function get($key)
    {
        return array_get($this->data, $key);
    }

    public function set($key, $value)
    {
        array_set($this->data, $key, $value);
    }

    public function data($data)
    {
        $this->data = $data;
    }

    public function hydrate()
    {
        return $this
            ->hydrateVariables()
            ->hydrateSegments()
            ->hydrateGlobals()
            ->hydrateContent()
            ->hydrateViewModel();
    }

    private function hydrateVariables()
    {
        foreach ($this->contextualVariables() as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    private function hydrateSegments()
    {
        $path = $this->site->relativePath($this->request->url());

        if ($path === '/') {
            return $this;
        }

        foreach (explode('/', $path) as $segment => $value) {
            $this->set("segment_{$segment}", $value);
        }

        $this->set('last_segment', $value);

        return $this;
    }

    private function hydrateGlobals()
    {
        foreach (GlobalSet::all() as $global) {
            if (! $global->existsIn($this->site->handle())) {
                continue;
            }

            $global = $global->in($this->site->handle());

            $this->set($global->handle(), $global->toAugmentedArray());
        }

        $mainGlobal = $this->get('global') ?? [];

        foreach ($mainGlobal as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    private function hydrateContent()
    {
        if (! $this->content) {
            return $this;
        }

        $variables = $this->content instanceof Augmentable
            ? $this->content->toAugmentedArray()
            : $this->content->toArray();

        foreach ($variables as $key => $value) {
            $this->set($key, $value);
        }

        $this->set('page', $variables);

        return $this;
    }

    private function contextualVariables()
    {
        return [
            // Constants
            'environment' => app()->environment(),
            'xml_header' => '<?xml version="1.0" encoding="utf-8" ?>', // @TODO remove and document new best practice
            'csrf_token' => csrf_token(),
            'csrf_field' => csrf_field(),
            'config' => config()->all(),
            'response_code' => 200,

            // Auth
            'logged_in' => $loggedIn = auth()->check(),
            'logged_out' => ! $loggedIn,

            // Date
            'current_date' => $now = now(),
            'now' => $now,
            'today' => $now,

            // Request
            'current_url' => $this->request->url(),
            'current_full_url' => $this->request->fullUrl(),
            'current_uri' => URL::format($this->request->path()),
            'get_post' => Arr::sanitize($this->request->all()),
            'get' => Arr::sanitize($this->request->query->all()),
            'post' => $this->request->isMethod('post') ? Arr::sanitize($this->request->request->all()) : [],
            'old' => Arr::sanitize(old(null, [])),

            'site' => $this->site,
            'sites' => Facades\Site::all()->values(),
            'homepage' => $this->site->url(),
            'cp_url' => cp_route('index'),
        ];
    }

    protected function hydrateViewModel()
    {
        if ($class = $this->get('view_model')) {
            $viewModel = new $class($this);
            $this->data = array_merge($this->data, $viewModel->data());
        }

        return $this;
    }

    public function getViewData($view)
    {
        $all = $this->get('views') ?? [];

        return collect($all)
            ->reverse()
            ->reduce(function ($carry, $data) {
                return $carry->merge($data);
            }, collect())
            ->merge($all[$view])
            ->all();
    }
}
