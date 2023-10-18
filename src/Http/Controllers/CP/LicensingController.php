<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Licensing\LicenseManager as Licenses;

class LicensingController extends CpController
{
    public function show(Licenses $licenses)
    {
        return view('statamic::licensing', [
            'requestError' => $licenses->requestFailed(),
            'site' => $site = $licenses->site(),
            'statamic' => $statamic = $licenses->statamic(),
            'addons' => $addons = $licenses->addons()->filter->existsOnMarketplace(),
            'unlistedAddons' => $licenses->addons()->reject->existsOnMarketplace(),
            'configCached' => app()->configurationIsCached(),
            'manageLicensesUrl' => $this->manageLicensesUrl($site),
        ]);
    }

    public function refresh(Licenses $licenses)
    {
        $licenses->refresh();

        return redirect()
            ->cpRoute('utilities.licensing')
            ->with('success', __('Data updated'));
    }

    public function manageLicensesUrl($site): string
    {
        $url = 'https://statamic.com/account/manage-licenses';

        if ($site->key()) {
            $url .= "?site={$site->key()}";
        }

        return $url;
    }
}
