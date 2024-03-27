<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Statamic\Validation\UniqueFormHandle;

class ValidationServiceProvider extends ServiceProvider
{
    protected $rules = [
        'unique_form_handle' => UniqueFormHandle::class,
    ];

    public function boot()
    {
        foreach ($this->rules as $rule => $class) {
            Validator::extend($rule, $class);
        }
    }
}
