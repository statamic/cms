<?php

namespace Statamic\View\Antlers\Language\Parser;

use Statamic\View\Antlers\Language\Nodes\AntlersNode;

class DocumentTransformer
{
    protected $parser = null;
    protected $nodes = [];
    protected $extractedAntlers = [];
    protected $buffer = '';

    public function __construct()
    {
        $this->parser = new DocumentParser();
    }

    public function load($text)
    {
        $this->parser->parse($text);
        $this->nodes = $this->parser->getNodes();
        $this->buffer = $this->parser->getParsedContent();

        foreach ($this->nodes as $node) {
            if ($node instanceof AntlersNode) {
                $originalContent = $node->getNodeDocumentText();
                $replaceId = 'ANTLERS:'.$node->refId;
                $this->buffer = str_replace($originalContent, $replaceId, $this->buffer);
                $this->extractedAntlers[$replaceId] = $originalContent;
            }
        }

        return $this;
    }

    public function correct($text)
    {
        $this->parser->parse($text);
        $this->nodes = $this->parser->getNodes();
        $this->buffer = $this->parser->getParsedContent();

        foreach ($this->nodes as $node) {
            if ($node instanceof AntlersNode) {
                $originalContent = $node->getNodeDocumentText();
                $this->buffer = str_replace($originalContent, html_entity_decode($originalContent), $this->buffer);
            }
        }

        return $this->buffer;
    }

    public function transform($callable)
    {
        $this->buffer = $callable($this->buffer);

        return $this;
    }

    public function getTemplate()
    {
        $newTemplate = $this->buffer;

        foreach ($this->extractedAntlers as $antlersId => $originalContent) {
            $newTemplate = str_replace($antlersId, $originalContent, $newTemplate);
        }

        return $newTemplate;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}
