<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\Collection;

/** @internal */
trait Container
{
    public function content(Node|string|null $content = null): NodeSequence|static
    {
        if (func_num_args() !== 0) {
            $replacement = $this->normalizeArenaNode($content ?? '');
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

    public function children(): array
    {
        return array_map([$this->document, 'handle'], $this->document->arena()->children($this->arenaContainerId()));
    }

    /** @return Collection<int, Element> */
    public function descendants(?string $name = null): Collection
    {
        return $this->arenaDescendants($this->descendantNameFilter($name));
    }

    public function firstDescendant(?string $name = null): ?Element
    {
        return $this->arenaFirstDescendant($this->descendantNameFilter($name));
    }

    public function firstChild(): ?Node
    {
        $child = $this->document->arena()->firstChild($this->arenaContainerId());

        return $this->containerChild($child);
    }

    public function lastChild(): ?Node
    {
        $child = $this->document->arena()->lastChild($this->arenaContainerId());

        return $this->containerChild($child);
    }

    public function append(Node|string $content): static
    {
        $node = $this->normalizeArenaNode($content);
        $this->assertArenaInsertable($node);
        $this->detachArenaNodeForInsert($node);
        $this->document->arena()->append($this->arenaContainerId(), $node->arenaId());
        $this->markDirty();

        return $this;
    }

    public function prepend(Node|string $content): static
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

    public function hasChild(Node $node): bool
    {
        if ($node->document() !== $this->document) {
            return false;
        }

        if ($node->arenaId() === null) {
            return false;
        }

        return $this->document->arena()->hasChild($this->arenaContainerId(), $node->arenaId());
    }

    public function insertBefore(Node $reference, Node|string $content): void
    {
        $this->insertArenaRelative($reference, $content, false);
    }

    public function insertAfter(Node $reference, Node|string $content): void
    {
        $this->insertArenaRelative($reference, $content, true);
    }

    public function replaceChild(Node $reference, Node|string $content): void
    {
        $this->replaceArenaChild($reference, $content);
    }

    public function removeChild(Node $node): void
    {
        if (! $this->hasChild($node)) {
            return;
        }

        $this->releaseArenaSourceAnchors($node->arenaId());
        $this->document->arena()->detach($node->arenaId());
        $this->markDirty();
    }

    public function createElement(string $name): Element
    {
        return $this->document->createArenaElement($name);
    }

    protected function renderChildren(): string
    {
        return $this->document->renderArenaChildren($this->arenaContainerId());
    }

    protected function normalizeArenaNode(Node|string $content): Node
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

    protected function assertArenaInsertable(Node $node): void
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

    protected function detachArenaNodeForInsert(Node $node): void
    {
        $id = $node->arenaId();
        $arena = $this->document->arena();

        if ($arena->parent($id) !== null && ! isset($arena->embeddedOwner[$id])) {
            $this->releaseArenaSourceAnchors($id);
            $arena->detach($id);
            $this->document->markDirty();
        }
    }

    protected function insertArenaRelative(Node $reference, Node|string $content, bool $after): void
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

    protected function replaceArenaChild(Node $reference, Node|string $content): void
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

    protected function releaseArenaSourceAnchors(int $id): void
    {
        $arena = $this->document->arena();

        if (($arena->kind($id) & Arena::KIND_MASK) === Arena::SOURCE_ANCHOR) {
            $target = $arena->anchorTarget[$id] ?? null;

            if ($target !== null) {
                unset($arena->sourceAnchor[$target]);
                $arena->anchorTarget[$id] = null;
            }

            return;
        }

        $anchor = $arena->sourceAnchor[$id] ?? null;

        if ($anchor !== null) {
            unset($arena->sourceAnchor[$id]);
            $arena->anchorTarget[$anchor] = null;
        }

        for ($child = $arena->firstChild($id); $child !== null; $child = $arena->next($child)) {
            $this->releaseArenaSourceAnchors($child);
        }
    }

    protected function arenaContainerId(): ?int
    {
        if ($this instanceof Document) {
            return 0;
        }

        return $this->arenaId;
    }

    protected function descendantNameFilter(?string $name): array
    {
        if ($name === null) {
            return [];
        }

        return [(string) $name];
    }

    protected function containerChild(?int $child): ?Node
    {
        if ($child === null) {
            return null;
        }

        return $this->document->handle($child);
    }

    protected function arenaDescendants(array $names): Collection
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

        return new Collection($elements);
    }

    protected function arenaFirstDescendant(array $names): ?Element
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

    protected function arenaNameMatches(?string $name, array $set): bool
    {
        return $name !== null && in_array(strtolower((string) $name), $set, true);
    }
}
