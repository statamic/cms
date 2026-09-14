<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Fieldtypes\Video\Video;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request): array
    {
        return Video::fromValue($request->query('value'))->toArray();
    }
}
