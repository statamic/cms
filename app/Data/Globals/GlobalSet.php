<?php

namespace Statamic\Data\Globals;

use Statamic\API\Blueprint;
use Statamic\Data\Localizable;
use Statamic\Contracts\Data\Globals\GlobalSet as Contract;

class GlobalSet implements Contract
{
    use Localizable;

    protected $id;

    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    public function title()
    {
        return $this->localizations()->first()->title();
    }

    public function blueprint()
    {
        return Blueprint::find($this->get('blueprint')) ?? $this->fallbackBlueprint();
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

    protected function makeLocalization()
    {
        return new LocalizedGlobalSet;
    }

    public function toCacheableArray()
    {
        return [
            'localizations' => $this->localizations()->map(function ($entry) {
                return [
                    'handle' => $entry->handle(),
                    'path' => $entry->initialPath() ?? $entry->path(),
                    'data' => $entry->data()
                ];
            })->all()
        ];
    }
}
