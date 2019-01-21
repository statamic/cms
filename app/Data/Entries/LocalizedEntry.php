<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Events\Data\EntrySaved;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Content\UrlBuilder;
use Statamic\Contracts\Data\Entries\LocalizedEntry as Contract;

class LocalizedEntry implements Contract, Arrayable, Responsable
{
    protected $id;
    protected $locale;
    protected $slug;
    protected $order;
    protected $published = true;
    protected $initialPath;
    protected $data = [];
    protected $supplements = [];
    protected $entry;
    protected $shouldPropagate = true;

    public function locale($locale = null)
    {
        if (is_null($locale)) {
            return $this->locale;
        }

        $this->locale = $locale;

        return $this;
    }

    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return $this->slug;
        }

        $this->slug = $slug;

        return $this;
    }

    public function get($key, $fallback = null)
    {
        return $this->data[$key] ?? $fallback;
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
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function merge($data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function entry($entry = null)
    {
        if (is_null($entry)) {
            return $this->entry;
        }

        $this->entry = $entry;

        return $this;
    }

    public function collection()
    {
        return $this->entry()->collection();
    }

    public function collectionHandle()
    {
        return $this->collection()->handle();
    }

    public function uri()
    {
        if (! $route = $this->collection()->route()) {
            return null;
        }

        return app(UrlBuilder::class)->content($this)->build($route);
    }

    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    public function absoluteUrl()
    {
        return vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($this->uri(), '/')
        ]);
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    public function supplements()
    {
        return $this->supplements;
    }

    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;

        return $this;
    }

    public function getSupplement($key)
    {
        return $this->supplements[$key];
    }

    public function toArray()
    {
        return array_merge($this->data, [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'edit_url' => $this->editUrl(),
            'published' => $this->published(),
        ], $this->supplements);
    }

    public function toAugmentedArray()
    {
        return array_merge($d = $this->toArray(), [
            'content' => markdown($d['content']),
            'content_raw' => $d['content']
        ]);
    }

    public function editUrl()
    {
        return cp_route('collections.entries.edit', [
            $this->collectionHandle(),
            $this->id(),
            $this->slug(),
            $this->locale(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('collections.entries.update', [
            $this->collectionHandle(),
            $this->id(),
            $this->slug(),
            $this->locale(),
        ]);
    }

    public function blueprint()
    {
        if ($blueprint = $this->get('blueprint')) {
            return Blueprint::find($blueprint);
        }

        return $this->collection()->entryBlueprint();
    }

    public function save()
    {
        API\Entry::save($this);

        $this->entry()->addLocalization($this);

        if ($this->shouldPropagate) {
            $this->propagate();
        }

        EntrySaved::dispatch($this, []);  // TODO: Fix test

        return $this;
    }

    public function saveWithoutPropagating()
    {
        $state = $this->shouldPropagate;

        $this->shouldPropagate = false;

        $return = $this->save();

        $this->shouldPropagate = $state;

        return $return;
    }

    public function initialPath($path = null)
    {
        if (func_num_args() === 0) {
            return $this->initialPath;
        }

        $this->initialPath = $path;

        return $this;
    }

    public function path()
    {
        $prefix = '';

        if ($order = $this->order()) {
            $prefix = $order . '.';
        }

        return vsprintf('%s/%s/%s%s%s.%s', [
            rtrim(Stache::store('entries')->directory(), '/'),
            $this->collectionHandle(),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $prefix,
            $this->slug(),
            'md'
        ]);
    }

    public function fileContents()
    {
        // This method should be clever about what contents to output depending on the
        // file type used. Right now it's assuming markdown. Maybe you'll want to
        // save JSON, etc. TODO: Make it smarter when the time is right.

        $data = array_merge($this->data(), [
            'id' => $this->id()
        ]);

        $content = array_pull($data, 'content');

        return YAML::dump($data, $content);
    }

    public function order($order = null)
    {
        if (func_num_args() === 0) {
            return $this->order;
        }

        $this->order = $order;

        return $this;
    }

    public function published($published = null)
    {
        if (func_num_args() === 0) {
            return $this->published;
        }

        $this->published = $published;

        return $this;
    }

    public function publish()
    {
        $this->published = true;

        return $this;
    }

    public function unpublish()
    {
        $this->published = false;

        return $this;
    }

    public function propagate()
    {
        collect($this->collection()->sites())
            ->diff($this->locale())
            ->each(function ($site) {
                $this->entry()
                    ->inOrClone($site)
                    ->merge($this->unlocalizableData())
                    ->saveWithoutPropagating();
            });

        return $this;
    }

    protected function unlocalizableData()
    {
        $data = $this->blueprint()->fields()
            ->addValues($this->data)
            ->unlocalizable()
            ->values();

        return array_except($data, ['slug', 'order', 'published']);
    }

    public function supplementTaxonomies()
    {
        // Added this method because a bunch of things call it.
        // Rather than update those things right now, just add this so things continue to hum along.
        // TODO: Get rid of this during taxonomy refactor.
    }

    public function template($template = null)
    {
        if (func_num_args() === 0) {
            return $this->template ?? $this->collection()->template();
        }

        $this->template = $template;

        return $this;
    }

    public function layout($layout = null)
    {
        if (func_num_args() === 0) {
            return $this->layout ?? $this->collection()->layout();
        }

        $this->layout = $layout;

        return $this;
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }
}
