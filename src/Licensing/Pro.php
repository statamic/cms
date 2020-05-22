<?php

namespace Statamic\Licensing;

use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class Pro
{
    public function check()
    {
        return config('statamic.api.enabled')
            || config('statamic.revisions.enabled')
            || Site::hasMultiple()
            || Form::count() > 1
            || User::count() > 1;
    }
}
