<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

/** @internal */
abstract class Node
{
    use Conditionable, Tappable;

    /** @var Document */
    protected $document;

    /** @var int|null */
    protected $arenaId;

    public function __construct(Document $document, $arenaId = null)
    {
        $this->document = $document;
        $this->arenaId = $arenaId;
    }

    public function arenaId()
    {
        return $this->arenaId;
    }

    public function document()
    {
        return $this->document;
    }

    public function parent()
    {
        if ($this->arenaId === null) {
            return null;
        }

        $parent = $this->document->arena()->parent($this->arenaId);

        if ($parent === null) {
            return null;
        }

        return $this->document->handle($parent);
    }

    public function parentElement(): ?Element
    {
        $parent = $this->parent();

        while ($parent !== null && ! $parent instanceof Element) {
            $parent = $parent->parent();
        }

        return $parent;
    }

    public function previousSibling()
    {
        return $this->siblingAt(-1);
    }

    public function nextSibling()
    {
        return $this->siblingAt(1);
    }

    public function isSourceAnchored()
    {
        return $this->arenaId !== null
            && isset($this->document->arena()->sourceAnchor[$this->arenaId]);
    }

    public function sourceAnchor()
    {
        if ($this->arenaId === null) {
            return null;
        }

        $anchor = $this->document->arena()->sourceAnchor[$this->arenaId] ?? null;

        if ($anchor === null) {
            return null;
        }

        return $this->document->handle($anchor);
    }

    public function anchorSourceAt(SourceAnchor $anchor)
    {
        if ($this->arenaId === null || $anchor->arenaId() === null) {
            throw new \LogicException('Source anchors must belong to an HTML arena.');
        }

        if ($anchor->document() !== $this->document) {
            throw new \InvalidArgumentException('Source anchors and targets must belong to the same HTML document.');
        }

        $arena = $this->document->arena();
        $anchorId = $anchor->arenaId();
        $currentAnchor = $arena->sourceAnchor[$this->arenaId] ?? null;
        $currentTarget = $arena->anchorTarget[$anchorId] ?? null;

        if ($currentAnchor === $anchorId && $currentTarget === $this->arenaId) {
            return $this;
        }

        if ($currentAnchor !== null
            && ($arena->anchorTarget[$currentAnchor] ?? null) === $this->arenaId) {
            $arena->anchorTarget[$currentAnchor] = null;
        }

        if ($currentTarget !== null
            && ($arena->sourceAnchor[$currentTarget] ?? null) === $anchorId) {
            unset($arena->sourceAnchor[$currentTarget]);
        }

        $arena->sourceAnchor[$this->arenaId] = $anchorId;
        $arena->anchorTarget[$anchorId] = $this->arenaId;
        $this->touchDocument();
        $this->touchArenaNode($anchorId);

        return $this;
    }

    public function releaseSourceAnchor()
    {
        if ($this->arenaId === null) {
            return $this;
        }

        $arena = $this->document->arena();
        $anchor = $arena->sourceAnchor[$this->arenaId] ?? null;
        unset($arena->sourceAnchor[$this->arenaId]);

        if ($anchor !== null) {
            $arena->anchorTarget[$anchor] = null;
            $this->touchDocument();
            $this->touchArenaNode($anchor);
        }

        return $this;
    }

    public function releaseSourceAnchors()
    {
        $anchor = $this->sourceAnchor();

        if ($anchor !== null) {
            $this->releaseSourceAnchor();
        }
    }

    public function closest(string $name): ?Element
    {
        $element = $this;

        if (! $element instanceof Element) {
            $element = $this->parentElement();
        }

        while ($element !== null) {
            if ($element->is($name)) {
                return $element;
            }

            $element = $element->parentElement();
        }

        return null;
    }

    public function before($content)
    {
        $this->assertMutableSibling();
        $this->parent()->insertBefore($this, $content);

        return $this;
    }

    public function after($content)
    {
        $this->assertMutableSibling();
        $this->parent()->insertAfter($this, $content);

        return $this;
    }

    public function replaceWith($content)
    {
        $this->assertMutableSibling();
        $this->parent()->replaceChild($this, $content);

        return $this;
    }

    /** @return $this */
    public function wrapWith(Element $element)
    {
        if ($element === $this) {
            throw new \LogicException('HTML graph nodes cannot wrap themselves.');
        }

        $this->assertMutableSibling();
        $this->replaceWith($element);
        $element->append($this);

        return $this;
    }

    public function remove()
    {
        $this->assertMutableSibling();
        $this->parent()->removeChild($this);

        return $this;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    abstract protected function render();

    protected function siblingAt($direction)
    {
        if ($this->arenaId === null) {
            return null;
        }

        $owner = $this->document->arena()->embeddedOwner[$this->arenaId] ?? null;

        if ($owner !== null) {
            $siblings = $this->document->arena()->embedded[$owner];
            $index = array_search($this->arenaId, $siblings, true);

            if ($index === false) {
                return null;
            }

            $sibling = $siblings[$index + $direction] ?? null;

            return $this->siblingHandle($sibling);
        }

        $arena = $this->document->arena();

        if ($direction < 0) {
            return $this->siblingHandle($arena->previous($this->arenaId));
        }

        return $this->siblingHandle($arena->next($this->arenaId));
    }

    protected function siblingHandle($sibling)
    {
        if ($sibling === null) {
            return null;
        }

        return $this->document->handle($sibling);
    }

    protected function assertMutableSibling()
    {
        if (isset($this->document->arena()->embeddedOwner[$this->arenaId])) {
            throw new \LogicException('Antlers regions embedded in HTML markup cannot be moved as DOM children. Mutate the owning node instead.');
        }

        $parent = $this->parent();

        if ($parent === null) {
            throw new \LogicException('Detached HTML graph nodes cannot be mutated relative to siblings.');
        }

        if (! $parent->hasChild($this)) {
            throw new \LogicException('Antlers regions embedded in HTML markup cannot be moved as DOM children. Mutate the owning node instead.');
        }
    }

    protected function touchDocument()
    {
        $this->touchArenaNode($this->arenaId);
    }

    protected function touchArenaNode($arenaId)
    {
        if ($arenaId !== null && $this->document->arena()->parent($arenaId) !== null) {
            $this->document->markDirty();
        }
    }
}
