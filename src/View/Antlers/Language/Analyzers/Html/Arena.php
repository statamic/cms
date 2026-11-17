<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;

/** @internal */
class Arena
{
    const DOCUMENT = 0;

    const ELEMENT = 1;

    const TEXT = 2;

    const ANTLERS = 3;

    const OPAQUE = 4;

    const SOURCE_ANCHOR = 5;

    const KIND_MASK = 7;

    const SELF_CLOSING = 8;

    const SYNTHETIC = 16;

    const SVG = 32;

    const MATHML = 64;

    /** @var int[] */
    public array $kinds = [self::DOCUMENT];

    /** @var array<int, int|null> */
    public array $parents = [null];

    /** @var array<int, int|null> */
    public array $firstChildren = [null];

    /** @var array<int, int|null> */
    public array $lastChildren = [null];

    /** @var array<int, int|null> */
    public array $previousSiblings = [null];

    /** @var array<int, int|null> */
    public array $nextSiblings = [null];

    /** @var array<int, string|null> */
    public array $name = [0 => null];

    /** @var string[] */
    public array $value = [''];

    /** @var array<int, string> */
    public array $closing = [0 => ''];

    /** @var array<int, string> */
    public array $opaqueType = [];

    /** @var array<int, LanguageAntlersNode> */
    public array $antlersNode = [];

    /** @var array<int, int[]> */
    public array $embedded = [];

    /** @var array<int, int> */
    public array $embeddedOwner = [];

    /** @var array<int, int> */
    public array $sourceAnchor = [];

    /** @var array<int, int|null> */
    public array $anchorTarget = [];

    /** @var array<int, int> */
    public array $sourceStart = [];

    /** @var array<int, int> */
    public array $closingStart = [];

    public function allocate(int $kind): int
    {
        $id = count($this->kinds);
        $this->kinds[] = $kind;
        $this->parents[] = null;
        $this->firstChildren[] = null;
        $this->lastChildren[] = null;
        $this->previousSiblings[] = null;
        $this->nextSiblings[] = null;
        $this->value[] = '';

        return $id;
    }

    public function element(?string $name, string $opening, bool $selfClosing = false, string $namespace = 'html', bool $synthetic = false): int
    {
        $flags = self::ELEMENT;

        if ($selfClosing) {
            $flags |= self::SELF_CLOSING;
        }

        if ($synthetic) {
            $flags |= self::SYNTHETIC;
        }

        if ($namespace === 'svg') {
            $flags |= self::SVG;
        }

        if ($namespace === 'mathml') {
            $flags |= self::MATHML;
        }

        $id = $this->allocate($flags);
        $this->name[$id] = $name;
        $this->value[$id] = $opening;
        $this->closing[$id] = '';

        return $id;
    }

    public function text(string $value, ?int $sourceStart = null): int
    {
        $id = count($this->kinds);
        $this->kinds[] = self::TEXT;
        $this->parents[] = null;
        $this->firstChildren[] = null;
        $this->lastChildren[] = null;
        $this->previousSiblings[] = null;
        $this->nextSiblings[] = null;
        $this->value[] = $value;

        if ($sourceStart !== null) {
            $this->sourceStart[$id] = $sourceStart;
        }

        return $id;
    }

    public function antlers(LanguageAntlersNode $node, string $source): int
    {
        $id = $this->allocate(self::ANTLERS);
        $this->antlersNode[$id] = $node;
        $this->value[$id] = $source;

        return $id;
    }

    public function opaque(string $source, string $type): int
    {
        $id = $this->allocate(self::OPAQUE);
        $this->value[$id] = $source;
        $this->opaqueType[$id] = $type;

        return $id;
    }

    public function anchor(int $target): int
    {
        $id = $this->allocate(self::SOURCE_ANCHOR);
        $this->anchorTarget[$id] = $target;
        $this->sourceAnchor[$target] = $id;

        return $id;
    }

    public function kind(int $id): int
    {
        return $this->kinds[$id];
    }

    public function parent(int $id): ?int
    {
        return $this->parents[$id];
    }

    public function setParent(int $id, ?int $parent): void
    {
        $this->parents[$id] = $parent;
    }

    public function firstChild(int $id): ?int
    {
        return $this->firstChildren[$id];
    }

    public function lastChild(int $id): ?int
    {
        return $this->lastChildren[$id];
    }

    public function previous(int $id): ?int
    {
        return $this->previousSiblings[$id];
    }

    public function next(int $id): ?int
    {
        return $this->nextSiblings[$id];
    }

    public function anchorExisting(int $target): int
    {
        $parent = $this->parent($target);

        if ($parent === null) {
            return $this->anchor($target);
        }

        $previous = $this->previous($target);
        $next = $this->next($target);
        $anchor = $this->anchor($target);
        $this->setParent($anchor, $parent);
        $this->setPrevious($anchor, $previous);
        $this->setNext($anchor, $next);

        if ($previous === null) {
            $this->setFirstChild($parent, $anchor);
        } else {
            $this->setNext($previous, $anchor);
        }

        if ($next === null) {
            $this->setLastChild($parent, $anchor);
        } else {
            $this->setPrevious($next, $anchor);
        }

        $this->setParent($target, null);
        $this->previousSiblings[$target] = null;
        $this->nextSiblings[$target] = null;

        return $anchor;
    }

    public function append(int $parent, int $node): int
    {
        $last = $this->lastChildren[$parent];
        $this->parents[$node] = $parent;
        $this->previousSiblings[$node] = $last;
        $this->nextSiblings[$node] = null;

        if ($last === null) {
            $this->firstChildren[$parent] = $node;
        } else {
            $this->nextSiblings[$last] = $node;
        }

        $this->lastChildren[$parent] = $node;

        return $node;
    }

    public function insertBefore(int $reference, int $node): int
    {
        $parent = $this->parent($reference);
        $previous = $this->previous($reference);
        $this->setParent($node, $parent);
        $this->previousSiblings[$node] = $previous;
        $this->nextSiblings[$node] = $reference;
        $this->setPrevious($reference, $node);

        if ($previous === null) {
            $this->setFirstChild($parent, $node);
        } else {
            $this->setNext($previous, $node);
        }

        return $node;
    }

    public function insertAfter(int $reference, int $node): int
    {
        $parent = $this->parent($reference);
        $next = $this->next($reference);
        $this->setParent($node, $parent);
        $this->previousSiblings[$node] = $reference;
        $this->nextSiblings[$node] = $next;
        $this->setNext($reference, $node);

        if ($next === null) {
            $this->setLastChild($parent, $node);
        } else {
            $this->setPrevious($next, $node);
        }

        return $node;
    }

    public function detach(int $node): int
    {
        $parent = $this->parent($node);

        if ($parent === null) {
            return $node;
        }

        $previous = $this->previous($node);
        $next = $this->next($node);

        if ($previous === null) {
            $this->setFirstChild($parent, $next);
        } else {
            $this->setNext($previous, $next);
        }

        if ($next === null) {
            $this->setLastChild($parent, $previous);
        } else {
            $this->setPrevious($next, $previous);
        }

        $this->setParent($node, null);
        $this->previousSiblings[$node] = null;
        $this->nextSiblings[$node] = null;

        return $node;
    }

    public function children(int $parent): array
    {
        $children = [];
        $seen = [];

        for ($child = $this->firstChild($parent); $child !== null; $child = $this->next($child)) {
            if (isset($seen[$child])) {
                throw new \LogicException('Cycle detected in HTML arena siblings for parent '.$parent.'.');
            }

            $seen[$child] = true;
            $children[] = $child;
        }

        return $children;
    }

    public function hasChild(int $parent, int $node): bool
    {
        return $this->parent($node) === $parent && ! isset($this->embeddedOwner[$node]);
    }

    public function namespace(int $id): string
    {
        $type = $this->kind($id);

        if ($type & self::SVG) {
            return 'svg';
        }

        if ($type & self::MATHML) {
            return 'mathml';
        }

        return 'html';
    }

    protected function setFirstChild(int $id, ?int $child): void
    {
        $this->firstChildren[$id] = $child;
    }

    protected function setLastChild(int $id, ?int $child): void
    {
        $this->lastChildren[$id] = $child;
    }

    protected function setPrevious(int $id, ?int $previous): void
    {
        $this->previousSiblings[$id] = $previous;
    }

    protected function setNext(int $id, ?int $next): void
    {
        $this->nextSiblings[$id] = $next;
    }
}
