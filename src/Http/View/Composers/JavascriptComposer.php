<?php

namespace Statamic\Http\View\Composers;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\User;
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
            'cpRoot' => str_start(config('statamic.cp.route'), '/'),
            'urlPath' => Str::after(request()->getRequestUri(), config('statamic.cp.route').'/'),
            'resourceUrl' => Statamic::cpAssetUrl(),
            'flash' => Statamic::flash(),
            'toasts' => Toast::toArray(),
            'translationLocale' => app('translator')->locale(),
            'translations' => app('translator')->toJson(),
            'locale' => config('app.locale'),
            'asciiReplaceExtraSymbols' => $replaceSymbols = config('statamic.system.ascii_replace_extra_symbols'),
            'charmap' => ASCII::charsArray($replaceSymbols),
        ];
    }

    private function protectedVariables()
    {
        $user = User::current();
        $licenses = app('Statamic\Licensing\LicenseManager');

        return [
            'version' => Statamic::version(),
            'laravelVersion' => app()->version(),
            'locales' => config('statamic.system.locales'),
            'ajaxTimeout' => config('statamic.system.ajax_timeout'),
            'googleDocsViewer' => config('statamic.assets.google_docs_viewer'),
            'focalPointEditorEnabled' => config('statamic.assets.focal_point_editor'),
            'user' => $this->user($user),
            'paginationSize' => config('statamic.cp.pagination_size'),
            'paginationSizeOptions' => config('statamic.cp.pagination_size_options'),
            'sites' => $this->sites(),
            'selectedSite' => Site::selected()->handle(),
            'preloadableFieldtypes' => FieldtypeRepository::preloadable()->keys(),
            'livePreview' => config('statamic.live_preview'),
            'permissions' => $this->permissions($user),
            'hasLicenseBanner' => $licenses->invalid() || $licenses->requestFailed(),
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
        ])->toArray();
    }
}
