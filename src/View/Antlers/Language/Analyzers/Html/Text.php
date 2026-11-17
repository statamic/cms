<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
class Text extends Node
{
    protected string $content;

    public function __construct(Document $document, string $content)
    {
        parent::__construct($document);

        $this->content = $content;
    }

    public static function fromArena(Document $document, int $id): self
    {
        $node = new self($document, $document->arena()->value[$id]);
        $node->arenaId = $id;

        return $node;
    }

    public function content(?string $content = null): string|static
    {
        if (func_num_args() === 0) {
            return $this->content;
        }

        $this->content = (string) $content;

        if ($this->arenaId !== null) {
            $this->document->arena()->value[$this->arenaId] = $this->content;
            unset($this->document->arena()->sourceStart[$this->arenaId]);
        }
        $this->touchDocument();

        return $this;
    }

    protected function render(): string
    {
        if ($this->arenaId === null) {
            return $this->content;
        }

        return $this->document->valueMarkup($this->arenaId);
    }
}
