<?php

namespace Statamic\View\Blade\Concerns;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Statamic\View\Blade\StatamicTagCompiler;
use Stillat\BladeParser\Nodes\Components\ComponentNode;

trait CompilesNocache
{
    protected function compileNocache(ComponentNode $component): string
    {
        $compiled = (new StatamicTagCompiler())->compile($component->innerDocumentContent);

        // The nocache region is written to its own view file which Blade compiles
        // independently. Any @php or @verbatim blocks have already been swapped
        // for raw placeholders by the outer compiler, so we need to restore them
        // here otherwise they'd be lost by the time that view is compiled.
        $compiled = $this->restoreRawBlocks($compiled);

        $viewName = '_nocache'.sha1($compiled);
        $path = storage_path('framework/views/'.$viewName.'.blade.php');
        file_put_contents($path, $compiled);

        return '@nocache(\'compiled__views::'.$viewName.'\')';
    }

    private function restoreRawBlocks(string $compiled): string
    {
        $compiler = Blade::getFacadeRoot();

        if (! $compiler instanceof BladeCompiler) {
            return $compiled;
        }

        $rawBlocks = \Closure::bind(fn () => $this->rawBlocks, $compiler, BladeCompiler::class)();

        if (empty($rawBlocks)) {
            return $compiled;
        }

        return preg_replace_callback('/@__raw_block_(\d+)__@/', function ($matches) use ($rawBlocks) {
            if (! isset($rawBlocks[$matches[1]])) {
                return $matches[0];
            }

            $raw = $rawBlocks[$matches[1]];

            // @php blocks are stored as raw PHP tags and can be written back as-is.
            if (str_starts_with($raw, '<?php') && str_ends_with($raw, '?>')) {
                return $raw;
            }

            // @verbatim blocks are stored as literal content. Re-wrap them so the
            // separate compile pass on the nocache view leaves them untouched.
            return '@verbatim'.$raw.'@endverbatim';
        }, $compiled);
    }
}
