<?php

namespace Statamic\Taxonomies;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Taxonomies\Taxonomy as Contract;
use Statamic\Data\ContainsCascadingData;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\TaxonomyCreated;
use Statamic\Events\TaxonomyCreating;
use Statamic\Events\TaxonomyDeleted;
use Statamic\Events\TaxonomyDeleting;
use Statamic\Events\TaxonomySaved;
use Statamic\Events\TaxonomySaving;
use Statamic\Events\TermBlueprintFound;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

use function Statamic\trans as __;

class Taxonomy implements Arrayable, ArrayAccess, AugmentableContract, ContainsQueryableValues, Contract, Responsable
{
    use ContainsCascadingData, ContainsSupplementalData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData;

    protected $handle;
    protected $title;
    protected $blueprints = [];
    protected $sites = [];
    protected $collection;
    protected $defaultPublishState = true;
    protected $searchIndex;
    protected $sortField;
    protected $sortDirection;
    protected $previewTargets = [];
    protected $template;
    protected $termTemplate;
    protected $layout;
    protected $structure;
    protected $structureContents;
    protected $routes;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    public function __construct()
    {
        $this->cascade = collect();
        $this->supplements = collect();
    }

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

    public function showUrl()
    {
        return cp_route('taxonomies.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('taxonomies.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('taxonomies.destroy', $this->handle());
    }

    public function editBlueprintUrl($blueprint)
    {
        return cp_route('blueprints.taxonomies.edit', [$this, $blueprint]);
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('taxonomies')->directory(), '/'),
            $this->handle,
        ]);
    }

    public function termBlueprints()
    {
        $blueprints = Blueprint::in('taxonomies/'.$this->handle());

        if ($blueprints->isEmpty()) {
            $blueprints = collect([$this->fallbackTermBlueprint()]);
        }

        return $blueprints->values()->map(function ($blueprint) {
            return $this->ensureTermBlueprintFields($blueprint);
        });
    }

    public function termBlueprint($blueprint = null, $term = null)
    {
        if (! $blueprint = $this->getBaseTermBlueprint($blueprint)) {
            return null;
        }

        $this->ensureTermBlueprintFields($blueprint);

        $blueprint->setParent($term ?? $this);

        // Only dispatch the event when there's no term.
        // When there is a term, the event is dispatched from the term.
        if (! $term) {
            Blink::once(
                'collection-termblueprintfound-'.$this->handle().'-'.$blueprint->handle(),
                fn () => TermBlueprintFound::dispatch($blueprint)
            );
        }

        return $blueprint;
    }

    private function getBaseTermBlueprint($blueprint)
    {
        if (is_null($blueprint)) {
            return $this->termBlueprints()->first();
        }

        return $this->termBlueprints()->keyBy->handle()->get($blueprint)
            ?? $this->termBlueprints()->keyBy->handle()->get(Str::singular($blueprint));
    }

    public function ensureTermBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true, 'validate' => 'max:200'], 'sidebar');

        return $blueprint;
    }

    public function fallbackTermBlueprint()
    {
        $blueprint = (clone Blueprint::find('default'))
            ->setHandle(Str::singular($this->handle()))
            ->setNamespace('taxonomies.'.$this->handle());

        $contents = $blueprint->contents();
        $contents['title'] = Str::singular($this->title());
        $blueprint->setContents($contents);

        return $blueprint;
    }

    public function hasVisibleTermBlueprint()
    {
        return $this->termBlueprints()->reject->hidden()->isNotEmpty();
    }

    public function structure($structure = null)
    {
        return $this
            ->fluentlyGetOrSet('structure')
            ->getter(function ($structure) {
                return Blink::once("taxonomy-{$this->id()}-structure", function () use ($structure) {
                    if (! $structure && $this->structureContents !== null) {
                        $structure = $this->structure = $this->makeStructureFromContents();
                    }

                    return $structure;
                });
            })
            ->setter(function ($structure) {
                if ($structure) {
                    $structure->handle($this->handle());
                }

                $this->structureContents = null;
                Blink::forget("taxonomy-{$this->id()}-structure");

                return $structure;
            })
            ->args(func_get_args());
    }

    public function structureContents(?array $contents = null)
    {
        return $this
            ->fluentlyGetOrSet('structureContents')
            ->setter(function ($contents) {
                Blink::forget("taxonomy-{$this->id()}-structure");
                $this->structure = null;

                return $contents;
            })
            ->getter(function ($contents) {
                if (! $structure = $this->structure()) {
                    return null;
                }

                // Empty arrays are stripped by ExistsAsFile::fileContents(), so
                // keep a placeholder when there's no max depth. Collections get
                // the same protection from their always-present `root` key.
                return Arr::removeNullValues([
                    'max_depth' => $structure->maxDepth(),
                ]) ?: ['max_depth' => null];
            })
            ->args(func_get_args());
    }

    protected function makeStructureFromContents()
    {
        return (new \Statamic\Structures\TaxonomyStructure)
            ->handle($this->handle())
            ->maxDepth($this->structureContents['max_depth'] ?? null);
    }

    public function structureHandle()
    {
        if (! $this->hasStructure()) {
            return null;
        }

        return $this->structure()->handle();
    }

    public function hasStructure()
    {
        return $this->structure !== null || $this->structureContents !== null;
    }

    public function orderable()
    {
        return optional($this->structure())->maxDepth() === 1;
    }

    public function hierarchical()
    {
        return $this->hasStructure() && $this->structure()->maxDepth() !== 1;
    }

    public function sortField()
    {
        return $this->sortField ?? 'title';
    }

    public function setSortField($field)
    {
        $this->sortField = $field;

        return $this;
    }

    public function sortDirection()
    {
        return $this->sortDirection ?? 'asc';
    }

    public function setSortDirection($dir)
    {
        $this->sortDirection = $dir;

        return $this;
    }

    public function queryTerms()
    {
        $query = Facades\Term::query()->where('taxonomy', $this->handle());

        if ($this->collection) {
            $query->where('collection', $this->collection->handle());
        }

        return $query;
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
        $isNew = is_null(Facades\Taxonomy::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && TaxonomyCreating::dispatch($this) === false) {
                return false;
            }

            if (TaxonomySaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\Taxonomy::save($this);

        Blink::forget("taxonomy-{$this->id()}-structure");
        Blink::forget("taxonomy-structure-taxonomy-{$this->handle()}");
        Blink::forget("taxonomy-structure-tree-{$this->handle()}");

        if ($withEvents) {
            if ($isNew) {
                TaxonomyCreated::dispatch($this);
            }

            TaxonomySaved::dispatch($this);
        }

        return true;
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

        if ($withEvents && TaxonomyDeleting::dispatch($this) === false) {
            return false;
        }

        if ($this->hasStructure()) {
            $this->structure()->trees()->each->delete();
        }

        $this->queryTerms()->get()->each->delete();

        Facades\Taxonomy::delete($this);

        if ($withEvents) {
            TaxonomyDeleted::dispatch($this);
        }

        return true;
    }

    public function truncate()
    {
        $this->queryTerms()->get()->each->delete();

        return true;
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title,
            'blueprints' => $this->blueprints,
            'preview_targets' => $this->previewTargetsForFile(),
            'template' => $this->template,
            'term_template' => $this->termTemplate,
            'layout' => $this->layout,
            'routes' => $this->routesForFile(),
        ];

        $data = Arr::removeNullValues(array_merge($data, [
            'sort_by' => $this->sortField,
            'sort_dir' => $this->sortDirection,
        ]));

        if ($this->hasStructure()) {
            $data['structure'] = $this->structureContents();
        }

        if (Site::multiEnabled()) {
            $data['sites'] = $this->sites;
        }

        $data['inject'] = $this->cascade->all();

        return $data;
    }

    public function defaultPublishState($state = null)
    {
        return $this->fluentlyGetOrSet('defaultPublishState')->args(func_get_args());
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                if (! Site::multiEnabled() || ! $sites) {
                    $sites = [Site::default()->handle()];
                }

                return collect($sites);
            })
            ->args(func_get_args());
    }

    /** @deprecated */
    public function revisionsEnabled($enabled = null)
    {
        return func_num_args() === 0 ? false : $this;
    }

    public function url()
    {
        if (! $url = $this->absoluteUrl()) {
            return null;
        }

        return URL::makeRelative($url);
    }

    public function urlWithoutRedirect()
    {
        return $this->url();
    }

    public function absoluteUrl()
    {
        if (! $this->uri()) {
            return null;
        }

        return URL::tidy(Site::current()->absoluteUrl().$this->uri());
    }

    public function uri()
    {
        $site = Site::current();

        if (! $route = $this->taxonomyRoute($site->handle())) {
            return null;
        }

        $prefix = $this->collection() ? $this->collection()->uri($site->handle()) : '/';

        return URL::tidy($prefix.$route);
    }

    public function routes($routes = null)
    {
        return $this->fluentlyGetOrSet('routes')->args(func_get_args());
    }

    public function routesEnabled(): bool
    {
        return $this->routes !== false;
    }

    public function taxonomyRoute(?string $site = null): ?string
    {
        if ($this->routes === false) {
            return null;
        }

        $site = $site ?? Site::current()->handle();
        $resolved = $this->routeForSite($this->routes, $site);

        if (is_string($resolved) && $resolved !== '') {
            return $this->normalizeRoute($resolved);
        }

        return $this->defaultTaxonomyRoute();
    }

    public function termRoute(?string $site = null): ?string
    {
        if (! $base = $this->taxonomyRoute($site)) {
            return null;
        }

        return $this->hierarchical()
            ? $base.'/{parent_uri}/{slug}'
            : $base.'/{slug}';
    }

    private function routeForSite($configured, string $site)
    {
        if (is_string($configured)) {
            return $configured;
        }

        if (is_array($configured) && array_key_exists($site, $configured)) {
            return $configured[$site] === '' ? null : $configured[$site];
        }

        return null;
    }

    private function defaultTaxonomyRoute(): string
    {
        return $this->normalizeRoute(str_replace('_', '-', $this->handle));
    }

    private function normalizeRoute(string $route): string
    {
        return URL::tidy($route);
    }

    private function routesForFile()
    {
        if ($this->routes === false) {
            return false;
        }

        if (! $this->routes) {
            return null;
        }

        return $this->routes;
    }

    public function collection($collection = null)
    {
        return $this->fluentlyGetOrSet('collection')->args(func_get_args());
    }

    public function collections()
    {
        return Collection::all()->filter(function ($collection) {
            return $collection
                ->taxonomies()
                ->keyBy->handle()
                ->has($this->handle);
        })->values();
    }

    public function toResponse($request)
    {
        if (! $this->uri() || ! view()->exists($this->template())) {
            throw new NotFoundHttpException;
        }

        if (! $this->sites()->contains($site = Site::current())) {
            throw new NotFoundHttpException;
        }

        if ($this->collection() && ! $this->collection()->sites()->contains($site)) {
            throw new NotFoundHttpException;
        }

        if ($this->collection() && ! $this->collections()->contains($this->collection())) {
            throw new NotFoundHttpException;
        }

        return (new \Statamic\Http\Responses\DataResponse($this))
            ->with([
                'terms' => $termQuery = $this->queryTerms()->where('site', $site),
                $this->handle() => $termQuery,
            ])
            ->toResponse($request);
    }

    public function get($key, $fallback = null)
    {
        // todo: Only used in DataResponse, added this method to prevent errors.
        return $fallback;
    }

    public function termTemplate($termTemplate = null)
    {
        return $this
            ->fluentlyGetOrSet('termTemplate')
            ->getter(function ($termTemplate) {
                if ($termTemplate ?? false) {
                    return $termTemplate;
                }

                $termTemplate = $this->handle().'.show';

                return $termTemplate;
            })
            ->args(func_get_args());
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                if ($template ?? false) {
                    return $template;
                }

                $template = $this->handle().'.index';

                if ($collection = $this->collection()) {
                    $template = $collection->handle().'.'.$template;
                }

                return $template;
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? config('statamic.system.layout', 'layout');
            })
            ->args(func_get_args());
    }

    public function createLabel()
    {
        $key = "messages.{$this->handle()}_taxonomy_create_term";

        $translation = __($key);

        if ($translation === $key) {
            return __('Create Term');
        }

        return $translation;
    }

    public function searchIndex($index = null)
    {
        return $this
            ->fluentlyGetOrSet('searchIndex')
            ->getter(function ($index) {
                return $index ? Search::index($index) : null;
            })
            ->args(func_get_args());
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Taxonomy::{$method}(...$parameters);
    }

    public function __toString()
    {
        return $this->handle();
    }

    public function augmentedArrayData()
    {
        return array_merge([
            'title' => $this->title(),
            'handle' => $this->handle(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
        ], $this->supplements->all());
    }

    public function previewTargets($targets = null)
    {
        return $this
            ->fluentlyGetOrSet('previewTargets')
            ->getter(function () {
                return $this->basePreviewTargets()->merge($this->additionalPreviewTargets());
            })
            ->args(func_get_args());
    }

    public function basePreviewTargets()
    {
        $targets = empty($this->previewTargets)
            ? $this->defaultPreviewTargets()
            : $this->previewTargets;

        return collect($targets)->map(function ($target) {
            return $target + ['refresh' => $target['refresh'] ?? true];
        });
    }

    public function addPreviewTargets($targets)
    {
        Facades\Taxonomy::addPreviewTargets($this->handle, $targets);

        return $this;
    }

    public function additionalPreviewTargets()
    {
        return Facades\Taxonomy::additionalPreviewTargets($this->handle)->map(function ($target) {
            return $target + ['refresh' => $target['refresh'] ?? true];
        });
    }

    private function defaultPreviewTargets()
    {
        return [
            [
                'label' => 'Term',
                'format' => '{permalink}',
                'refresh' => true,
            ],
        ];
    }

    private function previewTargetsForFile()
    {
        $targets = $this->previewTargets;

        if ($targets === $this->defaultPreviewTargets()) {
            return null;
        }

        return collect($targets)->map(function ($target) {
            if (! $target['format']) {
                return null;
            }

            return [
                'label' => $target['label'],
                'url' => $target['format'],
                'refresh' => $target['refresh'],
            ];
        })->filter()->values()->all();
    }

    public function hasCustomTemplate()
    {
        return $this->template !== null;
    }

    public function hasCustomTermTemplate()
    {
        return $this->termTemplate !== null;
    }

    public function termBlueprintCommandPaletteLinks()
    {
        $text = [__('Taxonomies'), __($this->title())];

        return $this
            ->termBlueprints()
            ->map(fn ($blueprint) => $blueprint->commandPaletteLink(
                type: $text,
                url: $this->editBlueprintUrl($blueprint),
            ));
    }

    public function getQueryableValue(string $field)
    {
        if (in_array($method = Str::camel($field), $this->queryableMethods())) {
            return $this->{$method}();
        }

        return $this->get($field);
    }

    private function queryableMethods(): array
    {
        return [
            'absoluteUrl', 'collection', 'collections', 'defaultPublishState', 'editUrl', 'handle',
            'hasSearchIndex', 'hasStructure', 'id', 'layout', 'orderable', 'path', 'revisionsEnabled',
            'searchIndex', 'sites', 'sortDirection', 'sortField', 'structureHandle', 'template',
            'termTemplate', 'title', 'uri', 'url',
        ];
    }
}
