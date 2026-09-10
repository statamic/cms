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
    public $kinds = [self::DOCUMENT];

    /** @var array<int, int|null> */
    public $parents = [null];

    /** @var array<int, int|null> */
    public $firstChildren = [null];

    /** @var array<int, int|null> */
    public $lastChildren = [null];

    /** @var array<int, int|null> */
    public $previousSiblings = [null];

    /** @var array<int, int|null> */
    public $nextSiblings = [null];

    /** @var array<int, string|null> */
    public $name = [0 => null];

    /** @var string[] */
    public $value = [''];

    /** @var array<int, string> */
    public $closing = [0 => ''];

    /** @var array<int, string> */
    public $opaqueType = [];

    /** @var array<int, LanguageAntlersNode> */
    public $antlersNode = [];

    /** @var array<int, int[]> */
    public $embedded = [];

    /** @var array<int, int> */
    public $embeddedOwner = [];

    /** @var array<int, int> target id => anchor id */
    public $sourceAnchor = [];

    /** @var array<int, int|null> anchor id => target id */
    public $anchorTarget = [];

    public function allocate($kind)
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

    public function element($name, $opening, $selfClosing = false, $namespace = 'html', $synthetic = false)
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

        $id = count($this->kinds);
        $this->kinds[] = $flags;
        $this->parents[] = null;
        $this->firstChildren[] = null;
        $this->lastChildren[] = null;
        $this->previousSiblings[] = null;
        $this->nextSiblings[] = null;
        $this->name[$id] = $name;
        $this->value[] = $opening;
        $this->closing[$id] = '';

        return $id;
    }

    public function text($value)
    {
        $id = count($this->kinds);
        $this->kinds[] = self::TEXT;
        $this->parents[] = null;
        $this->firstChildren[] = null;
        $this->lastChildren[] = null;
        $this->previousSiblings[] = null;
        $this->nextSiblings[] = null;
        $this->value[] = $value;

        return $id;
    }

    public function antlers(LanguageAntlersNode $node, $source)
    {
        $id = $this->allocate(self::ANTLERS);
        $this->antlersNode[$id] = $node;
        $this->value[$id] = $source;

        return $id;
    }

    public function opaque($source, $type)
    {
        $id = $this->allocate(self::OPAQUE);
        $this->value[$id] = $source;
        $this->opaqueType[$id] = $type;

        return $id;
    }

    public function anchor($target)
    {
        $id = $this->allocate(self::SOURCE_ANCHOR);
        $this->anchorTarget[$id] = $target;
        $this->sourceAnchor[$target] = $id;

        return $id;
    }

    public function kind($id)
    {
        return $this->kinds[$id];
    }

    public function parent($id)
    {
        return $this->parents[$id];
    }

    public function setParent($id, $parent)
    {
        $this->parents[$id] = $parent;
    }

    public function firstChild($id)
    {
        return $this->firstChildren[$id];
    }

    public function lastChild($id)
    {
        return $this->lastChildren[$id];
    }

    public function previous($id)
    {
        return $this->previousSiblings[$id];
    }

    public function next($id)
    {
        return $this->nextSiblings[$id];
    }

    public function anchorExisting($target)
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

    public function append($parent, $node)
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

    public function insertBefore($reference, $node)
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

    public function insertAfter($reference, $node)
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

    public function detach($node)
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

    public function children($parent)
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

    public function hasChild($parent, $node)
    {
        return $this->parent($node) === $parent && ! isset($this->embeddedOwner[$node]);
    }

    public function namespace($id)
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

    protected function setFirstChild($id, $child)
    {
        $this->firstChildren[$id] = $child;
    }

    protected function setLastChild($id, $child)
    {
        $this->lastChildren[$id] = $child;
    }

    protected function setPrevious($id, $previous)
    {
        $this->previousSiblings[$id] = $previous;
    }

    protected function setNext($id, $next)
    {
        $this->nextSiblings[$id] = $next;
    }
}
