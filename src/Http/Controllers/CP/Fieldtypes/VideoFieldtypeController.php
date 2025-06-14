<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Enums\VideoType;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request)
    {
        $url = $request->query('url');

        return [
            'provider' => $provider = $this->getProvider($url),
            'embed_url' => $this->getEmbedUrl($provider, $url),
        ];
    }

    private function getEmbedUrl(VideoType $provider, string $url): string
    {
        return match ($provider) {
            VideoType::CloudflareStream => $this->cloudflareStreamEmbedUrl($url),
            VideoType::Custom => $url,
            VideoType::Vimeo => $this->vimeoEmbedUrl($url),
            VideoType::YouTube => $this->youTubeEmbedUrl($url),
        };
    }

    private function getProvider(string $url): VideoType
    {
        return match (true) {
            str($url)->contains(['youtube.com', 'youtu.be']) => VideoType::YouTube,
            str($url)->contains('vimeo.com') => VideoType::Vimeo,
            is_int($url) => VideoType::CloudflareStream,
            default => VideoType::Custom,
        };
    }

    private function youTubeEmbedUrl(string $url): string
    {
        if (str($url)->contains('youtu.be')) {
            return str($url)->replace('youtu.be', 'youtube.com/embed');
        }

        return str($url)
            ->replace('watch?v=', 'embed/')
            ->replace('shorts/', 'embed/');
    }

    private function vimeoEmbedUrl(string $url): string
    {
        return str($url)->replace('vimeo.com/', 'player.vimeo.com/video/');
    }

    private function cloudflareStreamEmbedUrl(string $id): string
    {
        return "https://iframe.cloudflarestream.com/{$id}";
    }
}
