<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

use Statamic\View\Antlers\Language\Nodes\AntlersNode as LanguageAntlersNode;

/** @internal */
class AntlersNode extends Node
{
    protected LanguageAntlersNode $node;

    protected string $source;

    public function __construct(Document $document, LanguageAntlersNode $node, string $source)
    {
        parent::__construct($document);

        $this->node = $node;
        $this->source = $source;
    }

    public static function fromArena(Document $document, int $id): self
    {
        $arena = $document->arena();
        $node = new self($document, $arena->antlersNode[$id], $arena->value[$id]);
        $node->arenaId = $id;

        return $node;
    }

    public function antlersNode(): LanguageAntlersNode
    {
        return $this->node;
    }

    public function source(?string $source = null): string|static
    {
        if (func_num_args() === 0) {
            return $this->source;
        }

        if (isset($this->document->arena()->embeddedOwner[$this->arenaId])) {
            throw new \LogicException('Antlers regions embedded in HTML markup are printed by their owning node and cannot be replaced independently.');
        }

        $parent = $this->parent();

        if ($parent !== null && ! $parent->hasChild($this)) {
            throw new \LogicException('Antlers regions embedded in HTML markup are printed by their owning node and cannot be replaced independently.');
        }

        $this->source = (string) $source;

        if ($this->arenaId !== null) {
            $this->document->arena()->value[$this->arenaId] = $this->source;
            unset($this->document->arena()->sourceStart[$this->arenaId]);
        }
        $this->touchDocument();

        return $this;
    }

    protected function render(): string
    {
        if ($this->arenaId === null) {
            return $this->source;
        }

        return $this->document->valueMarkup($this->arenaId);
    }
}
