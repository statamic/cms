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

use function Statamic\trans as __;

class Embed implements Arrayable, ArrayAccess, Boolable, JsonSerializable
{
    const CACHE_TTL = 3600;
    const CLOUDFLARE = 'cloudflare';
    const CLOUDFLARE_EMBED_URL = 'https://iframe.cloudflarestream.com/';
    const CLOUDFLARE_ID_PATTERN = '/^[a-zA-Z0-9]+$/';
    const CLOUDFLARE_PREFIX = 'cloudflare:';
    const FILE = 'file';
    const UNSUPPORTED = 'unsupported';
    const URL = 'url';
    const VIMEO = 'Vimeo';
    const YOUTUBE = 'Youtube';

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

        if ($provider = static::knownProvider($value)) {
            return new self($provider, $value, static::embedUrl($value));
        }

        if ($video = static::fromOembed($value)) {
            return $video;
        }

        if (static::isVideoFile($value)) {
            return new self(self::FILE, $value, $value);
        }

        return static::unsupported($value);
    }

    public static function options(): array
    {
        return collect(Providers::names())
            ->map(fn (string $provider) => ['value' => $provider, 'label' => $provider])
            ->sortBy('label')
            ->prepend(['value' => self::URL, 'label' => __('URL')])
            ->push(['value' => self::CLOUDFLARE, 'label' => __('Cloudflare Stream')])
            ->values()
            ->all();
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

        if (Str::contains($url, 'vimeo')) {
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
        return filled($url) && Str::contains($url, ['youtu.be', 'youtube', 'vimeo']);
    }

    protected static function isVideoFile(string $url): bool
    {
        if (blank($path = parse_url($url, PHP_URL_PATH))) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), FileTypes::video());
    }

    protected static function knownProvider(string $url): ?string
    {
        if (Str::contains($url, 'vimeo')) {
            return self::VIMEO;
        }

        if (Str::contains($url, ['youtu.be', 'youtube'])) {
            return self::YOUTUBE;
        }

        return null;
    }

    protected static function fromOembed(string $url): ?self
    {
        if ($video = static::lookup($url, Embera::ONLY_FAKE_RESPONSES)) {
            return $video;
        }

        return Cache::remember(
            'statamic::video-fieldtype.'.md5($url),
            self::CACHE_TTL,
            fn () => static::lookup($url, Embera::DISABLE_FAKE_RESPONSES),
        );
    }

    protected static function lookup(string $url, int $fakeResponses): ?self
    {
        try {
            $response = (new Embera(
                ['fake_responses' => $fakeResponses],
                new Providers,
                new HttpClient,
            ))->getUrlData($url);
        } catch (Throwable) {
            return null;
        }

        if (empty($response) || blank($embedUrl = static::embedUrlFromHtml($first = Arr::first($response)))) {
            return null;
        }

        return new self(Arr::get($first, 'embera_provider_name', self::UNSUPPORTED), $url, $embedUrl);
    }

    protected static function embedUrlFromHtml(array $response): ?string
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
