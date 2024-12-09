<?php

namespace Statamic\View\Blade\Concerns;

use Statamic\View\Blade\StatamicTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;

trait CompilesNocache
{
    protected function compileNocache(ComponentNode $component): string
    {
        $compiled = (new StatamicTagCompiler())->compile($component->innerDocumentContent);
        $viewName = '_nocache'.sha1($compiled);
        $path = storage_path('framework/views/'.$viewName.'.blade.php');
        file_put_contents($path, $compiled);

        return '@nocache(\'compiled__views::'.$viewName.'\')';
    }
}
