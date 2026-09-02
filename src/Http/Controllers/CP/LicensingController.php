<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Statamic\Facades\Config;
use Statamic\Licensing\LicenseManager as Licenses;
use Statamic\Licensing\SiteKey;
use Statamic\Statamic;

use function Statamic\trans as __;

class LicensingController extends CpController
{
    public function show(Licenses $licenses)
    {
        $site = $licenses->site();
        $statamic = $licenses->statamic();
        $addons = $licenses->addons()->filter->existsOnMarketplace();

        return Inertia::render('utilities/Licensing', [
            'requestError' => $licenses->requestFailed(),
            'site' => [
                'url' => $site->url(),
                'handoffUrl' => $site->handoffUrl(),
                'key' => $site->key(),
                'name' => $site->name(),
                'valid' => $site->valid(),
                'connected' => $site->isConnected(),
                'domains' => $site->domains()->values()->all(),
                'invalidReason' => $site->invalidReason(),
                'usesIncorrectKeyFormat' => $site->key() && $site->usesIncorrectKeyFormat(),
                'hasSharedKey' => $site->hasSharedKey(),
            ],
            'statamic' => [
                'valid' => $statamic->valid(),
                'pro' => $statamic->pro(),
                'version' => $statamic->version(),
                'invalidReason' => $statamic->invalidReason(),
            ],
            'addons' => $addons->map(fn ($addon) => [
                'name' => $addon->name(),
                'valid' => $addon->valid(),
                'version' => $addon->version(),
                'edition' => $addon->edition(),
                'invalidReason' => $addon->invalidReason(),
                'marketplaceUrl' => $addon->addon()->marketplaceUrl(),
            ])->values(),
            'unlistedAddons' => $licenses->addons()->reject->existsOnMarketplace()->map(fn ($addon) => [
                'name' => $addon->name(),
                'version' => $addon->version(),
            ])->values(),
            'configCached' => app()->configurationIsCached(),
            'purchase' => $site->isConnected() ? $this->purchase($site, $statamic, $addons) : null,
            'primaryAction' => $licenses->primaryAction(),
            'usingLicenseKeyFile' => $licenses->usingLicenseKeyFile(),
            'refreshUrl' => cp_route('utilities.licensing.refresh'),
            'mintUrl' => $site->key() ? null : cp_route('utilities.licensing.mint'),
        ]);
    }

    public function mint(SiteKey $siteKey, Licenses $licenses)
    {
        if (Config::getLicenseKey()) {
            return redirect()
                ->cpRoute('utilities.licensing')
                ->with('error', __('statamic::messages.licensing_site_key_already_exists'));
        }

        $key = $siteKey->mint();
        config(['statamic.system.site_key' => $key]);
        $licenses->refresh();

        return redirect()
            ->cpRoute('utilities.licensing')
            ->with('success', __('statamic::messages.licensing_site_key_generated'));
    }

    public function refresh(Licenses $licenses)
    {
        $licenses->refresh();

        return redirect()
            ->cpRoute('utilities.licensing')
            ->with('success', __('Data updated'));
    }

    public function purchase($site, $statamic, $addons): ?array
    {
        if (! $site?->isConnected()) {
            return null;
        }

        $unlicensedAddons = $addons->reject->valid();
        $needsStatamic = ! $statamic->valid();

        if (! $needsStatamic && $unlicensedAddons->isEmpty()) {
            return null;
        }

        $catalog = $this->marketplaceCatalog($needsStatamic, $unlicensedAddons);
        $items = collect();
        $siteName = filled($site?->name()) ? $site->name() : null;

        if ($needsStatamic) {
            $core = $catalog->get('statamic/cms', []);
            $items->push([
                'name' => __('Statamic Pro'),
                'detail' => $siteName ?: __('statamic::messages.licensing_buy_pro_detail'),
                'url' => rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/pricing',
                'price' => $this->formatPrice($core['price'] ?? null),
                'thumbnail' => $core['thumbnail'] ?? null,
            ]);
        }

        foreach ($unlicensedAddons as $addon) {
            $market = $catalog->get($addon->addon()->id(), []);
            $items->push([
                'name' => $addon->name(),
                'detail' => ($market['seller_name'] ?? null)
                    ?: $addon->addon()->developer()
                    ?: (isset($market['seller']) ? Str::headline($market['seller']) : null),
                'url' => $addon->addon()->marketplaceUrl(),
                'price' => $this->formatPrice($market['price'] ?? null),
                'thumbnail' => $market['thumbnail'] ?? null,
            ]);
        }

        $needsRenewal = $needsStatamic && $unlicensedAddons->isEmpty() && $statamic->needsRenewal();

        $label = match (true) {
            $needsRenewal => __('Renew License'),
            $needsStatamic && $unlicensedAddons->isEmpty() => __('Buy Statamic Pro'),
            ! $needsStatamic => __('Buy Addon Licenses'),
            default => __('Buy Licenses'),
        };

        $siteLabel = $siteName ?? __('this site');
        $description = match (true) {
            $needsRenewal => __('statamic::messages.licensing_renew_pro_description'),
            $needsStatamic && $unlicensedAddons->isEmpty() => __('statamic::messages.licensing_buy_pro_description'),
            ! $needsStatamic => __('statamic::messages.licensing_buy_addons_description', ['site' => $siteLabel]),
            default => __('statamic::messages.licensing_buy_mixed_description', ['site' => $siteLabel]),
        };

        return [
            'label' => $label,
            'title' => $label,
            'description' => $description,
            'items' => $items->values()->all(),
            'checkoutUrl' => $this->addToCartUrl($site, $statamic, $unlicensedAddons),
        ];
    }

    public function addToCartUrl($site, $statamic, $addons)
    {
        $unlicensedAddons = $addons->reject->valid();

        if ($statamic->valid() && $unlicensedAddons->isEmpty()) {
            return null;
        }

        return rtrim(config('statamic.system.licensing_url', 'https://statamic.com'), '/').'/cart/bulk-add?'.http_build_query([
            'site' => $site->key(),
            'statamic' => ! $statamic->valid(),
            'products' => $unlicensedAddons->map->addon()->map(function ($addon) {
                $product = $addon->marketplaceId();
                if ($edition = $addon->edition()) {
                    $product .= ':'.$edition;
                }

                return $product;
            })->implode(','),
        ]);
    }

    private function marketplaceCatalog(bool $needsStatamic, $addons)
    {
        $packages = collect($addons)->map(fn ($addon) => [
            'package' => $addon->addon()->id(),
            'version' => $addon->version(),
            'edition' => $addon->edition(),
        ]);

        if ($needsStatamic) {
            $packages->push([
                'package' => 'statamic/cms',
                'version' => Statamic::version(),
            ]);
        }

        if ($packages->isEmpty()) {
            return collect();
        }

        return Marketplace::packages($packages->values()->all());
    }

    private function formatPrice($price): ?string
    {
        if ($price === null || $price === '' || (float) $price <= 0) {
            return null;
        }

        $price = (float) $price;

        return '$'.(fmod($price, 1.0) === 0.0 ? (int) $price : number_format($price, 2));
    }
}
