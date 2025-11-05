<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Statamic\CP\Toasts\Manager;
use Statamic\Statamic;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'statamic::layout';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function __construct(private Manager $toasts)
    {
        //
    }

    public function share(Request $request): array
    {
        return array_filter([
            ...parent::share($request),
            '_statamic' => [
                'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
                'logos' => $this->logos(),
            ],
            '_toasts' => $this->toasts($request),
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
            'siteName' => config('app.name'),
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

    private function toasts(Request $request)
    {
        $session = $request->session();

        if ($message = $session->get('success')) {
            $this->toasts->success($message);
        }

        if ($message = $session->get('error')) {
            $this->toasts->error($message);
        }

        if ($message = $session->get('info')) {
            $this->toasts->info($message);
        }

        $toasts = $this->toasts->toArray();

        $this->toasts->clear();

        return $toasts;
    }
}
