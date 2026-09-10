<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html\Concerns;

use Statamic\View\Antlers\Language\Analyzers\Html\Arena;

/**
 * @internal
 */
trait AnchorsSourceNodes
{
    protected function arenaNodeIsDescendantOf($node, $ancestor)
    {
        $seen = [];

        for ($parent = $this->arena->parent($node); $parent !== null; $parent = $this->arena->parent($parent)) {
            if (isset($seen[$parent])) {
                throw new \LogicException('Cycle detected while resolving an HTML arena ancestor.');
            }

            $seen[$parent] = true;

            if ($parent === $ancestor) {
                return true;
            }
        }

        return false;
    }

    protected function arenaNodeHasSourceAnchoredAncestor($node)
    {
        return $this->arenaNodeAncestorSourceAnchor($node) !== null;
    }

    protected function arenaNodeAncestorSourceAnchor($node)
    {
        $seen = [];

        for ($parent = $this->arena->parent($node); $parent !== null; $parent = $this->arena->parent($parent)) {
            if (isset($seen[$parent])) {
                throw new \LogicException('Cycle detected while resolving an HTML arena source anchor.');
            }

            $seen[$parent] = true;

            if (isset($this->arena->sourceAnchor[$parent])) {
                return $this->arena->sourceAnchor[$parent];
            }
        }

        return null;
    }

    protected function anchorRecoveredSourceNode(
        $node,
        $container,
        $sourceTable,
        $sourceTableTail,
        $previous = null,
        $sourcePositionAnchor = null
    ) {
        $containerIsSynthetic = (bool) ($this->arena->kind($container) & Arena::SYNTHETIC);
        $sourcePositionAnchor = $sourcePositionAnchor
            ?? ($this->arena->sourceAnchor[$container] ?? null);

        if ($sourcePositionAnchor === null && $sourceTable === null && ! $containerIsSynthetic) {
            return;
        }

        $sourcePositionTarget = null;

        if ($sourcePositionAnchor !== null) {
            $sourcePositionTarget = $this->arena->anchorTarget[$sourcePositionAnchor] ?? null;
        }

        // Continue an existing source-position anchor without anchoring the same subtree twice.
        if ($sourcePositionTarget !== null) {
            $ancestorSourceAnchor = $this->arenaNodeAncestorSourceAnchor($node);
            $ancestorAlreadyInsideSourceTarget = $ancestorSourceAnchor !== null
                && $this->arenaNodeIsDescendantOf($ancestorSourceAnchor, $sourcePositionTarget);
            $nodeIsInsideSourceTarget = $this->arenaNodeIsDescendantOf($node, $sourcePositionTarget);

            if ($nodeIsInsideSourceTarget) {
                if ($this->anchorAfterSourcePositionTail($node, $sourcePositionTarget)) {
                    return;
                }
            }

            if (! $nodeIsInsideSourceTarget
                && ! $ancestorAlreadyInsideSourceTarget) {
                $anchor = $this->arena->anchor($node);
                $this->arena->insertAfter($sourcePositionAnchor, $anchor);

                return;
            }
        }

        // Content recovered outside a table still renders at its authored position inside it.
        if ($sourceTable !== null
            && ! $this->arenaNodeIsDescendantOf($node, $sourceTable)
            && ! $this->arenaNodeHasSourceAnchoredAncestor($node)) {
            $anchor = $this->arena->anchor($node);

            if ($sourceTableTail !== null && $this->arena->parent($sourceTableTail) === $sourceTable) {
                $this->arena->insertAfter($sourceTableTail, $anchor);
            } else {
                $this->arena->append($sourceTable, $anchor);
            }

            return;
        }

        if ($previous === null) {
            $previous = $this->arena->previous($node);
        }

        // A synthetic container has no authored opening tag; follow its previous child's anchor.
        if ($containerIsSynthetic
            && $previous !== null
            && isset($this->arena->sourceAnchor[$previous])) {
            $previousAnchor = $this->arena->sourceAnchor[$previous];
            $anchorParent = $this->arena->parent($previousAnchor);

            if ($anchorParent !== null
                && ($this->arena->kind($anchorParent) & Arena::KIND_MASK) === Arena::ELEMENT
                && $this->arena->closing[$anchorParent] !== '') {
                return;
            }

            $anchor = $this->arena->anchor($node);
            $this->arena->insertAfter($previousAnchor, $anchor);
        }
    }

    protected function anchorAfterSourcePositionTail($node, $sourceContainer)
    {
        $sourcePositionAnchor = $this->arena->sourceAnchor[$sourceContainer] ?? null;

        if ($sourcePositionAnchor === null) {
            return false;
        }

        $anchorParent = $this->arena->parent($sourcePositionAnchor);

        if ($anchorParent === null) {
            return false;
        }

        $sourceTail = $this->arena->lastChild($anchorParent);

        if ($sourceTail === null
            || $sourceTail === $sourcePositionAnchor
            || ($this->arena->kind($sourceTail) & Arena::KIND_MASK) !== Arena::SOURCE_ANCHOR) {
            return false;
        }

        $this->arena->insertAfter($sourceTail, $this->arena->anchor($node));

        return true;
    }

    protected function anchorArenaNodeForMove($node)
    {
        if ($this->arena->parent($node) === null) {
            return;
        }

        if (! isset($this->arena->sourceAnchor[$node])) {
            $this->arena->anchorExisting($node);

            return;
        }

        $this->arena->detach($node);
    }

    protected function markDirtyFromContainer()
    {
        $this->markDirty();
    }
}
