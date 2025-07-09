<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Embera\Embera;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Statamic\Http\Controllers\CP\CpController;

class VideoFieldtypeController extends CpController
{
    public function details(Request $request)
    {
        if (! is_null($url = $request->query('url'))) {
            return Video::fromUrl($url);
        }

        if ($this->isCloudflareStream($request)) {
            $id = $request->query('id');
            $embedUrl = "https://iframe.cloudflarestream.com/{$id}";
            $iframe = "<iframe src='$embedUrl' frameborder='0' allow='fullscreen'></iframe>";

            return new Video(id: $id, provider: 'Cloudflare', embedUrl: $iframe);
        }

        return Video::notSupported();
    }

    private function isCloudflareStream(Request $request): bool
    {
        return $request->has('id') && $request->query('type') === 'Cloudflare';
    }
}

class Video implements Arrayable
{
    public static function fromUrl(string $url): self
    {
        if (empty($details = (new Embera(['responsive' => true]))->getUrlData($url))) {
            return static::notSupported();
        }

        $data = new Fluent(Arr::first($details));

        return new self(
            id: $data->video_id,
            provider: $data->embera_provider_name,
            embedUrl: $data->html
        );
    }

    public static function notSupported(): self
    {
        return new self(provider: 'not_supported');
    }

    public function __construct(
        public string $provider,
        public ?string $id = null,
        public ?string $embedUrl = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'embed_url' => $this->embedUrl,
            'id' => $this->id,
            'provider' => $this->provider,
        ];
    }
}
