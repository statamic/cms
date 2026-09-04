<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Fieldtypes\Video\Video;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request): Video
    {
        if (is_null($url = $request->query('url'))) {
            return Video::notSupported();
        }

        return Video::fromUrl($url);
    }
}
