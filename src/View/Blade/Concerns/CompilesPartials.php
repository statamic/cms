<?php

namespace Statamic\View\Blade\Concerns;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Statamic\Tags\IncludeTag;
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

    protected function compileSlotOutput(ComponentNode $component): string
    {
        if (! $this->isValidSlotName($name = $this->rawSlotName($component))) {
            return $this->compileComponent($component);
        }

        $slot = $name === 'slot'
            ? '($slot ?? null)'
            : '($'.IncludeTag::SLOTS_KEY.'['.var_export($name, true).'] ?? null)';

        $context = '$'.IncludeTag::CONTEXT_KEY.' ?? false';
        $output = '\Statamic\View\Slot::output('.$slot.', '.$this->compileParameters($component->parameters).')';

        $fallback = $this->isPairedComponent($component)
            ? $this->compile($component->innerDocumentContent)
            : '';

        return '<?php if ('.$context.') { if ('.$slot.' !== null) { echo '.$output.'; } else { ?>'.$fallback.'<?php } } else { ?>'.$this->compileComponent($component).'<?php } ?>';
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

    protected function compileIncludeSlot(ComponentNode $node): array
    {
        $name = $this->rawSlotName($node);

        if (! $this->isValidSlotName($name)) {
            throw new InvalidArgumentException("Invalid slot name [{$name}].");
        }

        return [$name, $this->compile($node->innerDocumentContent)];
    }

    protected function rawSlotName(ComponentNode $component): string
    {
        $name = (string) str($component->name)->substr(5);

        return $name === '' ? 'slot' : $name;
    }

    protected function isValidSlotName(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
    }

    protected function compilePartial(ComponentNode $component): string
    {
        return $this->compileViewTag($component, isInclude: false);
    }

    protected function compileInclude(ComponentNode $component): string
    {
        return $this->compileViewTag($component, isInclude: true);
    }

    protected function compileViewTag(ComponentNode $component, bool $isInclude): string
    {
        [$slots, $newContent] = $this->extractSlots($component);
        $params = $component->getParameters()->keyBy(fn (ParameterNode $param) => $param->materializedName);
        $forwardMethods = ['exists', 'if_exists'];

        [$baseName, $method, $originalMethod] = $this->extractMethodNames($component);
        $baseName = Str::lower($baseName);

        if (str_contains($component->tagName, ':') && ! in_array($originalMethod, $forwardMethods)) {
            $srcParam = new ParameterNode();
            $srcParam->type = ParameterType::Parameter;
            $srcParam->setName('src');
            $srcParam->setValue($originalMethod);
            $params['src'] = $srcParam;
        }

        $hoistedSet = '';
        $hoistedUnset = '';
        $compiledSlots = array_map(
            fn ($slot) => $isInclude ? $this->compileIncludeSlot($slot) : $this->compileSlot($slot),
            $slots
        );

        if ($isInclude && Str::snake($method) !== 'exists') {
            if (trim($newContent) !== '') {
                $compiledSlots[] = ['slot', $this->compile($newContent)];
            }

            $newContent = '';
        }

        if ($isInclude) {
            $seen = [];

            foreach ($compiledSlots as [$name]) {
                if (isset($seen[$name])) {
                    throw new InvalidArgumentException("The include tag cannot define the [{$name}] slot more than once.");
                }

                $seen[$name] = true;
            }
        }

        // The label is randomized so slot content containing the terminator cannot break out of the nowdoc.
        $set = <<<'SET'
$$varName = <<<'$label'
#compiled#
$label;
SET;
        $unset = <<<'UNSET'
unset($$varName);
UNSET;

        foreach ($compiledSlots as [$name, $compiled]) {
            $hoistedVarName = '__partialSlot'.Str::random(32);
            $hoistedLabel = 'COMPILED'.Str::random(32);
            $injectedParam = new ParameterNode();
            $paramName = $isInclude ? IncludeTag::SLOT_PARAM_PREFIX.$name : $name;
            $injectedParam->setName($paramName);
            $injectedParam->type = ParameterType::DynamicVariable;

            $injectedParam->value = $isInclude
                ? '\Statamic\View\Slot::forBlade($'.$hoistedVarName.', get_defined_vars())'
                : 'new \Illuminate\Support\HtmlString(\Illuminate\Support\Facades\Blade::render($'.$hoistedVarName.', get_defined_vars()))';

            $hoistedSet .= Str::swap([
                '$varName' => $hoistedVarName,
                '$label' => $hoistedLabel,
                '#compiled#' => $compiled,
            ], $set);

            $hoistedUnset .= Str::swap([
                '$varName' => $hoistedVarName,
            ], $unset);

            $params[$paramName] = $injectedParam;
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
                '$tagName' => $baseName,
                '$originalMethod' => "'".$originalMethod."'",
            ]
        );
    }
}
