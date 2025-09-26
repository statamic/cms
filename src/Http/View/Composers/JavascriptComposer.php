<?php

namespace Statamic\Http\View\Composers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Statamic\CommandPalette\Category;
use Statamic\CP\Navigation\NavItem;
use Statamic\Facades\CommandPalette;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\Icon;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Icons\IconSet;
use Statamic\Licensing\LicenseManager;
use Statamic\Statamic;
use Statamic\Support\Str;
use voku\helper\ASCII;

class JavascriptComposer
{
    const VIEWS = ['statamic::partials.scripts'];

    public function compose(View $view)
    {
        $variables = $this->commonVariables();

        if (Gate::allows('access cp')) {
            $variables = array_merge($variables, $this->protectedVariables());
        }

        Statamic::provideToScript($variables);
    }

    private function commonVariables()
    {
        return [
            'csrfToken' => csrf_token(),
            'cpUrl' => cp_route('index'),
            'cpRoot' => Str::start(config('statamic.cp.route'), '/'),
            'urlPath' => Str::after(request()->getRequestUri(), config('statamic.cp.route').'/'),
            'resourceUrl' => Statamic::cpAssetUrl(),
            'flash' => Statamic::flash(),
            'toasts' => Toast::toArray(),
            'translationLocale' => app('translator')->locale(),
            'translations' => $this->translations(),
            'locale' => Statamic::cpLocale(),
            'direction' => Statamic::cpDirection(),
            'asciiReplaceExtraSymbols' => $replaceSymbols = config('statamic.system.ascii_replace_extra_symbols'),
            'charmap' => ASCII::charsArray($replaceSymbols),
            'cmsName' => __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic'),
            'logos' => $this->logos(),
        ];
    }

    private function protectedVariables()
    {
        $user = User::current();

        return [
            'version' => Statamic::version(),
            'isPro' => Statamic::pro(),
            'laravelVersion' => app()->version(),
            'locales' => config('statamic.system.locales'),
            'ajaxTimeout' => config('statamic.system.ajax_timeout'),
            'googleDocsViewer' => config('statamic.assets.google_docs_viewer'),
            'focalPointEditorEnabled' => config('statamic.assets.focal_point_editor'),
            'user' => $this->user($user),
            'defaultPreferences' => Preference::default()->all(),
            'paginationSize' => config('statamic.cp.pagination_size'),
            'paginationSizeOptions' => config('statamic.cp.pagination_size_options'),
            'multisiteEnabled' => Site::multiEnabled(),
            'sites' => $this->sites(),
            'selectedSite' => Site::selected()->handle(),
            'selectedSiteUrl' => Site::selected()->url(),
            'supportUrl' => config('statamic.cp.support_url'),
            'preloadableFieldtypes' => FieldtypeRepository::preloadable()->keys(),
            'livePreview' => config('statamic.live_preview'),
            'permissions' => $this->permissions($user),
            'customSvgIcons' => $this->icons(),
            'commandPaletteCategories' => Category::order(),
            'commandPalettePreloadedItems' => CommandPalette::getPreloadedItems(),
            'linkToDocs' => config('statamic.cp.link_to_docs'),
            'licensing' => $this->licensing(),
            'nav' => $this->nav(),
        ];
    }

    protected function sites()
    {
        return Site::authorized()->map(function ($site) {
            return [
                'name' => $site->name(),
                'handle' => $site->handle(),
                'lang' => $site->lang(),
            ];
        })->values();
    }

    protected function permissions($user)
    {
        $permissions = $user ? $user->permissions() : [];

        return base64_encode(json_encode($permissions));
    }

    protected function user($user)
    {
        if (! $user) {
            return [];
        }

        return $user->toAugmentedCollection()->merge([
            'preferences' => Preference::all(),
            'permissions' => $user->permissions()->all(),
            'theme' => $user->preferredTheme(),
            'is_impersonating' => session()->has('statamic_impersonated_by'),
        ])->toArray();
    }

    protected function translations(): array
    {
        $translations = app('translator')->toJson();
        $fallbackTranslations = tap(app('translator'))->setLocale(app('translator')->getFallback())->toJson();

        return array_merge($fallbackTranslations, $translations);
    }

    private function icons()
    {
        return Icon::sets()->mapWithKeys(fn (IconSet $set) => [
            $set->name() => $set->contents(),
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
            'text' => config('statamic.cp.custom_logo_text') ?? config('app.name'),
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
        ];
    }

    private function nav()
    {
        return collect(Nav::build())->map(function ($section) {
            return [
                'display' => $section['display'],
                'items' => $this->navItems($section['items']->all()),
            ];
        })->all();
    }

    private function navItems(array $items)
    {
        return collect($items)->map(function (NavItem $item) {
            return [
                'display' => $item->display(),
                'icon' => $item->icon(),
                'url' => $item->url(),
                'attributes' => $item->attributes(),
                'active' => $item->isActive(),
                'children' => $this->navItems($item->resolveChildren()->children()?->all() ?? []),
            ];
        })->all();
    }
}
