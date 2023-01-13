<?php

namespace Statamic\Taxonomies;

use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\SyncsOriginalState;
use Statamic\Events\TermCreated;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\TermSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Term implements TermContract
{
    use ExistsAsFile, FluentlyGetsAndSets, SyncsOriginalState;

    protected $taxonomy;
    protected $slug;
    protected $blueprint;
    protected $collection;
    protected $data;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;
    protected $syncOriginalProperties = ['slug'];

    public function __construct()
    {
        $this->data = collect();
    }

    public function id()
    {
        return $this->taxonomyHandle().'::'.$this->slug();
    }

    public function slug($slug = null)
    {
        return $this->fluentlyGetOrSet('slug')->setter(function ($slug) {
            return Str::slug($slug);
        })->args(func_get_args());
    }

    public function taxonomy($taxonomy = null)
    {
        return $this
            ->fluentlyGetOrSet('taxonomy')
            ->setter(function ($taxonomy) {
                return $taxonomy instanceof \Statamic\Contracts\Taxonomies\Taxonomy ? $taxonomy->handle() : $taxonomy;
            })
            ->getter(function ($taxonomy) {
                return $taxonomy ? Blink::once("taxonomy-{$taxonomy}", function () use ($taxonomy) {
                    return Taxonomy::findByHandle($taxonomy);
                }) : null;
            })
            ->args(func_get_args());
    }

    public function taxonomyHandle()
    {
        return $this->taxonomy;
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            rtrim(Stache::store('terms')->directory(), '/'),
            $this->taxonomyHandle(),
            $this->slug(),
        ]);
    }

    public function blueprint($blueprint = null)
    {
        $key = "term-{$this->id()}-blueprint";

        return $this
            ->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) use ($key) {
                return Blink::once($key, function () use ($blueprint) {
                    return $this->taxonomy()->termBlueprint($blueprint ?? $this->value('blueprint'), $this);
                });
            })
            ->setter(function ($blueprint) use ($key) {
                Blink::forget($key);

                return $blueprint;
            })
            ->args(func_get_args());
    }

    public function fileData()
    {
        $localizations = clone $this->data;

        $array = Arr::removeNullValues(
            $localizations->pull($this->defaultLocale())->all()
        );

        // todo: add published bool (for each locale?)

        if ($this->blueprint) {
            $array['blueprint'] = $this->blueprint;
        }

        if (! $localizations->isEmpty()) {
            $array['localizations'] = $localizations->map->all()->all();
        }

        return $array;
    }

    public function in($site)
    {
        return app()->makeWith(LocalizedTerm::class, [
            'term' => $this,
            'locale' => $site,
        ]);
    }

    public function inDefaultLocale()
    {
        return $this->in($this->defaultLocale());
    }

    public function defaultLocale()
    {
        return $this->taxonomy()->sites()->first();
    }

    public function localizations()
    {
        return $this->taxonomy()->sites()->mapWithKeys(function ($site) {
            return [$site => $this->in($site)];
        });
    }

    public function collection($collection = null)
    {
        return $this->fluentlyGetOrSet('collection')->args(func_get_args());
    }

    public function entries()
    {
        return $this->queryEntries()->get();
    }

    public function entriesCount()
    {
        $key = vsprintf('term-entries-count-%s-%s', [
            $this->id(),
            optional($this->collection())->handle(),
        ]);

        return Blink::once($key, function () {
            return Facades\Term::entriesCount($this);
        });
    }

    public function queryEntries()
    {
        $entries = $this->collection
            ? $this->collection->queryEntries()
            : Entry::query();

        return $entries->whereTaxonomy($this->id());
    }

    public function title()
    {
        return $this->inDefaultLocale()->title();
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save()
    {
        $isNew = is_null(Facades\Term::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if (TermSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\Term::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                TermCreated::dispatch($this);
            }

            TermSaved::dispatch($this);
        }

        $this->syncOriginal();

        return true;
    }

    public function delete()
    {
        Facades\Term::delete($this);

        TermDeleted::dispatch($this);

        return true;
    }

    public function reference()
    {
        return "term::{$this->id()}";
    }

    public function revisionsEnabled()
    {
        return $this->taxonomy()->revisionsEnabled();
    }

    public function dataForLocale($locale, $data = null)
    {
        if (func_num_args() === 1) {
            return $this->data[$locale] ?? collect();
        }

        $this->data[$locale] = collect($data);

        return $this;
    }

    public function set($key, $value)
    {
        $this->inDefaultLocale()->set($key, $value);

        return $this;
    }

    public function __call($method, $args)
    {
        $default = $this->inDefaultLocale();

        $return = $default->$method(...$args);

        return ($return == $default) ? $this : $return;
    }
}
