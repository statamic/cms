<?php

namespace Statamic\Fieldtypes\Video;

use Illuminate\Support\Str;
use Statamic\Fields\ArrayableString;
use Statamic\Support\FileTypes;

use function Statamic\trans as __;

class Embed extends ArrayableString
{
    const CLOUDFLARE = 'cloudflare';
    const FILE = 'file';
    const UNSUPPORTED = 'unsupported';
    const URL = 'url';
    const VIMEO = 'vimeo';
    const YOUTUBE = 'youtube';

    private const CLOUDFLARE_EMBED_URL = 'https://iframe.cloudflarestream.com/';
    private const CLOUDFLARE_ID_PATTERN = '/^[a-zA-Z0-9]+$/';
    private const CLOUDFLARE_PREFIX = 'cloudflare:';
    private const VIMEO_PRIVACY_HASH_PATTERN = '/^[a-zA-Z0-9]+$/';
    private const YOUTUBE_ID_PATTERN = '/^[a-zA-Z0-9_-]+$/';

    private string $provider;

    public function __construct(?string $value)
    {
        parent::__construct($value);

        $this->provider = self::providerFor($value);
    }

    public static function options(): array
    {
        return [
            ['value' => self::URL, 'label' => __('URL')],
            ['value' => self::CLOUDFLARE, 'label' => __('Cloudflare Stream')],
        ];
    }

    public function url(): ?string
    {
        return $this->value;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function embedUrl(): ?string
    {
        return match ($this->provider) {
            self::CLOUDFLARE => self::CLOUDFLARE_EMBED_URL.$this->id(),
            self::VIMEO, self::YOUTUBE => self::embedUrlFor($this->value),
            self::FILE => $this->value,
            default => null,
        };
    }

    public function trackableEmbedUrl(): ?string
    {
        return match ($this->provider) {
            self::VIMEO, self::YOUTUBE => self::trackableEmbedUrlFor($this->value),
            default => $this->embedUrl() ?? $this->value,
        };
    }

    public function id(): ?string
    {
        return match ($this->provider) {
            self::CLOUDFLARE => Str::after($this->value, self::CLOUDFLARE_PREFIX),
            self::VIMEO => self::vimeoIdAndPrivacyHash($this->value)[0],
            self::YOUTUBE => self::youtubeId($this->value),
            default => null,
        };
    }

    public function privacyHash(): ?string
    {
        return $this->provider === self::VIMEO
            ? self::vimeoIdAndPrivacyHash($this->value)[1]
            : null;
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
            'embed_url' => $this->embedUrl(),
            'id' => $this->id(),
            'privacy_hash' => $this->privacyHash(),
            'provider' => $this->provider,
            'url' => $this->value,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->value;
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
    public static function embedUrlFor(?string $url): ?string
    {
        if (blank($url)) {
            return $url;
        }

        if (Str::contains($url, self::VIMEO)) {
            return self::vimeoEmbedUrl($url);
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

    /**
     * Turn a link that's direct to a video's page into its embeddable equivalent, without privacy enhancements.
     */
    public static function trackableEmbedUrlFor(?string $url): ?string
    {
        if (blank($url)) {
            return $url;
        }

        if (Str::contains($url, self::VIMEO)) {
            return str_replace('/vimeo.com', '/player.vimeo.com/video', $url);
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

    private static function isVideoFile(string $url): bool
    {
        if (blank($path = parse_url($url, PHP_URL_PATH))) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), FileTypes::video());
    }

    private static function providerFor(?string $url): string
    {
        return match (true) {
            blank($url) => self::UNSUPPORTED,
            Str::startsWith($url, self::CLOUDFLARE_PREFIX) => self::isCloudflareId(Str::after($url, self::CLOUDFLARE_PREFIX)) ? self::CLOUDFLARE : self::UNSUPPORTED,
            Str::contains($url, self::VIMEO) => self::VIMEO,
            Str::contains($url, ['youtu.be', 'youtube']) => self::YOUTUBE,
            self::isVideoFile($url) => self::FILE,
            default => self::UNSUPPORTED,
        };
    }

    private static function isCloudflareId(string $id): bool
    {
        return preg_match(self::CLOUDFLARE_ID_PATTERN, $id) === 1;
    }

    private static function youtubeId(string $url): ?string
    {
        $url = self::withScheme($url);
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

    private static function vimeoIdAndPrivacyHash(string $url): array
    {
        $url = self::withScheme($url);
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
    private static function withScheme(string $url): string
    {
        return preg_match('#^([a-z][a-z0-9+.-]*:)?//#i', $url) ? $url : 'https://'.$url;
    }

    // Unlisted vimeo urls are in the form vimeo.com/id/hash, but embeds pass the hash as a get param.
    private static function vimeoEmbedUrl(string $url): string
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
