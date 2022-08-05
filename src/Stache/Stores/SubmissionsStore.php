<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class SubmissionsStore extends AggregateStore
{
    protected $childStore = FormSubmissionStore::class;

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
