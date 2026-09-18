<?php

namespace Statamic\Fieldtypes\Video;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;
use Statamic\Contracts\Support\Boolable;
use Statamic\Support\FileTypes;

class Embed implements Arrayable, ArrayAccess, Boolable, JsonSerializable
{
    const CLOUDFLARE = 'cloudflare';
    const CLOUDFLARE_EMBED_URL = 'https://iframe.cloudflarestream.com/';
    const CLOUDFLARE_ID_PATTERN = '/^[a-zA-Z0-9]+$/';
    const CLOUDFLARE_PREFIX = 'cloudflare:';
    const FILE = 'file';
    const UNSUPPORTED = 'unsupported';
    const VIMEO = 'vimeo';
    const YOUTUBE = 'youtube';

    public static function fromValue(?string $value): self
    {
        if (blank($value)) {
            return static::unsupported($value);
        }

        if (Str::startsWith($value, self::CLOUDFLARE_PREFIX)) {
            $id = Str::after($value, self::CLOUDFLARE_PREFIX);

            return preg_match(self::CLOUDFLARE_ID_PATTERN, $id)
                ? new self(self::CLOUDFLARE, $value, self::CLOUDFLARE_EMBED_URL.$id, $id)
                : static::unsupported($value);
        }

        if ($provider = static::oembedProvider($value)) {
            return new self($provider, $value, static::embedUrl($value));
        }

        if (static::isVideoFile($value)) {
            return new self(self::FILE, $value, $value);
        }

        return static::unsupported($value);
    }

    public static function unsupported(?string $value = null): self
    {
        return new self(self::UNSUPPORTED, $value);
    }

    public function __construct(
        public readonly string $provider,
        public readonly ?string $url = null,
        public readonly ?string $embedUrl = null,
        public readonly ?string $id = null,
    ) {
    }

    public function isEmbeddable(): bool
    {
        return ! in_array($this->provider, [self::FILE, self::UNSUPPORTED]);
    }

    public function isSupported(): bool
    {
        return $this->provider !== self::UNSUPPORTED;
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
        return (bool) $this->url;
    }

    public function __toString(): string
    {
        return (string) $this->url;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return (string) $this;
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

    /**
     * Turn a link that's direct to a video's page into its embeddable equivalent.
     */
    public static function embedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return $url;
        }

        if (Str::contains($url, self::VIMEO)) {
            return static::vimeoEmbedUrl($url);
        }

        if (Str::contains($url, 'youtu.be')) {
            $url = str_replace('youtu.be', 'www.youtube.com/embed', $url);

            // Check for start at point and replace it with correct parameter.
            if (Str::contains($url, '?t=')) {
                $url = str_replace('?t=', '?start=', $url);
            }
        }

        if (Str::contains($url, 'youtube.com/watch?v=')) {
            $url = str_replace('watch?v=', 'embed/', $url);

            if (Str::contains($url, '&t=')) {
                $url = str_replace('&t=', '?start=', $url);
            }
        }

        if (Str::contains($url, 'youtube.com/shorts/')) {
            $url = str_replace('shorts/', 'embed/', $url);
        }

        if (Str::contains($url, 'youtube.com')) {
            $url = str_replace('youtube.com', 'youtube-nocookie.com', $url);
        }

        // This avoids SSL issues when using the non-www version
        if (Str::contains($url, '//youtube-nocookie.com')) {
            $url = str_replace('//youtube-nocookie.com', '//www.youtube-nocookie.com', $url);
        }

        if (Str::contains($url, '&') && ! Str::contains($url, '?')) {
            $url = Str::replaceFirst('&', '?', $url);
        }

        return $url;
    }

    public static function isEmbeddableUrl(?string $url): bool
    {
        return filled($url) && Str::contains($url, ['youtu.be', 'youtube', self::VIMEO]);
    }

    protected static function isVideoFile(string $url): bool
    {
        if (blank($path = parse_url($url, PHP_URL_PATH))) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), FileTypes::video());
    }

    protected static function oembedProvider(string $url): ?string
    {
        if (Str::contains($url, self::VIMEO)) {
            return self::VIMEO;
        }

        if (Str::contains($url, ['youtu.be', 'youtube'])) {
            return self::YOUTUBE;
        }

        return null;
    }

    // Unlisted vimeo urls are in the form vimeo.com/id/hash, but embeds pass the hash as a get param.
    protected static function vimeoEmbedUrl(string $url): string
    {
        $url = str_replace('/vimeo.com', '/player.vimeo.com/video', $url);
        $hash = '';

        if (! Str::contains($url, 'progressive_redirect') && Str::substrCount($url, '/') > 4) {
            $hash = Str::afterLast($url, '/');
            $url = Str::beforeLast($url, '/');

            if (Str::contains($hash, '?')) {
                $url .= '?'.Str::after($hash, '?');
                $hash = Str::before($hash, '?');
            }
        }

        $paramsToAdd = '?dnt=1';

        if ($hash) {
            $paramsToAdd .= '&h='.$hash;
        }

        return Str::contains($url, '?')
            ? str_replace('?', $paramsToAdd.'&', $url)
            : $url.$paramsToAdd;
    }
}
