<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Parser\DocumentParser;

class ComponentCompiler
{
    protected array $statamicTags = ['statamic', 's'];

    public function compile($template)
    {
        if (! Str::contains($template, ['<s-', '<s:', '<statamic-', '<statamic:'])) {
            return $template;
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
            return "{{ %$component->innerContent /}}";
        }

        $innerContent = $this->compileNodes($component->getNodes());

        return "{{ %$component->innerContent }}$innerContent{{ /%$component->innerContent }}";
    }
}
