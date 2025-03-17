<?php

namespace Statamic\Globals;

use Statamic\Contracts\Globals\GlobalSet as Contract;
use Statamic\Contracts\Globals\Variables;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\GlobalSetCreated;
use Statamic\Events\GlobalSetCreating;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetDeleting;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalSetSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalVariables;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class GlobalSet implements Contract
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $title;
    protected $handle;
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
            if ($isNew && GlobalSetCreating::dispatch($this) === false) {
                return false;
            }

            if (GlobalSetSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\GlobalSet::save($this);

        $this->saveOrDeleteLocalizations();

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

    protected function saveOrDeleteLocalizations()
    {
        $localizations = $this->localizations();

        $localizations->each->save();

        $this->freshLocalizations()
            ->diffKeys($localizations)
            ->each->delete();
    }

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && GlobalSetDeleting::dispatch($this) === false) {
            return false;
        }

        $this->localizations()->each->delete();

        Facades\GlobalSet::delete($this);

        if ($withEvents) {
            GlobalSetDeleted::dispatch($this);
        }

        return true;
    }

    public function fileData()
    {
        return [
            'title' => $this->title(),
        ];
    }

    public function makeLocalization($site)
    {
        return app(Variables::class)
            ->globalSet($this)
            ->locale($site);
    }

    public function addLocalization($localization)
    {
        $localization->globalSet($this);

        $this->localizations()[$localization->locale()] = $localization;

        return $this;
    }

    public function removeLocalization($localization)
    {
        $this->localizations()->forget($localization->locale());

        return $this;
    }

    public function sites()
    {
        return $this->localizations()->map->locale()->values()->toBase();
    }

    public function in($locale)
    {
        return $this->localizations()->get($locale);
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
        return Blink::once('global-set-localizations-'.$this->id(), function () {
            return $this->freshLocalizations();
        });
    }

    private function freshLocalizations()
    {
        return GlobalVariables::whereSet($this->handle())->keyBy->locale();
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
