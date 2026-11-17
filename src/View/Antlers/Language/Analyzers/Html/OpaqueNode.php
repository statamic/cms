<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
class OpaqueNode extends Node
{
    protected string $source;

    protected string $type;

    public function __construct(Document $document, string $source, string $type = 'markup')
    {
        parent::__construct($document);

        $this->source = $source;
        $this->type = $type;
    }

    public static function fromArena(Document $document, int $id): self
    {
        $arena = $document->arena();
        $node = new self($document, $arena->value[$id], $arena->opaqueType[$id]);
        $node->arenaId = $id;

        return $node;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function source(?string $source = null): string|static
    {
        if (func_num_args() === 0) {
            return $this->source;
        }

        $this->source = (string) $source;

        if ($this->arenaId !== null) {
            $this->document->arena()->value[$this->arenaId] = $this->source;
            unset($this->document->arena()->sourceStart[$this->arenaId]);
        }
        $this->touchDocument();

        return $this;
    }

    public function hasChild(Node $node): bool
    {
        return in_array($node->arenaId(), $this->document->arena()->embedded[$this->arenaId] ?? [], true);
    }

    protected function render(): string
    {
        if ($this->arenaId === null) {
            return $this->source;
        }

        return $this->document->valueMarkup($this->arenaId);
    }
}
