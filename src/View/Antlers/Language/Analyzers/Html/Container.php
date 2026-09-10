<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Statamic\View\Html\ElementCollection;

/** @internal */
trait Container
{
    /** @return NodeSequence|$this */
    public function content($content = null)
    {
        if (func_num_args() !== 0) {
            $replacement = $this->normalizeArenaNode($content);
            $this->assertArenaInsertable($replacement);
            $existing = $this->children();

            $this->append($replacement);

            foreach ($existing as $node) {
                if ($node !== $replacement) {
                    $this->removeChild($node);
                }
            }

            return $this;
        }

        return NodeSequence::childrenOf($this->document, $this->arenaContainerId());
    }

    public function children()
    {
        return array_map([$this->document, 'handle'], $this->document->arena()->children($this->arenaContainerId()));
    }

    /** @return ElementCollection */
    public function descendants($name = null)
    {
        return $this->arenaDescendants($this->descendantNameFilter($name));
    }

    public function firstDescendant($name = null)
    {
        return $this->arenaFirstDescendant($this->descendantNameFilter($name));
    }

    public function firstChild()
    {
        $child = $this->document->arena()->firstChild($this->arenaContainerId());

        return $this->containerChild($child);
    }

    public function lastChild()
    {
        $child = $this->document->arena()->lastChild($this->arenaContainerId());

        return $this->containerChild($child);
    }

    public function append($content)
    {
        $node = $this->normalizeArenaNode($content);
        $this->assertArenaInsertable($node);
        $this->detachArenaNodeForInsert($node);
        $this->document->arena()->append($this->arenaContainerId(), $node->arenaId());
        $this->markDirty();

        return $this;
    }

    public function prepend($content)
    {
        $node = $this->normalizeArenaNode($content);
        $this->assertArenaInsertable($node);
        $this->detachArenaNodeForInsert($node);
        $arena = $this->document->arena();
        $first = $arena->firstChild($this->arenaContainerId());

        if ($first === null) {
            $arena->append($this->arenaContainerId(), $node->arenaId());
        } else {
            $arena->insertBefore($first, $node->arenaId());
        }

        $this->markDirty();

        return $this;
    }

    public function hasChild(Node $node)
    {
        if ($node->document() !== $this->document) {
            return false;
        }

        if ($node->arenaId() === null) {
            return false;
        }

        return $this->document->arena()->hasChild($this->arenaContainerId(), $node->arenaId());
    }

    public function siblingsFor(Node $node)
    {
        return $this->children();
    }

    public function insertBefore(Node $reference, $content)
    {
        $this->insertArenaRelative($reference, $content, false);
    }

    public function insertAfter(Node $reference, $content)
    {
        $this->insertArenaRelative($reference, $content, true);
    }

    public function replaceChild(Node $reference, $content)
    {
        $this->replaceArenaChild($reference, $content);
    }

    public function removeChild(Node $node)
    {
        if (! $this->hasChild($node)) {
            return;
        }

        $this->releaseArenaSourceAnchors($node->arenaId());
        $this->document->arena()->detach($node->arenaId());
        $this->markDirty();
    }

    public function createElement($name)
    {
        return $this->document->createArenaElement($name);
    }

    protected function renderChildren()
    {
        return $this->document->renderArenaChildren($this->arenaContainerId());
    }

    protected function sourceChildren()
    {
        return $this->children();
    }

    protected function normalizeArenaNode($content)
    {
        if ($content instanceof Node) {
            if ($content->document() !== $this->document) {
                throw new \InvalidArgumentException('HTML graph nodes cannot be moved between documents.');
            }

            if ($content->arenaId() === null) {
                throw new \InvalidArgumentException('Only nodes created by this HTML document can be inserted.');
            }

            return $content;
        }

        $id = $this->document->arena()->text((string) $content);

        return $this->document->handle($id);
    }

    protected function assertArenaInsertable(Node $node)
    {
        $arena = $this->document->arena();

        if (isset($arena->embeddedOwner[$node->arenaId()])) {
            throw new \LogicException('Antlers regions embedded in HTML markup cannot be moved as DOM children. Mutate the owning node instead.');
        }

        for ($container = $this->arenaContainerId(); $container !== null; $container = $arena->parent($container)) {
            if ($container === $node->arenaId()) {
                throw new \LogicException('HTML graph nodes cannot be inserted into themselves or their descendants.');
            }
        }
    }

    protected function detachArenaNodeForInsert(Node $node)
    {
        $id = $node->arenaId();
        $arena = $this->document->arena();

        if ($arena->parent($id) !== null && ! isset($arena->embeddedOwner[$id])) {
            $this->releaseArenaSourceAnchors($id);
            $arena->detach($id);
        }
    }

    protected function insertArenaRelative(Node $reference, $content, $after)
    {
        if (! $this->hasChild($reference)) {
            return;
        }

        $node = $this->normalizeArenaNode($content);

        if ($node === $reference) {
            return;
        }

        $this->assertArenaInsertable($node);
        $this->detachArenaNodeForInsert($node);
        $arena = $this->document->arena();
        $referenceId = $reference->arenaId();
        $nodeId = $node->arenaId();
        $anchor = $arena->sourceAnchor[$referenceId] ?? null;

        if ($after) {
            $arena->insertAfter($referenceId, $nodeId);
        } else {
            $arena->insertBefore($referenceId, $nodeId);
        }

        if ($anchor !== null) {
            $sourceAnchor = $arena->anchor($nodeId);

            if ($after) {
                $arena->insertAfter($anchor, $sourceAnchor);
            } else {
                $arena->insertBefore($anchor, $sourceAnchor);
            }
        }

        $this->markDirty();
    }

    protected function replaceArenaChild(Node $reference, $content)
    {
        if (! $this->hasChild($reference)) {
            return;
        }

        $node = $this->normalizeArenaNode($content);

        if ($node === $reference) {
            return;
        }

        $this->assertArenaInsertable($node);
        $this->detachArenaNodeForInsert($node);
        $arena = $this->document->arena();
        $referenceId = $reference->arenaId();
        $nodeId = $node->arenaId();
        $anchor = $arena->sourceAnchor[$referenceId] ?? null;
        $arena->insertBefore($referenceId, $nodeId);
        $arena->detach($referenceId);

        if ($anchor !== null) {
            unset($arena->sourceAnchor[$referenceId]);
            $arena->anchorTarget[$anchor] = $nodeId;
            $arena->sourceAnchor[$nodeId] = $anchor;
        }

        $this->releaseArenaSourceAnchors($referenceId);
        $this->markDirty();
    }

    protected function releaseArenaSourceAnchors($id)
    {
        $arena = $this->document->arena();
        $anchor = $arena->sourceAnchor[$id] ?? null;

        if ($anchor !== null) {
            unset($arena->sourceAnchor[$id]);
            $arena->anchorTarget[$anchor] = null;
        }

        for ($child = $arena->firstChild($id); $child !== null; $child = $arena->next($child)) {
            if (($arena->kind($child) & Arena::KIND_MASK) === Arena::SOURCE_ANCHOR) {
                $target = $arena->anchorTarget[$child] ?? null;

                if ($target !== null) {
                    unset($arena->sourceAnchor[$target]);
                    $arena->anchorTarget[$child] = null;
                }
            } else {
                $this->releaseArenaSourceAnchors($child);
            }
        }
    }

    protected function arenaContainerId()
    {
        if ($this instanceof Document) {
            return 0;
        }

        return $this->arenaId;
    }

    protected function descendantNameFilter($name)
    {
        if ($name === null) {
            return [];
        }

        return [(string) $name];
    }

    protected function containerChild($child)
    {
        if ($child === null) {
            return null;
        }

        return $this->document->handle($child);
    }

    protected function arenaDescendants(array $names)
    {
        $arena = $this->document->arena();
        $set = array_map('strtolower', $names);
        $elements = [];

        $stack = array_reverse($arena->children($this->arenaContainerId()));

        while ($stack !== []) {
            $id = array_pop($stack);

            if (($arena->kind($id) & Arena::KIND_MASK) !== Arena::ELEMENT) {
                continue;
            }

            if ($set === [] || $this->arenaNameMatches($arena->name[$id], $set)) {
                $elements[] = $this->document->handle($id);
            }

            for ($child = $arena->lastChild($id); $child !== null; $child = $arena->previous($child)) {
                $stack[] = $child;
            }
        }

        return new ElementCollection($elements);
    }

    protected function arenaFirstDescendant(array $names)
    {
        $arena = $this->document->arena();
        $set = array_map('strtolower', $names);
        $stack = array_reverse($arena->children($this->arenaContainerId()));

        while ($stack !== []) {
            $id = array_pop($stack);

            if (($arena->kind($id) & Arena::KIND_MASK) !== Arena::ELEMENT) {
                continue;
            }

            if ($set === [] || $this->arenaNameMatches($arena->name[$id], $set)) {
                return $this->document->handle($id);
            }

            for ($child = $arena->lastChild($id); $child !== null; $child = $arena->previous($child)) {
                $stack[] = $child;
            }
        }

        return null;
    }

    protected function arenaNameMatches($name, array $set)
    {
        return $name !== null && in_array(strtolower((string) $name), $set, true);
    }
}
