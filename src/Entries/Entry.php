<?php

namespace Statamic\Entries;

use ArrayAccess;
use Closure;
use Facades\Statamic\Entries\InitiatorStack;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Traits\Localizable;
use LogicException;
use Statamic\Contracts\Auth\Protect\Protectable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Data\BulkAugmentable;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry as Contract;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Searchable as SearchableContract;
use Statamic\Data\ContainsComputedData;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\HasOrigin;
use Statamic\Data\Publishable;
use Statamic\Data\TracksLastModified;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntryCreating;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntryDeleting;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Exceptions\BlueprintNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Revisions\Revisable;
use Statamic\Routing\Routable;
use Statamic\Search\Searchable;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Entry implements Arrayable, ArrayAccess, Augmentable, BulkAugmentable, ContainsQueryableValues, Contract, Localization, Protectable, ResolvesValuesContract, Responsable, SearchableContract
{
    use ContainsComputedData, ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, Localizable, Publishable, Revisable, Searchable, TracksLastModified, TracksQueriedColumns, TracksQueriedRelations;

    use HasDirtyState;
    use HasOrigin {
        value as originValue;
        values as originValues;
    }
    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }
    use Routable {
        uri as routableUri;
    }

    protected $id;
    protected $collection;
    protected $blueprint;
    protected $date;
    protected $locale;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;
    protected $template;
    protected $layout;
    private $augmentationReferenceKey;
    private $computedCallbackCache;
    private $siteCache;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->supplements = clone $this->supplements;
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function getBulkAugmentationReferenceKey(): ?string
    {
        if ($this->augmentationReferenceKey) {
            return $this->augmentationReferenceKey;
        }

        $dataPart = implode('|', $this->data->keys()->sort()->all());

        return $this->augmentationReferenceKey = 'Entry::'.$this->blueprint()->namespace().'::'.$dataPart;
    }

    public function locale($locale = null)
    {
        return $this
            ->fluentlyGetOrSet('locale')
            ->setter(function ($locale) {
                $this->siteCache = null;

                return $locale instanceof \Statamic\Sites\Site ? $locale->handle() : $locale;
            })
            ->getter(function ($locale) {
                return $locale ?? Site::default()->handle();
            })
            ->args(func_get_args());
    }

    public function site()
    {
        if ($this->siteCache) {
            return $this->siteCache;
        }

        return $this->siteCache = Site::get($this->locale());
    }

    public function authors()
    {
        return collect($this->value('author'));
    }

    public function collection($collection = null)
    {
        if (func_num_args() === 0) {
            return $this->collection ? Blink::once("collection-{$this->collection}", function () {
                return Collection::findByHandle($this->collection);
            }) : null;
        }

        $this->computedCallbackCache = null;
        $this->collection = $collection instanceof \Statamic\Contracts\Entries\Collection ? $collection->handle() : $collection;

        return $this;
    }

    public function blueprint($blueprint = null)
    {
        $key = "entry-{$this->id()}-blueprint";

        return $this
            ->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) use ($key) {
                if (Blink::has($key)) {
                    return Blink::get($key);
                }

                if (! $blueprint) {
                    $blueprint = $this->hasOrigin()
                        ? $this->origin()->blueprint()->handle()
                        : $this->get('blueprint');
                }

                $blueprint = $this->collection()->entryBlueprint($blueprint, $this);

                if (! $blueprint) {
                    throw new BlueprintNotFoundException($this->value('blueprint'), 'collections/'.$this->collection()->handle());
                }

                Blink::put($key, $blueprint);

                EntryBlueprintFound::dispatch($blueprint, $this);

                return $blueprint;
            })
            ->setter(function ($blueprint) use ($key) {
                Blink::forget($key);

                return $blueprint instanceof \Statamic\Fields\Blueprint ? $blueprint->handle() : $blueprint;
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

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'collection' => $this->collectionHandle(),
            'locale' => $this->locale(),
            'origin' => $this->hasOrigin() ? $this->origin()->id() : null,
            'slug' => $this->slug(),
            'date' => optional($this->date())->format('Y-m-d-Hi'),
            'published' => $this->published(),
            'path' => $this->initialPath() ?? $this->path(),
        ], $this->data()->except(['updated_at'])->toArray());
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

        if ($withEvents && EntryDeleting::dispatch($this) === false) {
            return false;
        }

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
                    $this->page()?->pages()->all()->each(function ($child) use ($tree, $parent) {
                        $tree->move($child->id(), optional($parent)->id());
                    });
                    $tree->remove($this);
                })->save();
            });
        }

        Facades\Entry::delete($this);

        if ($withEvents) {
            EntryDeleted::dispatch($this);
        }

        return true;
    }

    public function deleteDescendants($withEvents = true)
    {
        $this->descendants()->each(function ($entry) use ($withEvents) {
            $entry->deleteDescendants($withEvents);
            $withEvents ? $entry->delete() : $entry->deleteQuietly();
        });

        Blink::forget('entry-descendants-'.$this->id());

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

        Blink::forget('entry-descendants-'.$this->id());

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

        return cp_route($route, [$this->collectionHandle(), $id]);
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

        return $this->save();
    }

    public function save()
    {
        $isNew = is_null(Facades\Entry::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && EntryCreating::dispatch($this) === false) {
                return false;
            }

            if (EntrySaving::dispatch($this) === false) {
                return false;
            }
        }

        if ($this->collection()->autoGeneratesTitles()) {
            $autoGeneratedTitle = $this->autoGeneratedTitle();
            $originAutoGeneratedTitle = $this->origin()?->autoGeneratedTitle();

            if ($autoGeneratedTitle !== $originAutoGeneratedTitle) {
                $this->set('title', $autoGeneratedTitle);
            }
        }

        $this->slug($this->slug());

        Facades\Entry::save($this);

        if ($this->id()) {
            Blink::store('entry-uris')->forget($this->id());
            Blink::store('structure-uris')->forget($this->id());
            Blink::store('structure-entries')->forget($this->id());
            Blink::forget($this->getOriginBlinkKey());
            Blink::store('collection-mounts')->flush();
        }

        $this->ancestors()->each(fn ($entry) => Blink::forget('entry-descendants-'.$entry->id()));

        $stack = InitiatorStack::entry($this)->push();

        $this->directDescendants()->each->{$withEvents ? 'save' : 'saveQuietly'}();

        $this->taxonomize();

        if ($this->isDirty('slug')) {
            optional(Collection::findByMount($this))->updateEntryUris();
            $this->updateChildPageUris();
        }

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                EntryCreated::dispatch($this);
            }

            EntrySaved::dispatch($this);
        }

        if ($isNew && ! $this->hasOrigin() && $this->collection()->propagate()) {
            $this->collection()->sites()
                ->reject($this->site()->handle())
                ->each(function ($siteHandle) use ($withEvents) {
                    $this->makeLocalization($siteHandle)->{$withEvents ? 'save' : 'saveQuietly'}();
                });
        }

        $stack->pop();

        $this->syncOriginal();

        return true;
    }

    private function updateChildPageUris()
    {
        $collection = $this->collection();

        // If it's orderable (single depth structure), there are no children to update.
        // If the collection has no route, there are no uris to update.
        // If there's no page, there are no children to update.
        if (
            $collection->orderable()
            || ! $this->route()
            || ! ($page = $this->page())
        ) {
            return;
        }

        if (empty($ids = $page->flattenedPages()->pluck('id'))) {
            return;
        }

        $collection->updateEntryUris($ids);
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

        if ($this->hasDate() && $this->date) {
            $format = 'Y-m-d';
            if ($this->hasTime()) {
                $format .= '-Hi';
                if ($this->hasSeconds()) {
                    $format .= 's';
                }
            }

            $prefix = $this->date->format($format).'.';
        }

        return vsprintf('%s/%s/%s%s%s.%s', [
            rtrim(Stache::store('entries')->directory(), '/'),
            $this->collectionHandle(),
            Site::multiEnabled() ? $this->locale().'/' : '',
            $prefix,
            $this->slug() ?? $this->id(),
            $this->fileExtension(),
        ]);
    }

    public function order()
    {
        if (! $this->hasStructure()) {
            return $this->value('order');
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
                $template = $template ?? $this->getSupplement('template') ?? $this->get('template') ?? optional($this->origin())->template() ?? $this->collection()->template();

                return $template === '@blueprint'
                    ? $this->inferTemplateFromBlueprint()
                    : $template;
            })
            ->args(func_get_args());
    }

    protected function inferTemplateFromBlueprint()
    {
        $template = $this->collection()->handle().'.'.$this->blueprint();

        $slugifiedTemplate = str_replace('_', '-', $template);

        return view()->exists($slugifiedTemplate)
            ? $slugifiedTemplate
            : $template;
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? $this->getSupplement('layout') ?? $this->get('layout') ?? optional($this->origin())->layout() ?? $this->collection()->layout();
            })
            ->args(func_get_args());
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }

    public function date($date = null)
    {
        return $this
            ->fluentlyGetOrSet('date')
            ->getter(function ($date) {
                if (! $this->collection()?->dated()) {
                    return null;
                }

                $date = $date ?? optional($this->origin())->date() ?? $this->lastModified();

                if (! $this->hasTime()) {
                    $date->startOfDay();
                }

                if (! $this->hasSeconds()) {
                    $date->startOfMinute();
                }

                return $date;
            })
            ->setter(function ($date) {
                if (! $this->collection()?->dated()) {
                    throw new LogicException('Cannot set date on non-dated collection entry.');
                }

                if ($date === null) {
                    return null;
                }

                if ($date instanceof \Carbon\CarbonInterface) {
                    return $date;
                }

                if (strlen($date) === 10) {
                    return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
                }

                if (strlen($date) === 15) {
                    return Carbon::createFromFormat('Y-m-d-Hi', $date)->startOfMinute();
                }

                return Carbon::createFromFormat('Y-m-d-His', $date);
            })
            ->args(func_get_args());
    }

    public function hasDate()
    {
        return $this->collection()->dated();
    }

    public function hasTime()
    {
        if (! $this->hasDate()) {
            return false;
        }

        return $this->blueprint()->field('date')->fieldtype()->timeEnabled();
    }

    public function hasSeconds()
    {
        if (! $this->hasTime()) {
            return false;
        }

        return $this->blueprint()->field('date')->fieldtype()->secondsEnabled();
    }

    public function hasExplicitDate(): bool
    {
        return $this->hasDate() && $this->date;
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
            $entry->date(Carbon::createFromTimestamp($date, config('app.timezone')));
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

    public function ancestors()
    {
        $ancestors = collect();

        $origin = $this->origin();

        while ($origin) {
            $ancestors->push($origin);
            $origin = $origin->origin();
        }

        return $ancestors;
    }

    public function directDescendants()
    {
        return Blink::once('entry-descendants-'.$this->id(), function () {
            return Facades\Entry::query()
                ->where('collection', $this->collectionHandle())
                ->where('origin', $this->id())->get()
                ->keyBy->locale();
        });
    }

    public function descendants()
    {
        $localizations = $this->directDescendants();

        foreach ($localizations as $loc) {
            $localizations = $localizations->merge($loc->descendants());
        }

        return $localizations;
    }

    public function existsIn($locale)
    {
        return $this->in($locale) !== null;
    }

    /** @deprecated */
    public function addLocalization($entry)
    {
        $entry->origin($this);

        return $this;
    }

    public function makeLocalization($site)
    {
        $localization = Facades\Entry::make()
            ->collection($this->collection)
            ->origin($this)
            ->locale($site)
            ->published($this->published)
            ->slug($this->slug());

        if ($callback = $this->addToStructure($site, $this->parent())) {
            $localization->afterSave($callback);
        }

        return $localization;
    }

    private function addToStructure($site, $parent = null): ?Closure
    {
        // If it's orderable (linear - a max depth of 1) then don't add it.
        if ($this->collection()->orderable()) {
            return null;
        }

        // Collection not structured? Don't add it.
        if (! $structure = $this->collection()->structure()) {
            return null;
        }

        $tree = $structure->in($site);
        $parent = optional($parent)->in($site);

        return function ($entry) use ($parent, $tree) {
            if (! $parent || $parent->isRoot()) {
                $tree->append($entry);
            } else {
                $tree->appendTo($parent->id(), $entry);
            }

            $tree->save();
        };
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

        return $this->structure()->in($this->locale())->find($id);
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
                'date' => $this->date(),
                'year' => $this->date()->format('Y'),
                'month' => $this->date()->format('m'),
                'day' => $this->date()->format('d'),
            ]);
        }

        return $data->all();
    }

    public function uri()
    {
        if ($this->id() && Blink::store('entry-uris')->has($this->id())) {
            return Blink::store('entry-uris')->get($this->id());
        }

        if (! $this->route()) {
            return null;
        }

        $uri = ($structure = $this->structure())
            ? $structure->entryUri($this)
            : $this->routableUri();

        if ($uri && $this->id()) {
            Blink::store('entry-uris')->put($this->id(), $uri);
        }

        return $uri;
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
        return $this->collection()->queryEntries()->where('id', $origin)->first();
    }

    protected function getOriginFallbackValues()
    {
        return $this->collection()->cascade();
    }

    protected function getOriginFallbackValue($key)
    {
        return $this->collection()->cascade()->get($key);
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'title', 'url', 'permalink', 'api_url'];
    }

    protected function defaultAugmentedRelations()
    {
        return $this->selectedQueryRelations;
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

    public function autoGeneratedTitle()
    {
        $format = $this->collection()->titleFormat($this->locale());

        if (! Str::contains($format, '{{')) {
            $format = preg_replace_callback('/{\s*([a-zA-Z0-9_\-\:\.]+)\s*}/', function ($match) {
                return "{{ {$match[1]} }}";
            }, $format);
        }

        // Since the slug is generated from the title, we'll avoid augmenting
        // the slug which could result in an infinite loop in some cases.
        $title = $this->withLocale($this->site()->lang(), fn () => (string) Antlers::parse($format, $this->augmented()->except('slug')->all()));

        return trim($title);
    }

    public function previewTargets()
    {
        return $this->collection()->previewTargets()->map(function ($target) {
            return [
                'label' => $target['label'],
                'format' => $target['format'],
                'url' => $this->resolvePreviewTargetUrl($target['format']),
            ];
        });
    }

    private function resolvePreviewTargetUrl($format)
    {
        if (! Str::contains($format, '{{')) {
            $format = preg_replace_callback('/{\s*([a-zA-Z0-9_\-\:\.]+)\s*}/', function ($match) {
                return "{{ {$match[1]} }}";
            }, $format);
        }

        return (string) Antlers::parse($format, array_merge($this->routeData(), [
            'config' => config()->all(),
            'site' => $this->site(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
            'locale' => $this->locale(),
        ]));
    }

    public function repository()
    {
        return app(EntryRepository::class);
    }

    public function getQueryableValue(string $field)
    {
        // Avoid using the authors() method.
        if ($field === 'authors') {
            return $this->value('authors');
        }

        // Reset the cached uri so it gets recalculated.
        if ($field === 'uri') {
            Blink::store('entry-uris')->forget($this->id());
        }

        if (method_exists($this, $method = Str::camel($field))) {
            return $this->{$method}();
        }

        $value = $this->value($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }

    public function getSearchValue(string $field)
    {
        return method_exists($this, $field) ? $this->$field() : $this->value($field);
    }

    public function getCpSearchResultBadge(): string
    {
        return $this->collection()->title();
    }

    protected function getComputedCallbacks()
    {
        if ($this->computedCallbackCache) {
            return $this->computedCallbackCache;
        }

        return $this->computedCallbackCache = Facades\Collection::getComputedCallbacks($this->collection);
    }

    public function __sleep()
    {
        if ($this->slug instanceof Closure) {
            $slug = $this->slug;
            $this->slug = $slug($this);
        }

        return array_keys(Arr::except(get_object_vars($this), ['cachedKeys', 'computedCallbackCache', 'siteCache', 'augmentationReferenceKey', 'resolvingValues']));
    }
}
