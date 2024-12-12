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
        $view->with('customLogo', $this->customLogo($view));
        $view->with('customDarkLogo', $this->customLogo($view, dark: true));
        $view->with('customLogoText', $this->customLogo($view, text: true));
    }

    protected function customLogo($view, bool $dark = false, bool $text = false)
    {
        if (! Statamic::pro()) {
            return false;
        }

        $config = config('statamic.cp.custom_logo_url');
        if ($dark && config('statamic.cp.custom_dark_logo_url')) {
            $config = config('statamic.cp.custom_dark_logo_url');
        }
        if ($text && config('statamic.cp.custom_logo_text')) {
            $config = config('statamic.cp.custom_logo_text');
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
