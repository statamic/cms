<?php

namespace Statamic\Structures;

use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Structures\TaxonomyTree;
use Statamic\Contracts\Structures\TaxonomyTreeRepository;
use Statamic\Facades\Blink;
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

        $this->assertDoesNotExceedMaxDepth($tree);

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

    /**
     * Nest $slug under $parentSlug in the persisted tree. No-op if $slug is
     * already somewhere in the tree. If the parent isn't in the persisted
     * tree yet (e.g. it was just created as part of the same path), it's
     * appended at the root so the child can actually nest under it.
     */
    public function graftTerm(string $slug, string $parentSlug): void
    {
        $tree = $this->tree();
        $raw = $this->repairTree($tree->fileData()['tree'] ?? []);

        if ($this->termIsInBranches($raw, $slug)) {
            return;
        }

        if (! $this->termIsInBranches($raw, $parentSlug)) {
            $raw[] = ['term' => $parentSlug];
        }

        $this->assertParentAllowsChild($raw, $parentSlug);

        $tree->tree($this->appendSlugToParent($raw, $parentSlug, $slug))->save();
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

    private function assertParentAllowsChild(array $tree, string $parentSlug): void
    {
        if (! $max = $this->maxDepth()) {
            return;
        }

        $parentDepth = $this->depthOfSlug($tree, $parentSlug) ?? 1;

        if ($parentDepth >= $max) {
            throw ValidationException::withMessages([
                'parent' => __('statamic::validation.parent_exceeds_max_depth'),
            ]);
        }
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
