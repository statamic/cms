<?php

namespace Statamic\Entries;

use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Carbon;
use Statamic\Contracts\Auth\Protect\Protectable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry as Contract;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasOrigin;
use Statamic\Data\Publishable;
use Statamic\Data\TracksLastModified;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Revisions\Revisable;
use Statamic\Routing\Routable;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Entry implements Contract, Augmentable, Responsable, Localization, Protectable, ResolvesValuesContract
{
    use Routable {
        uri as routableUri;
    }

    use ContainsData, ExistsAsFile, HasAugmentedInstance, FluentlyGetsAndSets, Revisable, Publishable, TracksQueriedColumns, TracksLastModified;
    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }
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
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

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
        return $this
            ->fluentlyGetOrSet('locale')
            ->setter(function ($locale) {
                return $locale instanceof \Statamic\Sites\Site ? $locale->handle() : $locale;
            })
            ->getter(function ($locale) {
                return $locale ?? Site::default()->handle();
            })
            ->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function authors()
    {
        return collect($this->value('author'));
    }

    public function collection($collection = null)
    {
        return $this
            ->fluentlyGetOrSet('collection')
            ->setter(function ($collection) {
                return $collection instanceof \Statamic\Contracts\Entries\Collection ? $collection->handle() : $collection;
            })
            ->getter(function ($collection) {
                return $collection ? Blink::once("collection-{$collection}", function () use ($collection) {
                    return Collection::findByHandle($collection);
                }) : null;
            })
            ->args(func_get_args());
    }

    public function blueprint($blueprint = null)
    {
        $key = "entry-{$this->id()}-blueprint";

        return $this
            ->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) use ($key) {
                return Blink::once($key, function () use ($blueprint) {
                    if (! $blueprint) {
                        $blueprint = $this->hasOrigin()
                            ? $this->origin()->blueprint()->handle()
                            : $this->get('blueprint');
                    }

                    return $this->collection()->entryBlueprint($blueprint, $this);
                });
            })
            ->setter(function ($blueprint) use ($key) {
                Blink::forget($key);

                return $blueprint;
            })
            ->args(func_get_args());
    }

    public function collectionHandle()
    {
        return $this->collection;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedEntry($this);
    }

    public function toCacheableArray()
    {
        return [
            'collection' => $this->collectionHandle(),
            'locale' => $this->locale(),
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
        if ($this->descendants()->map->fresh()->filter()->isNotEmpty()) {
            throw new \Exception('Cannot delete an entry with localizations.');
        }

        if ($this->hasStructure()) {
            tap($this->structure(), function ($structure) {
                tap($structure->in($this->locale()), function ($tree) {
                    // Ugly, but it's moving all the child pages to the parent. TODO: Tidy.
                    $parent = $this->parent();
                    if (optional($parent)->isRoot()) {
                        $parent = null;
                    }
                    $this->page()->pages()->all()->each(function ($child) use ($tree, $parent) {
                        $tree->move($child->id(), optional($parent)->id());
                    });
                    $tree->remove($this);
                })->save();
            });
        }

        Facades\Entry::delete($this);

        EntryDeleted::dispatch($this);

        return true;
    }

    public function deleteDescendants()
    {
        $this->descendants()->each(function ($entry) {
            $entry->deleteDescendants();
            $entry->delete();
        });

        $this->localizations = null;

        return true;
    }

    public function detachLocalizations()
    {
        Facades\Entry::query()
            ->where('collection', $this->collectionHandle())
            ->where('origin', $this->id())
            ->get()
            ->each(function ($loc) {
                $loc
                    ->origin(null)
                    ->data($this->data()->merge($loc->data()))
                    ->save();
            });

        return true;
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
        return $this->collection()->route($this->locale())
            ? $this->cpUrl('collections.entries.preview.edit')
            : null;
    }

    protected function cpUrl($route)
    {
        if (! $id = $this->id()) {
            return null;
        }

        return cp_route($route, [$this->collectionHandle(), $id, $this->slug()]);
    }

    public function apiUrl()
    {
        if (! $id = $this->id()) {
            return null;
        }

        return Statamic::apiRoute('collections.entries.show', [$this->collectionHandle(), $id]);
    }

    public function reference()
    {
        return "entry::{$this->id()}";
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        $result = $this->save();

        $this->withEvents = true;

        return $result;
    }

    public function save()
    {
        $isNew = is_null(Facades\Entry::find($this->id()));

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];
        if ($this->withEvents) {
            if (EntrySaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\Entry::save($this);

        if ($this->id()) {
            Blink::store('structure-uris')->forget($this->id());
            Blink::store('structure-entries')->forget($this->id());
        }

        $this->taxonomize();

        optional(Collection::findByMount($this))->updateEntryUris();

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($this->withEvents) {
            if ($isNew) {
                EntryCreated::dispatch($this);
            }

            EntrySaved::dispatch($this);
        }

        if ($isNew && ! $this->hasOrigin() && $this->collection()->propagate()) {
            $this->collection()->sites()
                ->reject($this->site()->handle())
                ->each(function ($siteHandle) {
                    $this->makeLocalization($siteHandle)->save();
                });
        }

        return true;
    }

    public function taxonomize()
    {
        Facades\Entry::taxonomize($this);
    }

    public function path()
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath()
    {
        $prefix = '';

        if ($this->hasDate()) {
            $prefix = $this->date->format($this->hasTime() ? 'Y-m-d-Hi' : 'Y-m-d').'.';
        }

        return vsprintf('%s/%s/%s%s%s.%s', [
            rtrim(Stache::store('entries')->directory(), '/'),
            $this->collectionHandle(),
            Site::hasMultiple() ? $this->locale().'/' : '',
            $prefix,
            $this->slug(),
            $this->fileExtension(),
        ]);
    }

    public function order()
    {
        if (! $this->collection()->orderable()) {
            return null;
        }

        return $this->structure()->in($this->locale())
            ->flattenedPages()
            ->map->reference()
            ->flip()->get($this->id) + 1;
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
                return $layout ?? optional($this->origin())->layout() ?? $this->collection()->layout();
            })
            ->args(func_get_args());
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }

    public function toLivePreviewResponse($request, $extras)
    {
        Cascade::hydrated(function ($cascade) use ($extras) {
            $cascade->set('live_preview', $extras);
        });

        return $this->toResponse($request);
    }

    public function date($date = null)
    {
        return $this
            ->fluentlyGetOrSet('date')
            ->getter(function ($date) {
                return $date ?? $this->lastModified();
            })
            ->setter(function ($date) {
                if ($date === null) {
                    return null;
                }

                if ($date instanceof \Carbon\Carbon) {
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
        $origin = $this->origin();
        $blueprint = $this->blueprint()->handle();

        if ($origin && $this->blueprint()->handle() === $origin->blueprint()->handle()) {
            $blueprint = null;
        }

        $array = Arr::removeNullValues([
            'id' => $this->id(),
            'origin' => optional($origin)->id(),
            'published' => $this->published === false ? false : null,
            'blueprint' => $blueprint,
        ]);

        $data = $this->data()->all();

        if ($this->isRoot()) {
            $data = Arr::removeNullValues($data);
        }

        return array_merge($array, $data);
    }

    protected function shouldRemoveNullsFromFileData()
    {
        return false;
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
            $this->id(),
        ]);
    }

    protected function revisionAttributes()
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
            'date' => $this->collection()->dated() ? $this->date()->timestamp : null,
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

        $entry
            ->published($attrs['published'])
            ->data($attrs['data'])
            ->slug($attrs['slug']);

        if ($this->collection()->dated() && ($date = Arr::get($attrs, 'date'))) {
            $entry->date(Carbon::createFromTimestamp($date));
        }

        return $entry;
    }

    public function status()
    {
        $collection = $this->collection();

        if (! $this->published()) {
            return 'draft';
        }

        if (! $collection->dated() && $this->published()) {
            return 'published';
        }

        if ($collection->futureDateBehavior() === 'private' && $this->date()->isFuture()) {
            return 'scheduled';
        }

        if ($collection->pastDateBehavior() === 'private' && $this->date()->isPast()) {
            return 'expired';
        }

        return 'published';
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

        if ($collection->pastDateBehavior() === 'private' && $this->date()->lte(now())) {
            return true;
        }

        return false;
    }

    public function in($locale)
    {
        if ($locale === $this->locale()) {
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
        return optional($this->page())->parent();
    }

    public function page()
    {
        if (! $this->hasStructure()) {
            return null;
        }

        if (! $id = $this->id()) {
            return null;
        }

        return $this->structure()->in($this->locale())->page($id);
    }

    public function route()
    {
        return $this->collection()->route($this->locale());
    }

    public function routeData()
    {
        $data = $this->values()->merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
            'mount' => $this->collection()->uri($this->locale()),
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
        if (! $this->route()) {
            return null;
        }

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

    public function values()
    {
        return $this->collection()->cascade()->merge($this->originValues());
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    protected function shallowAugmentedArrayKeys()
    {
        return ['id', 'title', 'url', 'permalink', 'api_url'];
    }

    public function getProtectionScheme()
    {
        return $this->value('protect');
    }

    public function resolveGqlValue($field)
    {
        if ($field === 'site') {
            return $this->site();
        }

        if ($field === 'parent') {
            return optional($this->parent())->entry();
        }

        if ($field === 'blueprint') {
            return $this->blueprint();
        }

        return $this->traitResolveGqlValue($field);
    }
}
