<?php

namespace Statamic\View\Blade\Concerns;

use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\LiteralNode;

trait CompilesComponents
{
    protected function isNoResultTag(ComponentNode $node): bool
    {
        return $node->tagName == 'no_results';
    }

    protected function extractNoResults(ComponentNode $componentNode): array
    {
        $newContent = '';
        $noResult = null;

        foreach ($componentNode->getRootNodes() as $node) {
            if (
                $node instanceof ComponentNode &&
                $node->parent === $componentNode &&
                $this->isNoResultTag($node)

            ) {
                $noResult = $node;

                continue;
            }

            if ($node instanceof ComponentNode) {
                $newContent .= $this->getComponentContent($node);
            } elseif ($node instanceof LiteralNode) {
                $newContent .= $node->unescapedContent;
            }
        }

        return [$newContent, $noResult];
    }

    protected function compileComponent(ComponentNode $component): string
    {
        [$compilerContent, $noResult] = $this->extractNoResults($component);

        $compiledNoResult = '';

        if ($noResult) {
            $compiledNoResult = $this->compile($noResult->innerDocumentContent);
        }

        $componentTemplate = <<<'PHP'
<?php

$__statamicResultVarSuffixTagContent = <<<'CONTENTVarSuffix'
#compiledEncoded#
CONTENTVarSuffix;

$__statamicBladeHostVarSuffix = new \Statamic\View\Blade\BladeTagHost(get_defined_vars());
$__statamicBladeHostVarSuffix->setContent(base64_decode($__statamicResultVarSuffixTagContent));
$__statamicBladeHostVarSuffix->setTag(
    app(\Statamic\Tags\Loader::class)->load('$tagName', [
        'parser' => null,
        'params' => $params,
        'content' => '',
        'context' => [],
        'tag' => '$fullTagName',
        'tag_method' => $originalMethod,
    ]),
    $tagMethod
)->setIsPair($isPair)->setParams($params);

/** Allows for navs to override values for recursive children. */
if (isset($__statamicOverrideTagResultValue)) {
   $__statamicBladeHostVarSuffix->setValue($__statamicOverrideTagResultValue);
   unset($__statamicOverrideTagResultValue);
} else {
    $__statamicBladeHostVarSuffix->render();
}
unset($__statamicResultVarSuffixTagContent);
#prepend#
if ($__statamicBladeHostVarSuffix->isAssociativeArray()) {
    /** Create variables from the array values. */
    foreach ($__statamicBladeHostVarSuffix->getValue() as $__key => $__value) {
        $$__key = $__value;
    }
    unset($__value);
} elseif ($__statamicBladeHostVarSuffix->isArray()) {
    $__currentLoopData = $__statamicBladeHostVarSuffix->getValue();

    if ($__statamicBladeHostVarSuffix->isEmpty()) {
    ?>#compiledEmpty#<?php } else {
        $__env->addLoop($__currentLoopData);

        /** Iterate the tag's results */
        foreach ($__currentLoopData as $__statamicLoopValueVarSuffix) {
            $__env->incrementLoopIndices();
            /** Make $loop variable available to the user. */
            $loop = $__env->getLastLoop();
            /** Make a copy of the variables we want to restore. */
            $__statamicStachedVarsVarSuffix = get_defined_vars();
            $__restoreLoopVariablesVarSuffix = $__statamicBladeHostVarSuffix->getDefaultProtectedVariables();
            
            if ($__statamicBladeHostVarSuffix->hasScope()) {
                ${$__statamicBladeHostVarSuffix->getScopeName()} = $__statamicLoopValueVarSuffix;
            } else {
                $__statamicLoopValueVarSuffix = \Statamic\View\Blade\BladeTagHost::adjustBladeValue($__statamicLoopValueVarSuffix);
            
                if (is_array($__statamicLoopValueVarSuffix) && \Illuminate\Support\Arr::isAssoc($__statamicLoopValueVarSuffix)) {
                    $__restoreLoopVariablesVarSuffix = array_merge($__restoreLoopVariablesVarSuffix, array_keys($__statamicLoopValueVarSuffix));
                    
                    foreach ($__statamicLoopValueVarSuffix as $__key => $__value) {
                        $$__key = $__value;
                    }
                    unset($__value);
                }
            }
            
            /** The inner compiled content. */
            ?>#compiled#<?php
            
            if ($__statamicBladeHostVarSuffix->hasScope()) {
                unset(${$__statamicBladeHostVarSuffix->getScopeName()});
            }
            
            /** Restore variables that may have been overwritten. */
            foreach ($__restoreLoopVariablesVarSuffix as $__key) {
                if (isset($__statamicStachedVarsVarSuffix[$__key])) {
                    $$__key = $__statamicStachedVarsVarSuffix[$__key];
                } else {
                    unset($__key);
                }
            }
            
            /** Cleanup loop values. */
            unset(
                $__value,
                $__key,
                $__statamicStachedVarsVarSuffix,
                $__restoreLoopVariablesVarSuffix,
                $__statamicLoopValueVarSuffix
            );
        }
    
        $__env->popLoop();
        $loop = $__env->getLastLoop();
    }
} elseif ($__statamicBladeHostVarSuffix->canRenderAsString()) {
    echo $__statamicBladeHostVarSuffix->renderString();
}

if ($__statamicBladeHostVarSuffix->shouldRenderCompiledContent()):
?>#compiled#<?php
endif;

foreach ($__statamicBladeHostVarSuffix->getProtectedVariables() as $__key) {
    if ($__statamicBladeHostVarSuffix->hasProtectedVar($__key)) {
        $$__key = $__statamicBladeHostVarSuffix->getProtectedVar($__key);    
    } else {
        unset($__key);
    }
}
#append#
unset(
    $__key,
    $__statamicBladeHostVarSuffix
);
?>
PHP;

        $compiledNested = '';

        if ($this->isPairedComponent($component)) {
            $compiledNested = $this->compile($compilerContent);
        }

        return $this->compileTemplate(
            $component,
            $componentTemplate,
            $compiledNested,
            additional: [
                '#compiledEmpty#' => $compiledNoResult,
            ]
        );
    }
}
