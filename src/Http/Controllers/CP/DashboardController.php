<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Licensing\LicenseManager;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Widgets\Loader;

use function Statamic\trans as __;

class DashboardController extends CpController
{
    /**
     * View for the CP dashboard.
     *
     * @return mixed
     */
    public function index(Loader $loader)
    {
        $widgets = $this->getDisplayableWidgets($loader);
        $hasLicenseKey = filled(config('statamic.system.license_key'));

        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
            'pro' => Statamic::pro(),
            'canEnablePro' => User::current()->isSuper(),
            'hasLicenseKey' => $hasLicenseKey,
            'enableProUrl' => cp_route('dashboard.enable-pro'),
            'blueprintsUrl' => cp_route('blueprints.index'),
            'collectionsCreateUrl' => cp_route('collections.create'),
            'navigationCreateUrl' => cp_route('navigation.create'),
        ]);
    }

    public function enablePro(Request $request, LicenseManager $licenses)
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

    /**
     * Get displayable widgets.
     *
     * @param  Loader  $loader
     * @return \Illuminate\Support\Collection
     */
    private function getDisplayableWidgets($loader)
    {
        $widgets = Preference::get('widgets') ?? config('statamic.cp.widgets') ?? [];

        return collect($widgets)
            ->map(function ($config) {
                return is_string($config) ? ['type' => $config] : $config;
            })
            ->filter(function ($config) {
                if ($config['type'] === 'getting_started') {
                    return false;
                }

                if (! $sites = $config['sites'] ?? null) {
                    return true;
                }

                return in_array(Site::selected()->handle(), $sites);
            })
            ->filter(function ($config) {
                return collect($config['can'] ?? $config['permissions'] ?? ['access cp'])
                    ->filter(function ($ability) {
                        return User::current()->can($ability);
                    })
                    ->isNotEmpty();
            })
            ->map(function ($config) use ($loader) {
                return [
                    'widget' => $widget = $loader->load(Arr::get($config, 'type'), $config),
                    'classes' => $widget->config('classes'),
                    'width' => $widget->config('width', 100),
                    'html' => (string) $widget->html(),
                    'component' => $widget->component(),
                ];
            })
            ->reject(function ($widget) {
                return empty($widget['component']) && empty($widget['html']);
            })
            ->values();
    }
}
