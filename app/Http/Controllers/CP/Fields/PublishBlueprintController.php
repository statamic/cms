<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class PublishBlueprintController extends CpController
{
    public function show($blueprint)
    {
        if (! $blueprint = Blueprint::find($blueprint)) {
            return response('Blueprint not found.', 404);
        }

        return $blueprint->toPublishArray();
    }
}
