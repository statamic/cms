<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Enums\VideoType;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request)
    {
        return $this->providerFromUrl($request->query('url'))->toArray();
    }

    private function providerFromUrl(string $url): Provider
    {
        $type = $this->type($url);
        $embedUrl = $this->embedUrl($type, $url);
        $prepend = $this->prepend($type);

        return new Provider(
            prepend: $prepend,
            provider: $type,
            embedUrl: $embedUrl
        );
    }

    private function type(string $url): VideoType
    {
        return match (true) {
            str($url)->contains(['youtube.com', 'youtu.be']) => VideoType::YouTube,
            str($url)->contains('vimeo.com') => VideoType::Vimeo,
            default => VideoType::CloudflareStream,
        };
    }

    private function embedUrl(VideoType $provider, string $url): string
    {
        return match ($provider) {
            VideoType::CloudflareStream => $this->cloudflareStreamEmbedUrl($url),
            VideoType::Custom => $url,
            VideoType::Vimeo => $this->vimeoEmbedUrl($url),
            VideoType::YouTube => $this->youTubeEmbedUrl($url),
        };
    }

    private function cloudflareStreamEmbedUrl(string $id): string
    {
        return "https://iframe.cloudflarestream.com/{$id}";
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

    private function prepend(VideoType $provider): string
    {
        return match ($provider) {
            VideoType::CloudflareStream => __('ID'),
            default => __('URL'),
        };
    }
}

class Provider
{
    public function __construct(
        public string $prepend,
        public VideoType $provider,
        public string $embedUrl,
    ) {
    }

    public function toArray(): array
    {
        return [
            'prepend' => $this->prepend,
            'provider' => $this->provider,
            'embed_url' => $this->embedUrl,
        ];
    }
}
