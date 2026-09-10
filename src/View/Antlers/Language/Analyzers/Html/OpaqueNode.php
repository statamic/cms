<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
class OpaqueNode extends Node
{
    protected $source;

    protected $type;

    public function __construct(Document $document, $source, $type = 'markup')
    {
        parent::__construct($document);

        $this->source = $source;
        $this->type = $type;
    }

    public static function fromArena(Document $document, $id)
    {
        $arena = $document->arena();
        $node = new self($document, $arena->value[$id], $arena->opaqueType[$id]);
        $node->arenaId = $id;

        return $node;
    }

    public function type()
    {
        return $this->type;
    }

    public function source($source = null)
    {
        if (func_num_args() === 0) {
            return $this->source;
        }

        $this->source = (string) $source;

        if ($this->arenaId !== null) {
            $this->document->arena()->value[$this->arenaId] = $this->source;
        }
        $this->touchDocument();

        return $this;
    }

    public function embeddedNodes()
    {
        return array_map([$this->document, 'handle'], $this->document->arena()->embedded[$this->arenaId] ?? []);
    }

    public function siblingsFor(Node $node)
    {
        return $this->embeddedNodes();
    }

    public function hasChild(Node $node)
    {
        return in_array($node->arenaId(), $this->document->arena()->embedded[$this->arenaId] ?? [], true);
    }

    protected function render()
    {
        return $this->source;
    }
}
