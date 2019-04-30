<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Site;
use Statamic\API\User;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\Routable;
use Illuminate\Support\Carbon;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\Localization;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Revisions\Revisable;
use Facades\Statamic\View\Cascade;
use Statamic\Events\Data\EntrySaved;
use Statamic\Events\Data\EntrySaving;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Entries\LocalizedEntry as Contract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Data\Localization as LocalizationContract;

class LocalizedEntry implements Contract, Arrayable, AugmentableContract, Responsable, LocalizationContract
{
    use Routable, Localization, ContainsData, ExistsAsFile, Augmentable, Revisable, FluentlyGetsAndSets {
        FluentlyGetsAndSets::fluentlyGetOrSet insteadof Localization;
    }

    protected $date;

    public function entry($entry = null)
    {
        return call_user_func_array([$this, 'localizable'], func_get_args());
    }

    public function reference()
    {
        return "entry::{$this->id()}";
    }

    public function collection()
    {
        return $this->entry()->collection();
    }

    public function collectionHandle()
    {
        return $this->collection()->handle();
    }

    public function toArray()
    {
        return array_merge($this->data, [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'edit_url' => $this->editUrl(),
            'permalink' => $this->absoluteUrl(),
            'amp_url' => $this->ampUrl(),
            'published' => $this->published(),
            'date' => $this->date(),
            'is_entry' => true,
            'collection' => $this->collectionHandle(),
            'last_modified' => $lastModified = $this->lastModified(),
            'updated_at' => $lastModified,
            'updated_by' => optional($this->lastModifiedBy())->toArray(),
        ], $this->supplements);
    }

    public function editUrl()
    {
        return $this->cpUrl('collections.entries.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('collections.entries.update');
    }

    public function publishUrl()
    {
        return $this->cpUrl('collections.entries.published.store');
    }

    public function revisionsUrl()
    {
        return $this->cpUrl('collections.entries.revisions.index');
    }

    public function createRevisionUrl()
    {
        return $this->cpUrl('collections.entries.revisions.store');
    }

    public function restoreRevisionUrl()
    {
        return $this->cpUrl('collections.entries.restore-revision');
    }

    protected function cpUrl($route)
    {
        return cp_route($route, [$this->collectionHandle(), $this->id(), $this->slug(), $this->locale()]);
    }

    public function blueprint()
    {
        if ($blueprint = $this->get('blueprint')) {
            return $this->collection()->ensureEntryBlueprintFields(
                Blueprint::find($blueprint)
            );
        }

        return $this->collection()->entryBlueprint();
    }

    public function save()
    {
        if (EntrySaving::dispatch($this) === false) {
            return false;
        }

        API\Entry::save($this);

        $this->entry()->addLocalization($this);

        if ($this->shouldPropagate) {
            $this->propagate();
        }

        EntrySaved::dispatch($this, []);  // TODO: Fix test

        return true;
    }

    public function delete()
    {
        API\Entry::deleteLocalization($this);

        return true;
    }

    public function path()
    {
        $prefix = '';

        if ($this->hasDate()) {
            $prefix = $this->date->format($this->hasTime() ? 'Y-m-d-Hi' : 'Y-m-d') . '.';
        }

        return vsprintf('%s/%s/%s%s%s.%s', [
            rtrim(Stache::store('entries')->directory(), '/'),
            $this->collectionHandle(),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $prefix,
            $this->slug(),
            'md'
        ]);
    }

    public function order($order = null)
    {
        if (func_num_args() === 0) {
            return $this->collection()->getEntryOrder($this->id());
        }

        $this->collection()->setEntryPosition($this->id(), $order);

        return $this;
    }

    public function supplementTaxonomies()
    {
        // Added this method because a bunch of things call it.
        // Rather than update those things right now, just add this so things continue to hum along.
        // TODO: Get rid of this during taxonomy refactor.
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? $this->collection()->template();
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? $this->collection()->layout();
            })
            ->args(func_get_args());
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }

    public function toLivePreviewResponse($request, $extras)
    {
        Cascade::set('live_preview', $extras);

        return $this->toResponse($request);
    }

    public function date($date = null)
    {
        return $this
            ->fluentlyGetOrSet('date')
            ->setter(function ($date) {
                if ($date instanceof Carbon) {
                    return $date;
                }

                if (strlen($date) === 10) {
                    return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
                }

                return Carbon::createFromFormat('Y-m-d-Hi', $date);
            })
            ->args(func_get_args());
    }

    public function hasDate()
    {
        return $this->date !== null;
    }

    public function hasTime()
    {
        return $this->hasDate() && !$this->date()->isMidnight();
    }

    public function sites()
    {
        return $this->collection()->sites();
    }

    protected function fileData()
    {
        return array_merge($this->data(), [
            'id' => $this->id(),
            'published' => $this->published === false ? false : null
        ]);
    }

    public function ampable()
    {
        return $this->collection()->ampable();
    }

    protected function revisionKey()
    {
        return vsprintf('collections/%s/%s/%s', [
            $this->collectionHandle(),
            $this->locale(),
            $this->id()
        ]);
    }

    protected function revisionAttributes()
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
            'data' => Arr::except($this->data(), ['updated_by', 'updated_at']),
        ];
    }

    public function makeFromRevision($revision)
    {
        $entry = clone $this;

        if (! $revision) {
            return $entry;
        }

        $attrs = $revision->attributes();

        return $entry
            ->published($attrs['published'])
            ->data($attrs['data'])
            ->slug($attrs['slug']);
    }

    public function lastModified()
    {
        return $this->has('updated_at')
            ? Carbon::createFromTimestamp($this->get('updated_at'))
            : $this->fileLastModified();
    }

    public function lastModifiedBy()
    {
        return User::find($this->get('updated_by'));
    }
}
