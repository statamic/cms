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

    public function routes()
    {
        Route::group(['namespace' => '\\'], function () {
            $this->all()->each(function ($utility) {
                Route::get($utility->slug(), $utility->action())
                    ->name('utilities.'.$utility->slug());

                if ($routeClosure = $utility->routes()) {
                    $routeClosure(Route::getFacadeRoot());
                }
            });
        });
    }
}