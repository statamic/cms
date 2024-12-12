<?php

namespace Statamic\Taxonomies;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
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
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Taxonomy implements Arrayable, ArrayAccess, AugmentableContract, Contract, Responsable
{
    use ContainsCascadingData, ContainsSupplementalData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData;

    protected $handle;
    protected $title;
    protected $blueprints = [];
    protected $sites = [];
    protected $collection;
    protected $defaultPublishState = true;
    protected $revisions = false;
    protected $searchIndex;
    protected $previewTargets = [];
    protected $template;
    protected $termTemplate;
    protected $layout;
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

    public function sortField()
    {
        return 'title'; // todo
    }

    public function sortDirection()
    {
        return 'asc'; // todo
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
        ];

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

    public function revisionsEnabled($enabled = null)
    {
        return $this
            ->fluentlyGetOrSet('revisions')
            ->getter(function ($enabled) {
                if (! config('statamic.revisions.enabled') || ! Statamic::pro()) {
                    return false;
                }

                return false; // TODO

                return $enabled;
            })
            ->args(func_get_args());
    }

    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    public function urlWithoutRedirect()
    {
        return $this->url();
    }

    public function absoluteUrl()
    {
        return URL::tidy(Site::current()->absoluteUrl().$this->uri());
    }

    public function uri()
    {
        $site = Site::current();

        $prefix = $this->collection() ? $this->collection()->uri($site->handle()) : '/';

        return URL::tidy($prefix.str_replace('_', '-', '/'.$this->handle));
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

    public function isAssignedToCollection()
    {
        return $this->collections()->contains($this->collection());
    }

    public function toResponse($request)
    {
        if (! view()->exists($this->template())) {
            throw new NotFoundHttpException;
        }

        if (! $this->sites()->contains($site = Site::current())) {
            throw new NotFoundHttpException;
        }

        if ($this->collection() && ! $this->collection()->sites()->contains($site)) {
            throw new NotFoundHttpException;
        }

        if ($this->collection() && ! $this->isAssignedToCollection()) {
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
}
