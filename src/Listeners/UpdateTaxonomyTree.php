<?php

namespace Statamic\Listeners;

use Statamic\Events\Subscriber;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;

class UpdateTaxonomyTree extends Subscriber
{
    protected $listeners = [
        TermSaved::class => 'handleSaved',
        TermDeleted::class => 'handleDeleted',
    ];

    /**
     * When a term's slug is renamed, update its reference in the taxonomy tree.
     */
    public function handleSaved(TermSaved $event)
    {
        $term = $event->term;

        if (! ($taxonomy = $term->taxonomy())->hasStructure()) {
            return;
        }

        $originalSlug = $term->getOriginal('slug');
        $newSlug = $term->slug();

        if (! $originalSlug || $originalSlug === $newSlug) {
            return;
        }

        $tree = $taxonomy->structure()->tree();

        // Operate on the repaired persisted tree to avoid the read-time
        // validation (which appends missing terms) kicking in.
        $raw = $taxonomy->structure()->repairTree($tree->fileData()['tree'] ?? []);

        $renamed = $this->renameBranches($raw, $originalSlug, $newSlug);

        if ($renamed !== $raw) {
            $tree->tree($renamed)->save();
        }
    }

    /**
     * When a term is deleted, remove its branch and promote its children into its position.
     */
    public function handleDeleted(TermDeleted $event)
    {
        $term = $event->term;

        if (! ($taxonomy = $term->taxonomy())->hasStructure()) {
            return;
        }

        $tree = $taxonomy->structure()->tree();

        $raw = $taxonomy->structure()->repairTree($tree->fileData()['tree'] ?? []);

        $removed = $this->removeBranchPromotingChildren($raw, $term->slug());

        if ($removed !== $raw) {
            $tree->tree($removed)->save();
        }
    }

    private function renameBranches(array $branches, string $from, string $to): array
    {
        return collect($branches)->map(function ($branch) use ($from, $to) {
            if (($branch['term'] ?? null) === $from) {
                $branch['term'] = $to;
            }

            if (isset($branch['children'])) {
                $branch['children'] = $this->renameBranches($branch['children'], $from, $to);
            }

            return $branch;
        })->all();
    }

    private function removeBranchPromotingChildren(array $branches, string $slug): array
    {
        return collect($branches)->flatMap(function ($branch) use ($slug) {
            if (($branch['term'] ?? null) === $slug) {
                return $this->removeBranchPromotingChildren($branch['children'] ?? [], $slug);
            }

            if (isset($branch['children'])) {
                $branch['children'] = $this->removeBranchPromotingChildren($branch['children'], $slug);

                if (empty($branch['children'])) {
                    unset($branch['children']);
                }
            }

            return [$branch];
        })->values()->all();
    }
}
