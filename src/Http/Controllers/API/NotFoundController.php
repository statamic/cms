<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Exceptions\NotFoundHttpException;

class NotFoundController
{
    public function __invoke()
    {
        throw new NotFoundHttpException;
    }
}
