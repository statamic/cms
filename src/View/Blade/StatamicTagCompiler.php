<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Str;
use Statamic\View\Blade\Concerns\CompilesComponents;
use Statamic\View\Blade\Concerns\CompilesNavs;
use Statamic\View\Blade\Concerns\CompilesNocache;
use Statamic\View\Blade\Concerns\CompilesPartials;
use Stillat\BladeParser\Compiler\CompilerServices\AttributeCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Parser\DocumentParser;

class StatamicTagCompiler
{
    use CompilesComponents,
        CompilesNavs,
        CompilesNocache,
        CompilesPartials;

    protected AttributeCompiler $attributeCompiler;

    protected array $statamicTags = ['statamic', 's'];
    protected string $prependCompiledContent = '';
    protected string $appendCompiledContent = '';
    protected bool $interceptNav = true;

    public function __construct()
    {
        $this->attributeCompiler = (new AttributeCompiler())
            ->prefixEscapedParametersWith('attr:')
            ->wrapResultIn(['as', 'scope'], function ($value) {
                return "\\Statamic\\View\\Blade\\StatamicTagCompiler::adjustDynamicVariableName($value)";
            });
    }

    protected function getComponentContent(ComponentNode $node): string
    {
        if ($node->isClosedBy === null || $node->isSelfClosing) {
            return $node->content;
        }

        return $node->outerDocumentContent;
    }

    public static function adjustDynamicVariableName(string $variableName): string
    {
        return ltrim($variableName, '$');
    }

    protected function compileParameters(array $params): string
    {
        return '\Statamic\View\Blade\BladeTagHost::filterParams('.$this->attributeCompiler->compile($params).')';
    }

    public function prependCompiledContent(string $content): static
    {
        $this->prependCompiledContent = $content;

        return $this;
    }

    public function appendCompiledContent(string $content): static
    {
        $this->appendCompiledContent = $content;

        return $this;
    }

    public function setInterceptNav(bool $interceptNav): static
    {
        $this->interceptNav = $interceptNav;

        return $this;
    }

    public function compile(string $template): string
    {
        if (! Str::contains($template, ['<statamic:', '<statamic-', '<s:', '<s-'])) {
            return $template;
        }

        return (new DocumentParser())
            ->registerCustomComponentTags($this->statamicTags)
            ->onlyParseComponents()
            ->parseTemplate($template)
            ->toDocument()
            ->getRootNodes()
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

                if ($node->tagName === 'nocache') {
                    return $this->compileNocache($node);
                } elseif ($this->isPartial($node)) {
                    return $this->compilePartial($node);
                } elseif ($this->interceptNav && $this->isStructure($node->tagName)) {
                    return $this->compileNav($node);
                }

                return $this->compileComponent($node);
            })->join('');
    }

    protected function isStructure(string $tagName): bool
    {
        $tagName = (string) str($tagName)->before(':')->lower();

        return in_array($tagName, ['nav', 'structure', 'children']);
    }

    protected function isPartial(ComponentNode $component): bool
    {
        return $component->tagName == 'partial' || str($component->tagName)->lower()->startsWith('partial:');
    }

    protected function extractMethodNames(ComponentNode $component): array
    {
        $name = $component->tagName;

        if ($pos = strpos($name, ':')) {
            $originalMethod = substr($name, $pos + 1);
            $method = Str::camel($originalMethod);
            $name = substr($name, 0, $pos);
        } else {
            $method = $originalMethod = 'index';
        }

        return [$name, $method, $originalMethod];
    }

    protected function isPairedComponent(ComponentNode $component): bool
    {
        return $component->isClosedBy != null && ! $component->isSelfClosing;
    }

    protected function compileTemplate(ComponentNode $component, string $template, string $nestedContent, ?array $params = null, array $additional = []): string
    {
        if ($params === null) {
            $params = $component->parameters;
        }

        [$name, $method, $originalMethod] = $this->extractMethodNames($component);

        $isPair = 'false';

        if ($this->isPairedComponent($component)) {
            $isPair = 'true';
        }

        return (string) str($template)
            ->swap(array_merge([
                '$tagName' => $name,
                '$fullTagName' => $component->tagName,
                '$tagMethod' => "'".$method."'",
                '$originalMethod' => "'".$originalMethod."'",
                '$params' => $this->compileParameters($params),
                '$isPair' => $isPair,
                '#compiled#' => $nestedContent,
                '#compiledEncoded#' => base64_encode($nestedContent),
                'VarSuffix' => Str::random(32),
                '#prepend#' => $this->prependCompiledContent,
                '#append#' => $this->appendCompiledContent,
            ], $additional));
    }
}
