<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Statamic\Validation\UniqueEntryValue;
use Statamic\Validation\UniqueFormHandle;
use Statamic\Validation\UniqueTermValue;
use Statamic\Validation\UniqueUserValue;

class ValidationServiceProvider extends ServiceProvider
{
    protected $rules = [
        'unique_entry_value' => UniqueEntryValue::class,
        'unique_term_value' => UniqueTermValue::class,
        'unique_user_value' => UniqueUserValue::class,
        'unique_form_handle' => UniqueFormHandle::class,
    ];

    public function boot()
    {
        foreach ($this->rules as $rule => $class) {
            Validator::extend($rule, $class);
        }
    }
}
