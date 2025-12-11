<?php

namespace Statamic\View\Blade\Concerns;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;
use Stillat\BladeParser\Nodes\LiteralNode;

trait CompilesPartials
{
    protected function isSlotTag(string $tagName): bool
    {
        $tagName = mb_strtolower($tagName);

        return $tagName === 'slot' || str($tagName)->startsWith(['slot.', 'slot:']);
    }

    protected function isComponentSlot(ComponentNode $parent, ComponentNode $child): bool
    {
        return $child->parent === $parent && $this->isSlotTag($child->tagName);
    }

    protected function extractSlots(ComponentNode $componentNode): array
    {
        $slots = [];
        $newContent = '';

        foreach ($componentNode->getRootNodes() as $node) {
            if ($node instanceof ComponentNode && $this->isComponentSlot($componentNode, $node)) {
                $slots[] = $node;

                continue;
            }

            if ($node instanceof ComponentNode) {
                $newContent .= $this->getComponentContent($node);
            } elseif ($node instanceof LiteralNode) {
                $newContent .= $node->unescapedContent;
            }
        }

        return [$slots, $newContent];
    }

    protected function compileSlot(ComponentNode $node): array
    {
        $name = (string) str($node->name)->substr(5);
        $compiled = $this->compile($node->innerDocumentContent);

        return [$name, $compiled];
    }

    protected function compilePartial(ComponentNode $component): string
    {
        [$slots, $newContent] = $this->extractSlots($component);
        $params = $component->getParameters()->keyBy(fn (ParameterNode $param) => $param->materializedName);
        $forwardMethods = ['exists', 'if_exists'];

        if (str($component->tagName)->startsWith('partial:')) {
            $partialName = (string) str($component->tagName)->substr(8);

            if (! in_array($partialName, $forwardMethods)) {
                $srcParam = new ParameterNode();
                $srcParam->type = ParameterType::Parameter;
                $srcParam->setName('src');
                $srcParam->setValue($partialName);
                $params['src'] = $srcParam;
            }
        }

        $hoistedSet = '';
        $hoistedUnset = '';

        $set = <<<'SET'
$$varName = <<<'COMPILED'
#compiled#
COMPILED;
SET;
        $unset = <<<'UNSET'
unset($$varName);
UNSET;

        foreach ($slots as $slot) {
            $hoistedVarName = '__partialSlot'.Str::random(32);
            [$name, $compiled] = $this->compileSlot($slot);
            $injectedParam = new ParameterNode();
            $injectedParam->setName($name);
            $injectedParam->type = ParameterType::DynamicVariable;

            $injectedParam->value = 'new \Illuminate\Support\HtmlString(\Illuminate\Support\Facades\Blade::render($'.$hoistedVarName.', get_defined_vars()))';

            $hoistedSet .= Str::swap([
                '$varName' => $hoistedVarName,
                '#compiled#' => $compiled,
            ], $set);

            $hoistedUnset .= Str::swap([
                '$varName' => $hoistedVarName,
            ], $unset);

            $params[$name] = $injectedParam;
        }

        $compiledNode = <<<'PHP'
<?php
$__statamicResultVarSuffixTagContent = <<<'CONTENT'
#compiledEncoded#
CONTENT;
#set#
$__statamicResultVarSuffix = (new \Statamic\View\Blade\BladeTagHost(get_defined_vars()))->setTag(
    app(\Statamic\Tags\Loader::class)
        ->load('$tagName', [
            'parser' => null,
            'params' => $params,
            'content' => '',
            'context' => [],
            'tag' => '$fullTagName',
            'tag_method' => $originalMethod,
        ]), $tagMethod)->setIsPair($isPair)->setContent(base64_decode($__statamicResultVarSuffixTagContent))->render();

if (is_string($__statamicResultVarSuffix)) {
    echo (string) $__statamicResultVarSuffix;
}

if (is_bool($__statamicResultVarSuffix) && $__statamicResultVarSuffix === true):?>#compiled#<?php endif;

unset(
    $__statamicResultVarSuffix,
    $__statamicResultVarSuffixTagContent
);
#unset#
?>
PHP;

        [$name, $method, $originalMethod] = $this->extractMethodNames($component);

        if (! in_array(Str::snake($method), $forwardMethods)) {
            $method = $originalMethod = 'index';
        }

        return $this->compileTemplate(
            $component,
            $compiledNode,
            $newContent,
            $params->toArray(),
            [
                '#set#' => $hoistedSet,
                '#unset#' => $hoistedUnset,
                '$tagMethod' => "'".$method."'",
                '$tagName' => 'partial',
                '$originalMethod' => "'".$originalMethod."'",
            ]
        );
    }
}
