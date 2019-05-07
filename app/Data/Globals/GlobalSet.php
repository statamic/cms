<?php

namespace Statamic\Data\Globals;

use Statamic\API;
use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\Localizable;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Globals\GlobalSet as Contract;

class GlobalSet implements Contract
{
    use Localizable, ExistsAsFile, FluentlyGetsAndSets;

    protected $id;
    protected $title;
    protected $handle;
    protected $sites;
    protected $blueprint;

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->afterSetter(function ($id) {
                $this->localizations()->each->id($id);
            })
            ->args(func_get_args());
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect(Site::hasMultiple() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
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
                return Blueprint::find($blueprint) ?? $this->fallbackBlueprint();
            })
            ->args(func_get_args());
    }

    protected function fallbackBlueprint()
    {
        $fields  = collect($this->data())
            ->except(['id', 'title', 'blueprint'])
            ->map(function ($field, $handle) {
                return [
                    'handle' => $handle,
                    'field' => ['type' => 'text'],
                ];
            });

        return (new \Statamic\Fields\Blueprint)->setContents([
            'sections' => [
                'main' => [
                    'fields' => $fields->all()
                ]
            ]
        ]);
    }

    public function path()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            $this->handle(),
            'yaml'
        ]);
    }

    protected function makeLocalization()
    {
        return new Variables;
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
        API\GlobalSet::save($this);

        return $this;
    }

    protected function fileData()
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title(),
            'blueprint' => $this->blueprint,
        ];

        if (Site::hasMultiple()) {
            $data['sites'] = $this->sites()->all();
        } else {
            $data['data'] = $this->data();
        }

        return $data;
    }
}
