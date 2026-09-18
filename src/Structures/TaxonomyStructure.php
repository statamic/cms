<?php

namespace Statamic\Structures;

use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Structures\TaxonomyTree;
use Statamic\Contracts\Structures\TaxonomyTreeRepository;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Str;

use function Statamic\trans as __;

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
     * Get the localized ancestor URI path for a term (e.g. "animals/cat"), or an
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
        $tree = $this->removeNonExistentTermsFromTree($this->repairTree($tree));

        $missingTerms = $this->existingTermSlugs()->diff($this->getTermSlugsFromTree($tree))->map(function ($slug) {
            return ['term' => $slug];
        })->values()->all();

        return array_merge($tree, $missingTerms);
    }

    /**
     * Drop branches referencing terms that no longer exist, promoting their
     * children into place. Unlike validateTree() this doesn't append terms
     * that are missing from the tree, so it can measure the persisted tree.
     */
    protected function removeNonExistentTermsFromTree(array $tree): array
    {
        $nonExistent = $this->getTermSlugsFromTree($tree)->diff($this->existingTermSlugs());

        return $nonExistent->isEmpty()
            ? $tree
            : $this->removeTermReferencesFromTree($tree, $nonExistent);
    }

    protected function existingTermSlugs()
    {
        return Blink::once('taxonomy-structure-term-slugs-'.$this->handle(), function () {
            return Term::query()
                ->where('taxonomy', $this->handle())
                ->get()
                ->map(fn ($term) => $term->inDefaultLocale()->slug());
        });
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

    /**
     * Drop the branches for the given slugs, promoting their children into their
     * position rather than taking the whole subtree down with them. Matches what
     * removeDuplicateTermsFromTree() and deleting a term both already do.
     */
    protected function removeTermReferencesFromTree($tree, $slugs)
    {
        return collect($tree)->flatMap(function ($branch) use ($slugs) {
            $children = isset($branch['children'])
                ? $this->removeTermReferencesFromTree($branch['children'], $slugs)
                : [];

            if ($slugs->contains($branch['term'] ?? null)) {
                return $children;
            }

            if ($children) {
                $branch['children'] = $children;
            } else {
                unset($branch['children']);
            }

            return [$branch];
        })->values()->all();
    }

    /**
     * Nest $slug under $parentSlug in the persisted tree. No-op if $slug is
     * already somewhere in the tree, or if it would be its own parent. If the
     * parent isn't in the persisted tree yet (e.g. it was just created as part
     * of the same path), it's appended at the root so the child can actually
     * nest under it.
     */
    public function graftTerm(string $slug, string $parentSlug, bool $save = true): void
    {
        if ($slug === $parentSlug) {
            return;
        }

        $tree = $this->tree();
        $raw = $this->repairTree($tree->fileData()['tree'] ?? []);

        if ($this->termIsInBranches($raw, $slug)) {
            return;
        }

        if (! $this->termIsInBranches($raw, $parentSlug)) {
            $raw[] = ['term' => $parentSlug];
        }

        $this->assertCanNest($parentSlug);

        $tree->tree($this->appendSlugToParent($raw, $parentSlug, $slug));

        if ($save) {
            $tree->save();
        }
    }

    public function assertDoesNotExceedMaxDepth(array $tree, int $depth = 1): void
    {
        if (! $max = $this->maxDepth()) {
            return;
        }

        if ($depth > $max) {
            throw ValidationException::withMessages([
                'tree' => __('statamic::validation.parent_exceeds_max_depth'),
            ]);
        }

        foreach ($tree as $branch) {
            if (! empty($branch['children'])) {
                $this->assertDoesNotExceedMaxDepth($branch['children'], $depth + 1);
            }
        }
    }

    /**
     * Assert that a child may be nested under $parentSlug. A parent that isn't in
     * the persisted tree yet would be grafted in at the root, so it counts as
     * depth 1. This is the only max-depth rule for nesting a single term.
     */
    public function assertCanNest(string $parentSlug): void
    {
        if (! $max = $this->maxDepth()) {
            return;
        }

        if (($this->depthOfTerm($parentSlug) ?? 1) >= $max) {
            throw ValidationException::withMessages([
                'parent' => __('statamic::validation.parent_exceeds_max_depth'),
            ]);
        }
    }

    /**
     * The depth a term sits at in the persisted tree, or null if it isn't in it.
     *
     * References to terms that no longer exist are dropped first, since they
     * don't render either. Otherwise the max depth rules would count levels
     * that aren't in the tree the user sees.
     */
    public function depthOfTerm(string $slug): ?int
    {
        $tree = $this->removeNonExistentTermsFromTree($this->repairTree($this->tree()->fileData()['tree'] ?? []));

        return $this->depthOfSlug($tree, $slug);
    }

    private function depthOfSlug(array $branches, string $slug, int $depth = 1): ?int
    {
        foreach ($branches as $branch) {
            if (($branch['term'] ?? null) === $slug) {
                return $depth;
            }

            if (isset($branch['children']) && ($found = $this->depthOfSlug($branch['children'], $slug, $depth + 1)) !== null) {
                return $found;
            }
        }

        return null;
    }

    private function termIsInBranches(array $branches, string $slug): bool
    {
        foreach ($branches as $branch) {
            if (($branch['term'] ?? null) === $slug) {
                return true;
            }

            if (isset($branch['children']) && $this->termIsInBranches($branch['children'], $slug)) {
                return true;
            }
        }

        return false;
    }

    private function appendSlugToParent(array $branches, string $parentSlug, string $slug): array
    {
        foreach ($branches as &$branch) {
            if (($branch['term'] ?? null) === $parentSlug) {
                $branch['children'] = array_merge($branch['children'] ?? [], [['term' => $slug]]);

                return $branches;
            }

            if (isset($branch['children'])) {
                $branch['children'] = $this->appendSlugToParent($branch['children'], $parentSlug, $slug);
            }
        }

        return $branches;
    }

    /**
     * The blinks are forgotten after the parent, since it reads the tree back
     * through in(), which would otherwise blink it again.
     */
    public function flushCache($site = null)
    {
        $structure = parent::flushCache($site);

        Blink::forget("taxonomy-structure-tree-{$this->handle()}");
        Blink::forget('taxonomy-structure-term-slugs-'.$this->handle());

        return $structure;
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

            return $tree ?? $this->makeTree(Site::default()->handle());
        });
    }

    public function existsIn($site)
    {
        return $this->taxonomy()->sites()->contains($site);
    }
}
