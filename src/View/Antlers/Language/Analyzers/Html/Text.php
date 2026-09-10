<?php

namespace Statamic\View\Antlers\Language\Analyzers\Html;

/** @internal */
class Text extends Node
{
    protected $content;

    public function __construct(Document $document, $content)
    {
        parent::__construct($document);

        $this->content = $content;
    }

    public static function fromArena(Document $document, $id)
    {
        $node = new self($document, $document->arena()->value[$id]);
        $node->arenaId = $id;

        return $node;
    }

    public function content($content = null)
    {
        if (func_num_args() === 0) {
            return $this->content;
        }

        $this->content = (string) $content;

        if ($this->arenaId !== null) {
            $this->document->arena()->value[$this->arenaId] = $this->content;
        }
        $this->touchDocument();

        return $this;
    }

    protected function render()
    {
        return $this->content;
    }
}
