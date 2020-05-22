<?php

namespace Statamic\Licensing;

use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class Pro
{
    public function check()
    {
        if (config('statamic.api.enabled')) {
            return true;
        }

        if (Site::hasMultiple()) {
            return true;
        }

        if (config('statamic.revisions.enabled')) {
            return true;
        }

        if (Form::count() > 1) {
            return true;
        }

        if (User::count() > 1) {
            return true;
        }

        return false;
    }
}
