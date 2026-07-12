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
            ->setInterceptNav(false)
            ->compile($component->outerDocumentContent);

        return <<<PHP
<?php \$___statamicNavCallback = function (\$___scope, \$___statamicNavCallback) {
    extract(\$___scope);
    ob_start();
?>$compiled<?php
    return ob_get_clean();
};

echo \$___statamicNavCallback(get_defined_vars(), \$___statamicNavCallback);
unset(\$___statamicNavCallback);
?>
PHP;
    }
}
