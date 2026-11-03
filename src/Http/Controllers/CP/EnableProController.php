<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\User;
use Statamic\Licensing\LicenseManager;
use Statamic\Statamic;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class EnableProController extends CpController
{
    public function __invoke(Request $request, LicenseManager $licenses)
    {
        if (! User::current()->isSuper()) {
            throw new AuthorizationException;
        }

        $hasLicenseKey = filled(config('statamic.system.license_key'));
        $licenseKey = trim((string) $request->input('license_key', ''));

        $request->merge([
            'license_key' => $licenseKey !== '' ? $licenseKey : null,
        ]);

        $request->validate([
            'license_key' => [
                $hasLicenseKey ? 'nullable' : 'required',
                'string',
            ],
        ], [
            'license_key.required' => __('statamic::messages.enable_pro_license_key_required'),
        ]);

        if ($licenseKey !== '') {
            $this->validateLicenseKeyWithOutpost($licenses, $licenseKey);
        }

        Statamic::enablePro($licenseKey !== '' ? $licenseKey : null);

        return back()->withSuccess(__('Statamic Pro enabled'));
    }

    private function validateLicenseKeyWithOutpost(LicenseManager $licenses, string $licenseKey): void
    {
        config(['statamic.system.license_key' => $licenseKey]);

        $licenses->refresh();

        if ($licenses->outpostIsOffline() || $licenses->requestFailed()) {
            throw ValidationException::withMessages([
                'license_key' => $licenses->requestFailureMessage(),
            ]);
        }

        $reason = Arr::get($licenses->site()->response() ?? [], 'reason');

        if ($reason === 'unknown_site' || $licenses->site()->response() === null) {
            throw ValidationException::withMessages([
                'license_key' => __('statamic::messages.enable_pro_license_key_invalid'),
            ]);
        }
    }
}
