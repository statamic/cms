<?php

namespace Statamic\Globals;

use Statamic\Contracts\Globals\GlobalSet as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\GlobalSetCreated;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalSetSaving;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class GlobalSet implements Contract
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $title;
    protected $handle;
    protected $localizations;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

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

    public function blueprint()
    {
        return Blueprint::find('globals.'.$this->handle());
    }

    public function path()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            $this->handle(),
            'yaml',
        ]);
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
        $isNew = is_null(Facades\GlobalSet::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if (GlobalSetSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\GlobalSet::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                GlobalSetCreated::dispatch($this);
            }

            GlobalSetSaved::dispatch($this);
        }

        return $this;
    }

    public function delete()
    {
        Facades\GlobalSet::delete($this);

        GlobalSetDeleted::dispatch($this);

        return true;
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title(),
        ];

        if (! Site::hasMultiple()) {
            $data['data'] = Arr::removeNullValues(
                $this->in(Site::default()->handle())->data()->all()
            );
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

    public function inCurrentSite()
    {
        return $this->in(Site::current()->handle());
    }

    public function inDefaultSite()
    {
        return $this->in(Site::default()->handle());
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
