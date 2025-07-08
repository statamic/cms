<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Widgets\Loader;

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

        if ($widgets->isEmpty()) {
            return view('statamic::dashboard.empty');
        }

        return view('statamic::dashboard.dashboard', [
            'widgets' => $widgets,
        ]);
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
                ];
            })
            ->reject(function ($widget) {
                return empty($widget['html']);
            });
    }
}
