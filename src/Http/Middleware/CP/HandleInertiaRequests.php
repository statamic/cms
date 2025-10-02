<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\OAuth;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Licensing\LicenseManager;
use Statamic\Statamic;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'statamic::layout';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_filter([
            ...parent::share($request),
            '_statamic' => $request->inertia() ? $this->alwaysProps() : [
                ...$this->alwaysProps(),
                ...$this->protectedProps(),
                'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
                'logos' => $this->logos(),
            ],
        ]);
    }

    private function alwaysProps()
    {
        return [
            'nav' => $this->nav(),
            'isInertia' => true,
        ];
    }

    private function protectedProps()
    {
        if (Statamic::$isRenderingCpException || ! Gate::allows('access cp')) {
            return [];
        }

        return [
            'isPro' => Statamic::pro(),
            'selectedSiteUrl' => Site::selected()->url(),
            'licensing' => $this->licensing(),
            'sessionExpiry' => $this->sessionExpiry(),
        ];
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

    private function licensing()
    {
        $licenses = app(LicenseManager::class);

        return [
            'valid' => $licenses->valid(),
            'requestFailed' => $licenses->requestFailed(),
            'requestFailureMessage' => $licenses->requestFailureMessage(),
            'isOnPublicDomain' => $licenses->isOnPublicDomain(),
            'alert' => ($alert = $licenses->licensingAlert()) ? [
                ...$alert,
                'manageUrl' => User::current()->can('access licensing utility') ? cp_route('utilities.licensing') : null,
            ] : null,
        ];
    }

    private function nav()
    {
        if (Statamic::$isRenderingCpException || ! Gate::allows('access cp')) {
            return [];
        }

        return collect(Nav::build())->map(function ($section, $sectionIndex) {
            return [
                'id' => (string) $sectionIndex,
                'display' => $section['display'],
                'items' => $this->navItems($section['items']->all(), $sectionIndex),
            ];
        })->all();
    }

    private function navItems(array $items, $parentId = null)
    {
        return collect($items)->map(function (NavItem $item, $index) use ($parentId) {
            $id = $parentId !== null ? "{$parentId}.{$index}" : (string) $index;

            return [
                'id' => $id,
                'display' => $item->display(),
                'icon' => $item->icon(),
                'url' => $item->url(),
                'attributes' => $item->attributes(),
                'active' => $item->isActive(),
                'children' => $this->navItems($item->resolveChildren()->children()?->all() ?? [], $id),
                'extra' => $item->extra(),
                'view' => ($view = $item->view()) ? view($view, ['item' => $item])->render() : null,
            ];
        })->all();
    }

    private function breadcrumbs()
    {
        return Breadcrumbs::additional();
    }

    private function sessionExpiry()
    {
        return [
            'email' => User::current()->email(),
            'lifetime' => config('session.lifetime') * 60,
            'warnAt' => 60,
            'oauthProvider' => $this->sessionExpiryOauth(),
            'auth' => config('statamic.cp.auth'),
        ];
    }

    private function sessionExpiryOauth()
    {
        if (! $provider = session('oauth-provider')) {
            return null;
        }

        return OAuth::provider($provider)->toArray();
    }
}
