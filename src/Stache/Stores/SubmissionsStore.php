<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Form;

class SubmissionsStore extends AggregateStore
{
    protected $childStore = FormSubmissionsStore::class;

    public function key()
    {
        return 'form-submissions';
    }

    public function discoverStores()
    {
        return Form::all()->map(function ($form) {
            return $this->store($form->handle());
        });
    }
}
