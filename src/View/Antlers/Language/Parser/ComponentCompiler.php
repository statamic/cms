<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Parser\Concerns\CompilesBladeComponents;
use Statamic\View\Instrumentation\Antlers\ComponentSourceMap;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Parser\DocumentParser;

class ComponentCompiler
{
    use CompilesBladeComponents;

    protected array $statamicTags = ['statamic', 's', 'flux'];

    protected ?ComponentSourceMap $sourceMap = null;

    public function sourceMap(): ?ComponentSourceMap
    {
        return $this->sourceMap;
    }

    public function compile($template, bool $mapSource = false)
    {
        $this->sourceMap = null;
        if (! Str::contains($template, ['<s-', '<s:', '<statamic-', '<statamic:', '<x:', '<x-'])) {
            return $template;
        }

        if ($mapSource) {
            $this->sourceMap = new ComponentSourceMap($template);
        }

        return (new DocumentParser())
            ->registerCustomComponentTags($this->statamicTags)
            ->onlyParseComponents()
            ->parseTemplate($template)
            ->toDocument()
            ->getRootNodes()
            ->pipe(fn ($nodes) => $this->compileNodes($nodes));
    }

    protected function compileNodes($nodes)
    {
        return $nodes
            ->map(function ($node) {
                if (! $node instanceof ComponentNode) {
                    return $node->unescapedContent;
                }

                if ($node->componentPrefix === 'x') {
                    return $this->compileBladeComponent($node);
                }

                if (! in_array(mb_strtolower($node->componentPrefix), $this->statamicTags)) {
                    return $node->outerDocumentContent;
                }

                if ($node->isClosingTag && ! $node->isSelfClosing) {
                    return '';
                }

                return $this->compileComponent($node);
            })
            ->join('');
    }

    protected function compileComponent(ComponentNode $component)
    {
        if ($component->isSelfClosing) {
            return $this->mapComponentMarkup($component, "{{ %$component->innerContent /}}");
        }

        $innerContent = $this->compileNodes($component->getNodes());

        return $this->mapComponentMarkup($component, "{{ %$component->innerContent }}")
            .$innerContent
            .$this->mapComponentMarkup($component->isClosedBy, "{{ /%$component->innerContent }}");
    }

    protected function mapComponentMarkup(?ComponentNode $node, string $compiled): string
    {
        if ($node?->position !== null) {
            $this->sourceMap?->replace($node->position->startOffset, $node->position->endOffset + 1, $compiled);
        }

        return $compiled;
    }
}
