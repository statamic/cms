<?php

namespace Statamic\Globals;

use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Blueprint;
use Statamic\Data\ExistsAsFile;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Contracts\Globals\GlobalSet as Contract;

class GlobalSet implements Contract
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $title;
    protected $handle;
    protected $blueprint;
    protected $localizations;

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
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

    public function blueprint($blueprint = null)
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return Blueprint::find($blueprint);
            })
            ->args(func_get_args());
    }

    public function path()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            $this->handle(),
            'yaml'
        ]);
    }

    public function toCacheableArray()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'blueprint' => $this->blueprint,
            'sites' => $this->sites()->all(),
            'path' => $this->path(),
            'localizations' => $this->localizations()->map(function ($localized) {
                return [
                    'path' => $localized->initialPath() ?? $localized->path(),
                    'data' => $localized->data()
                ];
            })->all()
        ];
    }

    public function save()
    {
        Facades\GlobalSet::save($this);

        return $this;
    }

    public function delete()
    {
        Facades\GlobalSet::delete($this);

        return true;
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title(),
            'blueprint' => $this->blueprint,
        ];

        if (! Site::hasMultiple()) {
            $data['data'] = $this->in(Site::default()->handle())->data()->all();
        }

        return $data;
    }

    public function makeLocalization($site)
    {
        return (new Variables)
            ->globalSet($this)
            ->locale($site);
    }

    public function addLocalization($localization)
    {
        $localization->globalSet($this);

        $this->localizations[$localization->locale()] = $localization;

        return $this;
    }

    public function removeLocalization($localization)
    {
        unset($this->localizations[$localization->locale()]);

        return $this;
    }

    public function in($locale)
    {
        return $this->localizations[$locale] ?? null;
    }

    public function inSelectedSite()
    {
        return $this->in(Site::selected()->handle());
    }

    public function existsIn($locale)
    {
        return $this->in($locale) !== null;
    }

    public function localizations()
    {
        return collect($this->localizations);
    }

    public function editUrl()
    {
        return cp_route('globals.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('globals.destroy', $this->handle());
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\GlobalSet::{$method}(...$parameters);
    }
}
