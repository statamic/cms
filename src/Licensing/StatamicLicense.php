<?php

namespace Statamic\Licensing;

use Statamic\Statamic;

class StatamicLicense extends License
{
    public function pro()
    {
        return Statamic::pro();
    }

    public function version()
    {
        return Statamic::version();
    }
}
