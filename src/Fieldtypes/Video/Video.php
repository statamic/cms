<?php

namespace Statamic\Fieldtypes\Video;

use ArrayAccess;
use Embera\Embera;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonSerializable;
use Statamic\Contracts\Support\Boolable;
use Statamic\Support\FileTypes;
use Throwable;

class Video implements Arrayable, ArrayAccess, Boolable, JsonSerializable
{
    const CACHE_TTL = 3600;
    const CLOUDFLARE_PREFIX = 'cloudflare:';
    const CLOUDFLARE_EMBED_URL = 'https://iframe.cloudflarestream.com/';

    public static function fromValue(?string $value): self
    {
        if (blank($value)) {
            return static::unsupported($value);
        }

        if (Str::startsWith($value, self::CLOUDFLARE_PREFIX)) {
            $id = Str::after($value, self::CLOUDFLARE_PREFIX);

            return blank($id)
                ? static::unsupported($value)
                : new self(Providers::CLOUDFLARE, $value, self::CLOUDFLARE_EMBED_URL.$id, $id);
        }

        if (static::isVideoFile($value)) {
            return new self(Providers::FILE, $value, $value);
        }

        return static::fromOembed($value);
    }

    public static function unsupported(?string $value = null): self
    {
        return new self(Providers::UNSUPPORTED, $value);
    }

    public function __construct(
        public readonly string $provider,
        public readonly ?string $url = null,
        public readonly ?string $embedUrl = null,
        public readonly ?string $id = null,
    ) {
    }

    public function isSupported(): bool
    {
        return $this->provider !== Providers::UNSUPPORTED;
    }

    public function toArray(): array
    {
        return [
            'embed_url' => $this->embedUrl,
            'id' => $this->id,
            'provider' => $this->provider,
            'url' => $this->url,
        ];
    }

    public function toBool(): bool
    {
        return $this->isSupported();
    }

    public function __toString(): string
    {
        return (string) $this->url;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset)
    {
        return array_key_exists($offset, $this->toArray());
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->toArray()[$offset] ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value)
    {
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset)
    {
    }

    protected static function embedUrlFrom(array $response): ?string
    {
        if (blank($html = Arr::get($response, 'html'))) {
            return null;
        }

        if (! preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return null;
        }

        $url = html_entity_decode($matches[1], ENT_QUOTES);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return match (parse_url($url, PHP_URL_SCHEME)) {
            'https' => $url,
            'http' => Str::replaceStart('http://', 'https://', $url),
            default => null,
        };
    }

    protected static function fromOembed(string $url): self
    {
        if ($video = static::lookup($url, Embera::ONLY_FAKE_RESPONSES)) {
            return $video;
        }

        return Cache::remember(
            'statamic::video-fieldtype.'.md5($url),
            self::CACHE_TTL,
            fn () => static::lookup($url, Embera::DISABLE_FAKE_RESPONSES) ?? static::unsupported($url),
        );
    }

    protected static function isVideoFile(string $url): bool
    {
        if (blank($path = parse_url($url, PHP_URL_PATH))) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), FileTypes::video());
    }

    protected static function lookup(string $url, int $fakeResponses): ?self
    {
        try {
            $embera = new Embera(
                ['fake_responses' => $fakeResponses],
                new Providers,
                new HttpClient,
            );

            $response = $embera->getUrlData($url);
        } catch (Throwable) {
            return null;
        }

        if (empty($response) || blank($embedUrl = static::embedUrlFrom($first = Arr::first($response)))) {
            return null;
        }

        return new self(
            Arr::get($first, 'embera_provider_name', Providers::UNSUPPORTED),
            $url,
            $embedUrl,
        );
    }
}
