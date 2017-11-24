<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Statamic\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $path = __DIR__.'/../../routes/';

        if (config('cp.enabled')) {
            Route::middleware('web')
                ->prefix(config('cp.route'))
                ->namespace($this->namespace . '\\CP')
                ->group($path . 'cp.php');
        }

        Route::middleware('web')
             ->namespace($this->namespace)
             ->group($path . 'web.php');
    }
}
