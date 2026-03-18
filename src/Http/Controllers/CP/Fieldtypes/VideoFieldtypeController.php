<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Fieldtypes\Video\Video;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request): Video
    {
        if (! is_null($url = $request->query('url'))) {
            return Video::fromUrl($url);
        }

        if ($this->isCloudflareStream($request)) {
            $id = $request->query('id');
            $embedUrl = "https://iframe.cloudflarestream.com/{$id}";
            $iframe = "<iframe src='$embedUrl' frameborder='0' allow='fullscreen' style='height: 100%; width: 100%;'></iframe>";

            return new Video(id: $id, provider: 'Cloudflare', embed: $iframe);
        }

        return Video::notSupported();
    }

    private function isCloudflareStream(Request $request): bool
    {
        return $request->has('id') && $request->query('type') === 'Cloudflare';
    }
}
