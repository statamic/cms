<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Validation\UniqueEntryValue;
use Validator;
use Statamic\Validation\UniqueUserValue;

class ValidationServiceProvider extends ServiceProvider
{
    protected $rules = [
        'unique_entry_value' => UniqueEntryValue::class,
        'unique_user_value' => UniqueUserValue::class,
    ];

    public function boot()
    {
        foreach ($this->rules as $rule => $class) {
            Validator::extend($rule, $class);
        }
    }
}
