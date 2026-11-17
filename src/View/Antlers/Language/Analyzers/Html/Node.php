<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

/** @internal */
abstract class Node
{
    use Conditionable, Tappable;

    protected Document $document;

    protected ?int $arenaId = null;

    public function __construct(Document $document, ?int $arenaId = null)
    {
        $this->document = $document;
        $this->arenaId = $arenaId;
    }

    public function arenaId(): ?int
    {
        return $this->arenaId;
    }

    public function document(): Document
    {
        return $this->document;
    }

    public function parent(): Node|Document|null
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

    public function previousSibling(): ?Node
    {
        return $this->siblingAt(-1);
    }

    public function nextSibling(): ?Node
    {
        return $this->siblingAt(1);
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

    public function before(Node|string $content): static
    {
        $this->assertMutableSibling();
        $this->parent()->insertBefore($this, $content);

        return $this;
    }

    public function after(Node|string $content): static
    {
        $this->assertMutableSibling();
        $this->parent()->insertAfter($this, $content);

        return $this;
    }

    public function replaceWith(Node|string $content): static
    {
        $this->assertMutableSibling();
        $this->parent()->replaceChild($this, $content);

        return $this;
    }

    public function wrapWith(Element $element): static
    {
        if ($element === $this) {
            throw new \LogicException('HTML graph nodes cannot wrap themselves.');
        }

        $this->assertMutableSibling();
        $this->replaceWith($element);
        $element->append($this);

        return $this;
    }

    public function remove(): static
    {
        $this->assertMutableSibling();
        $this->parent()->removeChild($this);

        return $this;
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    abstract protected function render(): string;

    protected function siblingAt(int $direction): ?Node
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

    protected function siblingHandle(?int $sibling): ?Node
    {
        if ($sibling === null) {
            return null;
        }

        return $this->document->handle($sibling);
    }

    protected function assertMutableSibling(): void
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

    protected function touchDocument(): void
    {
        $this->touchArenaNode($this->arenaId);
    }

    protected function touchArenaNode(?int $arenaId): void
    {
        if ($arenaId !== null && $this->document->arena()->parent($arenaId) !== null) {
            $this->document->markDirty();
        }
    }
}
