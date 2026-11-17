<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
class SourceAnchor extends Node
{
    private function __construct(Document $document, int $arenaId)
    {
        parent::__construct($document, $arenaId);
    }

    public static function fromArena(Document $document, int $id): self
    {
        return new self($document, $id);
    }

    protected function render(): string
    {
        return $this->document->renderArenaNode($this->arenaId);
    }
}
