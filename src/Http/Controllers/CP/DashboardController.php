<?php

namespace Statamic\Http\Controllers\CP;

use Inertia\Inertia;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Statamic;
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

        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
            'pro' => Statamic::pro(),
            'blueprintsUrl' => cp_route('blueprints.index'),
            'collectionsCreateUrl' => cp_route('collections.create'),
            'navigationCreateUrl' => cp_route('navigation.create'),
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
                    'component' => $widget->component(),
                ];
            })
            ->reject(function ($widget) {
                return empty($widget['component']) && empty($widget['html']);
            })
            ->values();
    }
}
