<?php

namespace Statamic\Data\Globals;

use Statamic\API;
use Statamic\API\Site;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\Localizable;
use Statamic\Data\ExistsAsFile;
use Statamic\Contracts\Data\Globals\GlobalSet as Contract;

class GlobalSet implements Contract
{
    use Localizable, ExistsAsFile;

    protected $id;
    protected $title;
    protected $handle;
    protected $sites;
    protected $blueprint;

    public function id($id = null)
    {
        if (func_num_args() === 0) {
            return $this->id;
        }

        $this->id = $id;

        $this->localizations()->each->id($id);

        return $this;
    }

    public function sites($sites = null)
    {
        if (func_num_args() === 0) {
            return collect(
                Site::hasMultiple() ? $this->sites : [Site::default()->handle()]
            );
        }

        $this->sites = $sites;

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

    public function title($title = null)
    {
        if (func_num_args() === 0) {
            return $this->title ?? ucfirst($this->handle);
        }

        $this->title = $title;

        return $this;
    }

    public function blueprint($blueprint = null)
    {
        if (func_num_args() === 0) {
            return Blueprint::find($this->blueprint) ?? $this->fallbackBlueprint();
        }

        $this->blueprint = $blueprint;

        return $this;
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
        return new LocalizedGlobalSet;
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
