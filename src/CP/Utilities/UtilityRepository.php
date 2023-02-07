<?php

namespace Statamic\CP\Utilities;

use Facades\Statamic\CP\Utilities\CoreUtilities;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\User;

class UtilityRepository
{
    protected $utilities;
    protected $extensions = [];

    public function __construct()
    {
        $this->utilities = collect([]);
    }

    public function boot()
    {
        CoreUtilities::boot();

        foreach ($this->extensions as $callback) {
            $callback($this);
        }
    }

    public function extend($callback)
    {
        $this->extensions[] = $callback;
    }

    public function make($handle)
    {
        return (new Utility)->handle($handle);
    }

    public function register($utility)
    {
        if (! $utility instanceof Utility) {
            $utility = $this->make($utility);
        }

        $this->utilities[$utility->handle()] = $utility;

        return $utility;
    }

    /** @deprecated */
    public function push(Utility $utility)
    {
        $this->register($utility);

        return $this;
    }

    public function all()
    {
        return $this->utilities;
    }

    public function authorized()
    {
        return $this->all()->filter(function ($utility) {
            return User::current()->can("access {$utility->handle()} utility");
        });
    }

    public function find($handle)
    {
        return $this->utilities->get($handle);
    }

    public function routes()
    {
        $this->boot();

        Route::namespace('\\')->prefix('utilities')->name('utilities.')->group(function () {
            $this->all()->each(function ($utility) {
                if ($utility->action()) {
                    Route::get($utility->slug(), $utility->action())
                        ->middleware("can:access {$utility->handle()} utility")
                        ->name($utility->slug());
                }

                if ($routeClosure = $utility->routes()) {
                    Route::name($utility->slug().'.')
                        ->prefix($utility->slug())
                        ->middleware("can:access {$utility->handle()} utility")
                        ->group(function () use ($routeClosure) {
                            $routeClosure(Route::getFacadeRoot());
                        });
                }
            });
        });
    }
}
