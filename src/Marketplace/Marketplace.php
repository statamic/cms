<?php

namespace Statamic\Marketplace;

use Facades\Statamic\Marketplace\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Addon;
use Statamic\Marketplace\Addon as AddonProduct;
use Statamic\Statamic;

class Marketplace
{
    public function packages(array $packages)
    {
        $uri = 'packages';
        $hash = md5(json_encode($packages));

        return Cache::rememberWithExpiration("marketplace-$uri-$hash", function () use ($uri, $packages) {
            try {
                $response = Client::post($uri, ['packages' => $packages]);

                return [60 => collect($response['data'])];
            } catch (RequestException $e) {
                return [5 => collect()];
            }
        });
    }

    public function releases($package, $params = [])
    {
        $uri = "packages/$package/releases";
        $hash = md5(json_encode($params));

        return Cache::rememberWithExpiration("marketplace-$uri-$hash", function () use ($uri, $params) {
            $fallback = [5 => ['data' => collect(), 'meta' => null]];

            try {
                if (! $response = Client::get($uri, $params)) {
                    return $fallback;
                }

                return [60 => [
                    'data' => collect($response['data']),
                    'meta' => $response['meta'] ?? null,
                ]];
            } catch (RequestException $e) {
                return $fallback;
            }
        });
    }

    public function product($slug)
    {
        if ($slug === Statamic::CORE_SLUG) {
            return $this->statamic();
        }

        $addon = Addon::all()->first(function ($addon) use ($slug) {
            return $addon->slug() === $slug;
        });

        if ($addon) {
            return new AddonProduct($addon);
        }
    }

    public function statamic()
    {
        return new Core;
    }

    public function themes()
    {
        $uri = 'cp-themes';

        return Cache::rememberWithExpiration("marketplace-$uri", function () use ($uri) {
            try {
                $response = Client::get($uri);

                return [60 => collect($response['data'])];
            } catch (RequestException $e) {

                return [5 => collect()];
            }
        });
    }

    public function clearThemesCache()
    {
        Cache::forget('marketplace-cp-themes');
        Client::clearCache('cp-themes');
    }
}
