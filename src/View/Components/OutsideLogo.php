<?php

namespace Statamic\View\Components;

use Illuminate\View\Component;
use Statamic\Statamic;
use Statamic\Support\Arr;

class OutsideLogo extends Component
{
    public function render()
    {
        return view('components.outside-logo', [
            'customLogo' => $this->customLogo(),
            'customDarkLogo' => $this->customLogo(dark: true),
            'customLogoText' => $this->customLogo(text: true),
        ]);
    }

    protected function customLogo(bool $dark = false, bool $text = false)
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

        $type = 'outside';

        if ($logo = Arr::get($config, $type)) {
            return $logo;
        }

        if (! is_array($config)) {
            return $config;
        }

        return false;
    }
}
