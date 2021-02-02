<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Support\Facades\Blade;

class ControlPanel
{
    public function handle($request, Closure $next)
    {
        Blade::directive('statamic_svg', function ($expression) {
            return "<?php echo Statamic::svg({$expression}) ?>";
        });

        return $next($request);
    }
}
