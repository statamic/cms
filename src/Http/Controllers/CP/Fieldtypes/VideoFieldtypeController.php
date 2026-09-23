<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Fieldtypes\Video\Embed;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request): array
    {
        return Embed::fromValue($request->query('value'))->toArray();
    }
}
