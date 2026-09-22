<?php

namespace Statamic\Stache\Repositories;

use Closure;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Contracts\Taxonomies\TermRepository as RepositoryContract;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Exceptions\TermNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\URL;
use Statamic\Query\Scopes\AllowsScopes;
use Statamic\Stache\Query\TermQueryBuilder;
use Statamic\Stache\Stache;
use Statamic\Support\Str;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;
use Statamic\Taxonomies\TermRoute;

class TermRepository implements RepositoryContract
{
    use AllowsScopes;

    protected $stache;
    protected $store;
    protected $substitutionsById = [];
    protected $substitutionsByUri = [];

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('terms');
    }

    public function all(): TermCollection
    {
        return $this->query()->get();
    }

    public function whereTaxonomy(string $handle): TermCollection
    {
        if (! Taxonomy::find($handle)) {
            throw new TaxonomyNotFoundException($handle);
        }

        return $this->query()->where('taxonomy', $handle)->get();
    }

    public function whereInTaxonomy(array $handles): TermCollection
    {
        if (empty($handles)) {
            return TermCollection::make();
        }

        collect($handles)
            ->reject(fn ($taxonomy) => Taxonomy::find($taxonomy))
            ->each(fn ($taxonomy) => throw new TaxonomyNotFoundException($taxonomy));

        return $this->query()->whereIn('taxonomy', $handles)->get();
    }

    public function find($id): ?Term
    {
        // Association indexes add keys for every site an entry uses the term. Prefer
        // the taxonomy's own default site so we load the term file instead of a stub
        // from another site's association. A term with no file only exists under the
        // locales of the entries referencing it, though, so fall back to any site.
        // The preference can then only ever disambiguate, never eliminate a match.
        if (is_string($id) && str_contains($id, '::')) {
            $taxonomy = Taxonomy::findByHandle(Str::before($id, '::'));

            if ($taxonomy && $site = $taxonomy->sites()->first()) {
                if ($term = $this->query()->where('id', $id)->where('site', $site)->first()) {
                    return $term;
                }
            }
        }

        return $this->query()->where('id', $id)->first();
    }

    public function findByUri(string $uri, ?string $site = null): ?Term
    {
        $site = $site ?? $this->stache->sites()->first();

        if ($substitute = $this->substitutionsByUri[$site.'@'.$uri] ?? null) {
            return $substitute;
        }

        $uri = URL::tidy(Str::ensureLeft($uri, '/'));

        if ($term = $this->findTermMatchingUri($uri, $site)) {
            return $term;
        }

        [$collection, $stripped] = $this->stripCollectionPrefix($uri, $site);

        if (! $collection) {
            return null;
        }

        return $this->findTermMatchingUri($stripped, $site, automagicOnly: true)
            ?->collection($collection);
    }

    private function findTermMatchingUri(string $uri, string $site, bool $automagicOnly = false): ?Term
    {
        foreach (Taxonomy::all()->sortByDesc(fn ($taxonomy) => strlen((string) $taxonomy->termRoute($site))) as $taxonomy) {
            if ($automagicOnly && $taxonomy->hasCustomRoutes($site)) {
                continue;
            }

            if ($term = $this->findTermByRoute($taxonomy, $uri, $site)) {
                return $term;
            }
        }

        return null;
    }

    private function stripCollectionPrefix(string $uri, string $site): array
    {
        $collection = Collection::all()
            ->first(function ($collection) use ($uri, $site) {
                if (Str::startsWith($uri, $collection->uri($site))) {
                    return true;
                }

                return Str::startsWith($uri.'/', '/'.$collection->handle().'/');
            });

        if (! $collection) {
            return [null, $uri];
        }

        $stripped = Str::after($uri, $collection->uri($site) ?? $collection->handle());
        $stripped = URL::tidy(Str::ensureLeft($stripped, '/'));

        return [$collection, $stripped];
    }

    private function findTermByRoute($taxonomy, string $uri, string $site): ?Term
    {
        $pattern = $taxonomy->termRoute($site);

        if (! $pattern) {
            return null;
        }

        $pattern = URL::tidy($pattern);
        $captures = $this->matchRoutePattern($uri, $pattern, $taxonomy->nestable());

        if ($captures === null) {
            return null;
        }

        if (isset($captures['slug'])) {
            $term = $this->query()
                ->where('slug', $captures['slug'])
                ->where('taxonomy', $taxonomy->handle())
                ->where('site', $site)
                ->first();
        } else {
            $term = $this->query()
                ->where('taxonomy', $taxonomy->handle())
                ->where('site', $site)
                ->get()
                ->first(fn ($term) => $term->uri() === $uri);
        }

        if (! $term) {
            return null;
        }

        if ($term->uri() !== $uri && ! $taxonomy->nestable()) {
            return null;
        }

        return $term;
    }

    /**
     * Match a URI against a route pattern like `/topics/{parent_uri}/{slug}`.
     *
     * `{parent_uri}` may span multiple segments and is optional, so nestable
     * taxonomies get a second pattern for terms at the root of the tree.
     */
    private function matchRoutePattern(string $uri, string $pattern, bool $nestable): ?array
    {
        foreach ($this->routePatternRegexes($pattern, $nestable) as $regex) {
            if (preg_match($regex, $uri, $matches)) {
                return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            }
        }

        return null;
    }

    private function routePatternRegexes(string $pattern, bool $nestable): array
    {
        return Blink::store('term-route-regexes')->once($pattern.'@'.(int) $nestable, function () use ($pattern, $nestable) {
            $patterns = [$pattern];

            if ($nestable && Str::contains($pattern, '{parent_uri}')) {
                $patterns[] = TermRoute::withoutParentUri($pattern);
            }

            return collect($patterns)
                ->map(function ($pattern) {
                    try {
                        return TermRoute::toRegex($pattern);
                    } catch (\DomainException|\LogicException $e) {
                        Log::warning("Taxonomy route [{$pattern}] could not be compiled: {$e->getMessage()}");

                        return null;
                    }
                })
                ->filter()
                ->all();
        });
    }

    public function findOrFail($id): Term
    {
        $term = $this->find($id);

        if (! $term) {
            throw new TermNotFoundException($id);
        }

        return $term;
    }

    public function findOrMake($id)
    {
        return $this->find($id) ?? $this->make();
    }

    public function findOr($id, Closure $callback)
    {
        return $this->find($id) ?? $callback();
    }

    public function save($term)
    {
        $this->store
            ->store($term->taxonomyHandle())
            ->save($term);
    }

    public function delete($term)
    {
        $this->store
            ->store($term->taxonomyHandle())
            ->delete($term);
    }

    public function query()
    {
        $this->ensureAssociations();

        return new TermQueryBuilder($this->store);
    }

    public function updateOrders($taxonomy, $ids = null)
    {
        $this->store->store($taxonomy->handle())->updateOrders($ids);
    }

    public function make(?string $slug = null): Term
    {
        return app(Term::class)->slug($slug);
    }

    public function entriesCount(Term $term, ?string $status = null): int
    {
        // Entries tagged with a descendant term count towards their ancestors, to
        // match what querying for the term's entries returns. On a flat taxonomy
        // there are no descendants, so this is just the term's own slug.
        $slug = $term->inDefaultLocale()->slug();

        $slugs = $term->taxonomy()?->termWithDescendants($slug) ?? [$slug];

        $items = $this->store->store($term->taxonomyHandle())
            ->index('associations')
            ->items()
            ->whereIn('value', $slugs);

        if ($term instanceof LocalizedTerm) {
            $items = $items->where('site', $term->locale());
        }

        if ($collection = $term->collection()) {
            $items = $items->where('collection', $collection->handle());
        }

        // An entry tagged with both a term and one of its descendants has an
        // association for each, but should only be counted once.
        if (count($slugs) > 1) {
            $items = $items->unique('entry');
        }

        if ($status) {
            return Entry::query()
                ->whereIn('id', $items->pluck('entry')->all())
                ->when($collection, fn ($query) => $query->where('collection', $collection->handle()))
                ->whereStatus($status)
                ->count();
        }

        return $items->count();
    }

    protected function ensureAssociations()
    {
        Taxonomy::all()->each(function ($taxonomy) {
            $this->store->store($taxonomy->handle())->index('associations');
        });
    }

    public static function bindings(): array
    {
        return [
            Term::class => \Statamic\Taxonomies\Term::class,
        ];
    }

    public function substitute($item)
    {
        $this->substitutionsById[$item->id()] = $item;
        $this->substitutionsByUri[$item->locale().'@'.$item->uri()] = $item;
    }

    public function applySubstitutions($items)
    {
        if (empty($this->substitutionsById)) {
            return $items;
        }

        return $items->map(function ($item) {
            return $this->substitutionsById[$item->id()] ?? $item;
        });
    }
}
