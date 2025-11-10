<?php

namespace Statamic\Http\Controllers\CP;

use Inertia\Inertia;
use Statamic\Licensing\LicenseManager as Licenses;

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
                'key' => $site->key(),
                'valid' => $site->valid(),
                'domain' => $site->domain(),
                'hasMultipleDomains' => $site->hasMultipleDomains(),
                'additionalDomainCount' => $site->additionalDomainCount(),
                'invalidReason' => $site->invalidReason(),
                'usesIncorrectKeyFormat' => $site->key() && $site->usesIncorrectKeyFormat(),
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
            'addToCartUrl' => $this->addToCartUrl($site, $statamic, $addons),
            'usingLicenseKeyFile' => $licenses->usingLicenseKeyFile(),
            'refreshUrl' => cp_route('utilities.licensing.refresh'),
        ]);
    }

    public function refresh(Licenses $licenses)
    {
        $licenses->refresh();

        return redirect()
            ->cpRoute('utilities.licensing')
            ->with('success', __('Data updated'));
    }

    public function addToCartUrl($site, $statamic, $addons)
    {
        return 'https://statamic.com/cart/bulk-add?'.http_build_query([
            'site' => $site->key(),
            'statamic' => ! $statamic->valid(),
            'products' => $addons->reject->valid()->map->addon()->map(function ($addon) {
                $product = $addon->marketplaceId();
                if ($edition = $addon->edition()) {
                    $product .= ':'.$edition;
                }

                return $product;
            })->implode(','),
        ]);
    }
}
