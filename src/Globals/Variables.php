<?php

namespace Statamic\Globals;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Globals\Variables as Contract;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Data\HasOrigin;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Variables implements Contract, Localization, Augmentable
{
    use ExistsAsFile, ContainsData, HasAugmentedData, HasOrigin, FluentlyGetsAndSets;

    protected $set;
    protected $locale;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function globalSet($set = null)
    {
        return $this->fluentlyGetOrSet('set')->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->globalSet()->id();
    }

    public function handle()
    {
        return $this->globalSet()->handle();
    }

    public function title()
    {
        return $this->globalSet()->title();
    }

    public function path()
    {
        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store('globals')->directory(), '/'),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $this->handle(),
            'yaml',
        ]);
    }

    public function editUrl()
    {
        return $this->cpUrl('globals.variables.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('globals.variables.update');
    }

    protected function cpUrl($route)
    {
        $params = [$this->handle()];

        if (Site::hasMultiple()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
    }

    public function save()
    {
        $this
            ->globalSet()
            ->addLocalization($this)
            ->save();

        return $this;
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function sites()
    {
        return $this->globalSet()->sites();
    }

    public function blueprint()
    {
        return $this->globalSet()->blueprint() ?? $this->fallbackBlueprint();
    }

    protected function fallbackBlueprint()
    {
        $fields = collect($this->values())
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
                    'fields' => $fields->all(),
                ],
            ],
        ]);
    }

    public function fileData()
    {
        return array_merge([
            'origin' => $this->hasOrigin() ? $this->origin()->locale() : null,
        ], $this->data()->all());
    }

    public function reference()
    {
        return "globals::{$this->id()}";
    }

    protected function getOriginByString($origin)
    {
        return $this->globalSet()->in($origin);
    }
}
