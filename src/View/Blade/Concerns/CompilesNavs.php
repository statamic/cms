<?php

namespace Statamic\View\Blade\Concerns;

use Statamic\View\Blade\StatamicTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;

trait CompilesNavs
{
    protected function compileNav(ComponentNode $component): string
    {
        $viewName = '___nav'.sha1($component->outerDocumentContent);

        $compiled = (new StatamicTagCompiler())
            ->prependCompiledContent('$__currentStatamicNavView = \''.$viewName.'\';')
            ->appendCompiledContent('unset($__currentStatamicNavView);')
            ->setInterceptNav(false)
            ->compile($component->outerDocumentContent);

        file_put_contents(storage_path('framework/views/'.$viewName.'.blade.php'), $compiled);

        return '@include(\'compiled__views::'.$viewName.'\', get_defined_vars())';
    }
}
