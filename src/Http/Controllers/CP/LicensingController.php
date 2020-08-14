<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Licensing\LicenseManager as Licenses;

class LicensingController extends CpController
{
    public function show(Licenses $licenses)
    {
        return view('statamic::licensing', [
            'requestError' => $licenses->requestFailed(),
            'site' => $licenses->site(),
            'statamic' => $licenses->statamic(),
            'addons' => $licenses->addons()->filter->existsOnMarketplace(),
            'unlistedAddons' => $licenses->addons()->reject->existsOnMarketplace(),
        ]);
    }

    public function refresh(Licenses $licenses)
    {
        $licenses->refresh();

        return redirect()
            ->cpRoute('utilities.licensing')
            ->with('success', __('Data updated'));
    }
}
