<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Statamic\Statamic;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'statamic::layout';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_filter([
            ...parent::share($request),
            '_statamic' => [
                'isInertia' => true,
                'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
                'logos' => $this->logos(),
            ],
        ]);
    }

    private function logos()
    {
        if (! Statamic::pro()) {
            return false;
        }

        if (is_string($light = config('statamic.cp.custom_logo_url'))) {
            $light = ['nav' => $light, 'outside' => $light];
        }

        if (is_string($dark = config('statamic.cp.custom_dark_logo_url'))) {
            $dark = ['nav' => $dark, 'outside' => $dark];
        }

        return [
            'text' => config('statamic.cp.custom_logo_text'),
            'light' => [
                'nav' => $light['nav'] ?? null,
                'outside' => $light['outside'] ?? null,
            ],
            'dark' => [
                'nav' => $dark['nav'] ?? null,
                'outside' => $dark['outside'] ?? null,
            ],
        ];
    }
}
