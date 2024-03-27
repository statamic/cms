<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    protected $rules = [
        //
    ];

    public function boot()
    {
        foreach ($this->rules as $rule => $class) {
            Validator::extend($rule, $class);
        }
    }
}
