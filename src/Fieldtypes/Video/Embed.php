<?php

namespace Statamic\Fieldtypes\Video;

use Illuminate\Support\Str;
use Statamic\Fields\ArrayableString;
use Statamic\Support\FileTypes;

class Embed extends ArrayableString
{
    const FILE = 'file';
    const UNSUPPORTED = 'unsupported';
    const VIMEO = 'vimeo';
    const VIMEO_PRIVACY_HASH_PATTERN = '/^[a-zA-Z0-9]+$/';
    const YOUTUBE = 'youtube';
    const YOUTUBE_ID_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    public static function fromValue(?string $value): self
    {
        if (blank($value)) {
            return static::unsupported($value);
        }

        if ($provider = static::oembedProvider($value)) {
            [$id, $privacyHash] = match ($provider) {
                self::VIMEO => static::vimeoIdAndPrivacyHash($value),
                self::YOUTUBE => [static::youtubeId($value), null],
            };

            return new self($provider, $value, static::embedUrl($value), $id, $privacyHash);
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
        public readonly ?string $privacyHash = null,
    ) {
        parent::__construct($url);
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
            'privacy_hash' => $this->privacyHash,
            'provider' => $this->provider,
            'url' => $this->url,
        ];
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

    protected static function youtubeId(string $url): ?string
    {
        $url = static::withScheme($url);
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (parse_url($url, PHP_URL_HOST) === 'youtu.be') {
            $id = Str::before(ltrim($path, '/'), '/');
        } elseif (preg_match('#^/(?:embed|shorts)/([^/]+)#', $path, $matches)) {
            $id = $matches[1];
        } else {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            $id = $query['v'] ?? null;
        }

        return is_string($id) && preg_match(self::YOUTUBE_ID_PATTERN, $id) ? $id : null;
    }

    protected static function vimeoIdAndPrivacyHash(string $url): array
    {
        $url = static::withScheme($url);
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        $patterns = [
            '#^/(\d+)(?:/([a-zA-Z0-9]+))?/?$#',
            '#^/video/(\d+)/?$#',
            '#^/progressive_redirect/playback/(\d+)/#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path, $matches)) {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
                $privacyHash = $matches[2] ?? $query['h'] ?? null;

                return [$matches[1], is_string($privacyHash) && preg_match(self::VIMEO_PRIVACY_HASH_PATTERN, $privacyHash) ? $privacyHash : null];
            }
        }

        return [null, null];
    }

    // Without a scheme, parse_url treats the host as part of the path.
    protected static function withScheme(string $url): string
    {
        return preg_match('#^([a-z][a-z0-9+.-]*:)?//#i', $url) ? $url : 'https://'.$url;
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
