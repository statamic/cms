<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\OAuth;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Licensing\LicenseManager;
use Statamic\Statamic;

class HandleAuthenticatedInertiaRequests
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::share($this->share($request));

        return $next($request);
    }

    public function share(Request $request): array
    {
        return [
            '_statamic' => [
                ...Inertia::getShared('_statamic'),
                ...$this->authedStatamicProps($request),
            ],
        ];
    }

    private function authedStatamicProps($request)
    {
        return $request->inertia() ? $this->alwaysProps() : [
            ...$this->alwaysProps(),
            ...$this->protectedProps(),
        ];
    }

    private function alwaysProps()
    {
        return [
            'isPro' => Statamic::pro(),
            'nav' => $this->nav(),
            'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
        ];
    }

    private function protectedProps()
    {
        if (Statamic::$isRenderingCpException || ! Gate::allows('access cp')) {
            return [];
        }

        return [
            'selectedSiteUrl' => Site::selected()->url(),
            'licensing' => $this->licensing(),
            'sessionExpiry' => $this->sessionExpiry(),
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
