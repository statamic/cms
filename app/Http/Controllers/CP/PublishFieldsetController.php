<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Fieldset;

class PublishFieldsetController extends CpController
{
    public function show($fieldset)
    {
        return Fieldset::get($fieldset)->toPublishArray();
    }
}
