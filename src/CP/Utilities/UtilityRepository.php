<?php

namespace Statamic\CP\Utilities;

use Illuminate\Support\Facades\Route;
use Statamic\Facades\User;

class UtilityRepository
{
    protected $utilities;

    public function __construct()
    {
        $this->utilities = collect([]);
    }

    public function make($handle)
    {
        return (new Utility)->handle($handle);
    }

    public function push(Utility $utility)
    {
        $this->utilities[$utility->handle()] = $utility;

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
