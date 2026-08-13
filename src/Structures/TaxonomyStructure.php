<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\TaxonomyTree;
use Statamic\Contracts\Structures\TaxonomyTreeRepository;
use Statamic\Facades\Blink;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Str;

class TaxonomyStructure extends Structure
{
    public function title($title = null)
    {
        if (func_num_args() === 1) {
            throw new \LogicException('Title cannot be set.');
        }

        return $this->taxonomy()->title();
    }

    public function taxonomy()
    {
        return Blink::once('taxonomy-structure-taxonomy-'.$this->handle(), function () {
            return Taxonomy::findByHandle($this->handle());
        });
    }

    public function expectsRoot($expectsRoot = null)
    {
        if (func_num_args() === 1) {
            throw new \LogicException('Taxonomy structures do not support root terms.');
        }

        return false;
    }

    public function collections($collections = null)
    {
        //
    }

    public function newTreeInstance()
    {
        return app(TaxonomyTree::class);
    }

    /**
     * Get the localized ancestor slug path for a term (e.g. "animals/cat"), or an
     * empty string for root-level terms. Returns null for terms not in the tree.
     */
    public function termParentUri($term): ?string
    {
        $page = $this->tree()->find($term->inDefaultLocale()->slug());

        if (! $page) {
            return null;
        }

        return collect($this->ancestorsOf($page))
            ->map(fn ($slug) => $this->localizedSlug($slug, $term->locale()))
            ->implode('/');
    }

    /**
     * Get the default-locale slugs of a page's ancestors, root-first.
     */
    public function ancestorsOf(Page $page): array
    {
        $ancestors = [];

        while ($page = $page->parent()) {
            array_unshift($ancestors, $page->id());
        }

        return $ancestors;
    }

    private function localizedSlug(string $slug, string $site)
    {
        $term = Term::find($this->handle().'::'.$slug);

        return $term ? $term->in($site)->slug() : $slug;
    }

    /**
     * Normalize branch keys/IDs and drop duplicate slugs, without appending
     * missing terms. Use this before mutating the persisted tree.
     */
    public function repairTree(array $tree): array
    {
        $tree = $this->normalizeTree($tree);

        if ($this->getTermSlugsFromTree($tree)->duplicates()->isNotEmpty()) {
            $tree = $this->removeDuplicateTermsFromTree($tree);
        }

        return $tree;
    }

    public function validateTree(array $tree, string $locale): array
    {
        $tree = $this->repairTree($tree);
        $slugs = $this->getTermSlugsFromTree($tree);

        $existingSlugs = Blink::once('taxonomy-structure-term-slugs-'.$this->handle(), function () {
            return Term::query()
                ->where('taxonomy', $this->handle())
                ->get()
                ->map(fn ($term) => $term->inDefaultLocale()->slug());
        });

        if (($nonExistent = $slugs->diff($existingSlugs))->isNotEmpty()) {
            $tree = $this->removeTermReferencesFromTree($tree, $nonExistent);
        }

        $missingTerms = $existingSlugs->diff($slugs)->map(function ($slug) {
            return ['term' => $slug];
        })->values()->all();

        return array_merge($tree, $missingTerms);
    }

    /**
     * Coerce branches to `term: {slug}`. Older trees (and Tree::append) stored
     * collection-style `entry: taxonomy::slug` keys and/or full term IDs.
     */
    protected function normalizeTree(array $tree): array
    {
        return collect($tree)
            ->map(function ($branch) {
                $slug = $this->slugFromBranch($branch);

                if (! $slug) {
                    return null;
                }

                $normalized = ['term' => $slug];

                if (isset($branch['children'])) {
                    $normalized['children'] = $this->normalizeTree($branch['children']);
                }

                return $normalized;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function slugFromBranch(array $branch): ?string
    {
        $value = $branch['term'] ?? $branch['entry'] ?? null;

        if (! $value) {
            return null;
        }

        return Str::after($value, $this->handle().'::');
    }

    /**
     * Keep the first occurrence of each slug (and its children); later
     * duplicates have their children promoted into place.
     */
    protected function removeDuplicateTermsFromTree(array $tree, $seen = null): array
    {
        $seen ??= collect();

        return collect($tree)->flatMap(function ($branch) use ($seen) {
            $slug = $branch['term'] ?? null;
            $children = isset($branch['children'])
                ? $this->removeDuplicateTermsFromTree($branch['children'], $seen)
                : [];

            if (! $slug || $seen->contains($slug)) {
                return $children;
            }

            $seen->push($slug);

            if ($children) {
                $branch['children'] = $children;
            } else {
                unset($branch['children']);
            }

            return [$branch];
        })->values()->all();
    }

    protected function getTermSlugsFromTree($tree)
    {
        return collect($tree)
            ->map(function ($item) {
                return [
                    'term' => $item['term'] ?? null,
                    'children' => isset($item['children']) ? $this->getTermSlugsFromTree($item['children']) : null,
                ];
            })
            ->flatten()
            ->filter();
    }

    protected function removeTermReferencesFromTree($tree, $slugs)
    {
        return collect($tree)
            ->reject(function ($branch) use ($slugs) {
                return $slugs->contains($branch['term'] ?? null);
            })
            ->map(function ($branch) use ($slugs) {
                if (isset($branch['children'])) {
                    $branch['children'] = $this->removeTermReferencesFromTree($branch['children'], $slugs);

                    if (empty($branch['children'])) {
                        unset($branch['children']);
                    }
                }

                return $branch;
            })
            ->values()
            ->all();
    }

    public function save()
    {
        $this->taxonomy()->structure($this)->save();

        return true;
    }

    public function tree()
    {
        return $this->in(null);
    }

    public function trees()
    {
        return collect([$this->tree()]);
    }

    public function in($site)
    {
        return Blink::once("taxonomy-structure-tree-{$this->handle()}", function () {
            $tree = app(TaxonomyTreeRepository::class)->find($this->handle());

            return $tree ?? $this->makeTree($this->taxonomy()->sites()->first());
        });
    }

    public function existsIn($site)
    {
        return $this->taxonomy()->sites()->contains($site);
    }
}
