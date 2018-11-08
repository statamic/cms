<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Blueprint;

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
