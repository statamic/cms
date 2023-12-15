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
    public function package($package, $version = null)
    {
        $uri = "packages/$package/$version";

        return Cache::rememberWithExpiration("marketplace-$uri", function () use ($uri) {
            try {
                return [60 => Client::get($uri)['data']];
            } catch (RequestException $e) {
                return [5 => null];
            }
        });
    }

    public function releases($package)
    {
        $uri = "packages/$package/releases";

        return Cache::rememberWithExpiration("marketplace-$uri", function () use ($uri) {
            try {
                return [60 => collect(Client::get($uri)['data'])];
            } catch (RequestException $e) {
                return [5 => collect()];
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
}
