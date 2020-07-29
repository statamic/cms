<?php

namespace Statamic\Globals;

use Statamic\Contracts\Globals\GlobalSet as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
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

    public function save()
    {
        Facades\GlobalSet::save($this);

        GlobalSetSaved::dispatch($this);

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
