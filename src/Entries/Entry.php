<?php

namespace Statamic\Entries;

use ArrayAccess;
use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Stache;
use Statamic\Facades\Blueprint;
use Statamic\Routing\Routable;
use Statamic\Facades\Collection;
use Illuminate\Support\Carbon;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Revisions\Revisable;
use Facades\Statamic\View\Cascade;
use Statamic\Events\Data\EntrySaved;
use Statamic\Events\Data\EntrySaving;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Contracts\Entries\Entry as Contract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Data\HasOrigin;
use Statamic\Contracts\Data\Localization;

class Entry implements Contract, AugmentableContract, Responsable, Localization, ArrayAccess
{
    use Routable {
        uri as routableUri;
    }

    use ContainsData, ExistsAsFile, Augmentable, FluentlyGetsAndSets, Revisable;

    use HasOrigin {
        value as originValue;
        values as originValues;
    }

    protected $id;
    protected $collection;
    protected $blueprint;
    protected $date;
    protected $locale;
    protected $localizations;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function collection($collection = null)
    {
        return $this
            ->fluentlyGetOrSet('collection')
            ->setter(function ($collection) {
                return $collection instanceof \Statamic\Contracts\Entries\Collection ? $collection->handle() : $collection;
            })
            ->getter(function ($collection) {
                return $collection ? Collection::findByHandle($collection) : null;
            })
            ->args(func_get_args());
    }

    public function blueprint()
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return $blueprint
                    ? $this->collection()->ensureEntryBlueprintFields(Blueprint::find($blueprint))
                    : $this->defaultBlueprint();
            })
            ->args(func_get_args());
    }

    public function collectionHandle()
    {
        return $this->collection;
    }

    public function toArray()
    {
        return [];
    }

    public function augmentedArrayData()
    {
        return $this->values()->merge([
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
        ])->merge($this->supplements)->all();
    }

    public function toCacheableArray()
    {
        return [
            'collection' => $this->collectionHandle(),
            'locale' => $this->locale,
            'origin' => $this->hasOrigin() ? $this->origin()->id() : null,
            'slug' => $this->slug(),
            'date' => optional($this->date())->format('Y-m-d-Hi'),
            'published' => $this->published(),
            'path' => $this->initialPath() ?? $this->path(),
            'data' => $this->data(),
        ];
    }

    public function delete()
    {
        Facades\Entry::delete($this);

        return true;
    }

    public function editUrl()
    {
        return $this->hasStructure()
            ? $this->cpUrl('structures.entries.edit')
            : $this->cpUrl('collections.entries.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('collections.entries.update');
    }

    public function publishUrl()
    {
        return $this->cpUrl('collections.entries.published.store');
    }

    public function unpublishUrl()
    {
        return $this->cpUrl('collections.entries.published.destroy');
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

    public function livePreviewUrl()
    {
        return $this->cpUrl('collections.entries.preview.edit');
    }

    protected function cpUrl($route)
    {
        return cp_route($route, [$this->collectionHandle(), $this->id(), $this->slug()]);
    }

    public function reference()
    {
        return "entry::{$this->id()}";
    }

    public function defaultBlueprint()
    {
        if ($blueprint = $this->value('blueprint')) {
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

        Facades\Entry::save($this);

        $this->taxonomize();

        optional(Collection::findByMount($this))->updateEntryUris();

        EntrySaved::dispatch($this, []);  // TODO: Fix test

        return true;
    }

    public function taxonomize()
    {
        Facades\Entry::taxonomize($this);
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
            $this->fileExtension()
        ]);
    }

    public function order($order = null)
    {
        if (func_num_args() === 0) {
            return $this->collection()->getEntryOrder($this->id());
        }

        $this->collection()->setEntryPosition($this->id(), $order)->save();

        return $this;
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? optional($this->origin())->template() ?? $this->collection()->template();
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
                if ($date === null) {
                    return null;
                }

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
        return $this->hasDate() && $this->date()->format('H:i:s') !== '00:00:00';
    }

    public function sites()
    {
        return $this->collection()->sites();
    }

    public function fileData()
    {
        $array = $this->data()->merge([
            'id' => $this->id(),
            'origin' => optional($this->origin)->id(),
            'published' => $this->published === false ? false : null,
        ]);

        if ($this->blueprint && $this->collection()->entryBlueprints()->count() > 1) {
            $array['blueprint'] = $this->blueprint;
        }

        return $array->all();
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
            'data' => $this->data()->except(['updated_by', 'updated_at'])->all(),
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

    public function private()
    {
        $collection = $this->collection();

        if (! $collection->dated()) {
            return false;
        }

        if ($collection->futureDateBehavior() === 'private' && $this->date()->isFuture()) {
            return true;
        }

        if ($collection->pastDateBehavior() === 'private' && $this->date()->isPast()) {
            return true;
        }

        return false;
    }

    public function in($locale)
    {
        if ($locale === $this->locale) {
            return $this;
        }

        if (! $this->isRoot()) {
            return $this->root()->in($locale);
        }

        return $this->descendants()->get($locale);
    }

    public function descendants()
    {
        if (! $this->localizations) {
            $this->localizations = Facades\Entry::query()
                ->where('collection', $this->collectionHandle())
                ->where('origin', $this->id())->get()
                ->keyBy->locale();
        }

        $localizations = collect($this->localizations);

        foreach ($localizations as $loc) {
            $localizations = $localizations->merge($loc->descendants());
        }

        return $localizations;
    }

    public function existsIn($locale)
    {
        return $this->in($locale) !== null;
    }

    public function addLocalization($entry)
    {
        $entry->origin($this);

        $this->localizations[$entry->locale()] = $entry;

        return $this;
    }

    public function makeLocalization($site)
    {
        return Facades\Entry::make()
            ->collection($this->collection)
            ->origin($this)
            ->locale($site)
            ->slug($this->slug())
            ->date($this->date());
    }

    public function supplementTaxonomies()
    {
        // TODO: This is just here to make things work without rewriting a bunch of places.
    }

    public function revisionsEnabled()
    {
        return $this->collection()->revisionsEnabled();
    }

    public function structure()
    {
        return $this->collection()->structure();
    }

    public function hasStructure()
    {
        return $this->collection()->hasStructure();
    }

    public function parent()
    {
        if (! $this->hasStructure()) {
            return null;
        }

        return $this->structure()->in($this->locale())->page($this->id())->parent();
    }

    public function route()
    {
        return $this->collection()->route();
    }

    public function routeData()
    {
        $data = $this->values()->merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
            'mount' => $this->collection()->url(),
        ]);

        if ($this->hasDate()) {
            $data = $data->merge([
                'year' => $this->date()->format('Y'),
                'month' => $this->date()->format('m'),
                'day' => $this->date()->format('d'),
            ]);
        }

        return $data->all();
    }

    public function uri()
    {
        if ($structure = $this->structure()) {
            return $structure->entryUri($this);
        }

        return $this->routableUri();
    }

    public function fileExtension()
    {
        return 'md';
    }

    public function fresh()
    {
        return Facades\Entry::find($this->id);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Entry::{$method}(...$parameters);
    }

    protected function getOriginByString($origin)
    {
        return Facades\Entry::find($origin);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->value($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    public function value($key)
    {
        return $this->originValue($key) ?? $this->collection()->cascade($key);
    }

    public function values()
    {
        return $this->collection()->cascade()->merge($this->originValues());
    }
}
