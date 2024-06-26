<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Statamic;
use Statamic\Support\Arr;

class CustomLogoComposer
{
    const VIEWS = [
        'statamic::partials.global-header',
        'statamic::partials.outside-logo',
    ];

    public function compose(View $view)
    {
        $view->with('customLogo', $this->customLogo($view, false));
        $view->with('customDarkLogo', $this->customLogo($view, true));
    }

    protected function customLogo($view, bool $darkMode = false)
    {
        if (! Statamic::pro()) {
            return false;
        }

        $config = config('statamic.cp.custom_logo_url');
        if ($darkMode && config('statamic.cp.custom_dark_logo_url')) {
            $config = config('statamic.cp.custom_dark_logo_url');
        }

        switch ($view->name()) {
            case 'statamic::partials.outside-logo':
                $type = 'outside';
                break;
            case 'statamic::partials.global-header':
                $type = 'nav';
                break;
            default:
                $type = 'other';
        }

        if ($logo = Arr::get($config, $type)) {
            return $logo;
        }

        if (! is_array($config)) {
            return $config;
        }

        return false;
    }
}
