<?php

namespace Statamic\Fieldtypes\Video;

use Embera\Embera;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class Video implements Arrayable
{
    public static function fromUrl(string $url): self
    {
        if (Str::startsWith($url, 'cloudflare:')) {
            $id = Str::after($url, 'cloudflare:');
            $embedUrl = 'https://iframe.cloudflarestream.com/'.e($id);
            $iframe = "<iframe src=\"{$embedUrl}\" frameborder=\"0\" allow=\"fullscreen\" style=\"height: 100%; width: 100%;\"></iframe>";

            return new self(id: $id, provider: 'Cloudflare', embed: $iframe);
        }

        if (empty($details = (new Embera(['responsive' => true]))->getUrlData($url))) {
            return static::notSupported();
        }

        $data = new Fluent(Arr::first($details));

        return new self(
            id: $data->video_id,
            provider: $data->embera_provider_name,
            embed: $data->html
        );
    }

    public static function notSupported(): self
    {
        return new self(provider: 'Not Supported');
    }

    public function __construct(
        public string $provider,
        public ?string $id = null,
        public ?string $embed = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'embed' => $this->embed,
            'id' => $this->id,
            'provider' => $this->provider,
        ];
    }
}
