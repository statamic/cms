<?php

namespace Statamic\Globals;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Globals\Variables as Contract;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasOrigin;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\GlobalVariablesBlueprintFound;
use Statamic\Events\GlobalVariablesCreated;
use Statamic\Events\GlobalVariablesCreating;
use Statamic\Events\GlobalVariablesDeleted;
use Statamic\Events\GlobalVariablesDeleting;
use Statamic\Events\GlobalVariablesSaved;
use Statamic\Events\GlobalVariablesSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Variables implements Arrayable, ArrayAccess, Augmentable, Contract, Localization, ResolvesValuesContract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, HasOrigin, ResolvesValues, TracksQueriedRelations;

    protected $set;
    protected $locale;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function globalSet($set = null)
    {
        return $this->fluentlyGetOrSet('set')
            ->getter(function ($set) {
                return $set instanceof GlobalSet ? $set : Facades\GlobalSet::find($set);
            })
            ->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->handle().($this->locale ? '::'.$this->locale : '');
    }

    public function handle()
    {
        return $this->set instanceof GlobalSet ? $this->set->handle() : $this->set;
    }

    public function title()
    {
        return $this->globalSet()->title();
    }

    public function path()
    {
        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store('global-variables')->directory(), '/'),
            Site::multiEnabled() ? $this->locale().'/' : '',
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

        if (Site::multiEnabled()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
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
        $isNew = is_null(Facades\GlobalVariables::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && GlobalVariablesCreating::dispatch($this) === false) {
                return false;
            }

            if (GlobalVariablesSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\GlobalVariables::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                GlobalVariablesCreated::dispatch($this);
            }

            GlobalVariablesSaved::dispatch($this);
        }

        return $this;
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

        if ($withEvents && GlobalVariablesDeleting::dispatch($this) === false) {
            return false;
        }

        Facades\GlobalVariables::delete($this);

        if ($withEvents) {
            GlobalVariablesDeleted::dispatch($this);
        }

        return true;
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
        if (Blink::has($blink = 'globals-blueprint-'.$this->handle().'-'.$this->locale())) {
            return Blink::get($blink);
        }

        $blueprint = $this->globalSet()->blueprint() ?? $this->fallbackBlueprint();

        Blink::put($blink, $blueprint);

        GlobalVariablesBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
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
            'tabs' => [
                'main' => [
                    'fields' => array_values($fields->all()),
                ],
            ],
        ]);
    }

    public function fileData()
    {
        $data = $this->data()->all();

        if ($this->hasOrigin()) {
            $data['origin'] = $this->origin()->locale();
        }

        return $data;
    }

    protected function shouldRemoveNullsFromFileData()
    {
        return ! $this->hasOrigin();
    }

    public function reference()
    {
        return "globals::{$this->id()}";
    }

    protected function getOriginByString($origin)
    {
        return $this->globalSet()->in($origin);
    }

    protected function getOriginIdFromObject($origin)
    {
        return $origin->locale();
    }

    protected function getOriginBlinkKey()
    {
        return 'origin-globals-'.$this->id().'-'.$this->locale();
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedVariables($this);
    }

    protected function defaultAugmentedRelations()
    {
        return $this->selectedQueryRelations;
    }

    public function fresh()
    {
        return Facades\GlobalSet::find($this->handle())->in($this->locale);
    }
}
