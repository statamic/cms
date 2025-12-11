<?php

namespace Statamic\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Sites\Site;

/**
 * @method static self instance()
 * @method static array toArray()
 * @method static self withRequest(Request $request)
 * @method static self withSite(Site $site)
 * @method static self withContent($content)
 * @method static mixed content()
 * @method static mixed get(string $key)
 * @method static void set(string $key, $value)
 * @method static void data(array $data)
 * @method static self hydrated(Closure $callback)
 * @method static self hydrate()
 * @method static array getViewData(string $view)
 * @method static Collection sections()
 * @method static self clearSections()
 *
 * @see \Statamic\View\Cascade
 */
class Cascade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\View\Cascade::class;
    }
}
