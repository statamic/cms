<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\User;
use Statamic\Extend\Management\WidgetLoader;

class DashboardController extends CpController
{
    /**
     * View for the CP dashboard
     *
     * @param WidgetLoader $loader
     * @return mixed
     */
    public function index(WidgetLoader $loader)
    {
        return view('statamic::dashboard', [
            'widgets' => $this->getDisplayableWidgets($loader)
        ]);
    }

    /**
     * Get displayable widgets.
     *
     * @param WidgetLoader $loader
     * @return \Illuminate\Support\Collection
     */
    private function getDisplayableWidgets($loader)
    {
        return collect(config('statamic.cp.widgets', []))
            ->map(function ($config) {
                return is_string($config) ? ['type' => $config] : $config;
            })
            ->filter(function ($config) {
                return collect($config['can'] ?? $config['permissions'] ?? ['access cp'])
                    ->filter(function ($ability) {
                        return auth()->user()->can($ability);
                    })
                    ->isNotEmpty();
            })
            ->map(function ($config) use ($loader) {
                return [
                    'widget' => $widget = $loader->load(array_get($config, 'type'), $config),
                    'classes' => $widget->config('classes'),
                    'width' => $widget->config('width', 100),
                    'html' => (string) $widget->html()
                ];
            })
            ->reject(function ($widget) {
                return empty($widget['html']);
            });
    }
}
