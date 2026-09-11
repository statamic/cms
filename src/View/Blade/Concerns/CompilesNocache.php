<?php

namespace Statamic\View\Blade\Concerns;

use Statamic\Facades\File;
use Statamic\View\Blade\StatamicTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;

trait CompilesNocache
{
    protected function compileNocache(ComponentNode $component): string
    {
        $compiled = (new StatamicTagCompiler())->compile($component->innerDocumentContent);
        $viewName = '_nocache'.sha1($compiled);
        $path = storage_path('statamic/tmp/nocache/'.$viewName.'.blade.php');
        File::put($path, $compiled);

        return '@nocache(\'compiled__views::'.$viewName.'\')';
    }
}
