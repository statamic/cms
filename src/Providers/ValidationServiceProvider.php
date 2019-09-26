<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Validation\UniqueEntryValue;
use Validator;

class ValidationServiceProvider extends ServiceProvider
{
    protected $rules = [
        'unique_entry_value' => UniqueEntryValue::class,
    ];

    public function boot()
    {
        foreach ($this->rules as $rule => $class) {
            Validator::extend($rule, $class);
        }
    }
}
