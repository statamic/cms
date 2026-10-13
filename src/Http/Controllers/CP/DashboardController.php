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
        $configs = $this->resolvedWidgetConfigs();
        $widgets = $this->buildDisplayableWidgets($loader, $configs);

        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
            'widgetConfigs' => $this->preProcessedConfigs($configs),
            'hasCustomWidgets' => Preference::get('dashboard.widgets') !== null,
            'canEditWidgets' => User::current()->can('access cp'),
            'widgetMetaUrl' => cp_route('dashboard.widgets.meta'),
            'widgetUpdateUrl' => cp_route('dashboard.widgets.update'),
            'widgetDestroyUrl' => cp_route('dashboard.widgets.destroy'),
            'pro' => Statamic::pro(),
            'blueprintsUrl' => cp_route('blueprints.index'),
            'collectionsCreateUrl' => cp_route('collections.create'),
            'navigationCreateUrl' => cp_route('navigation.create'),
        ]);
    }

    private function resolvedWidgetConfigs()
    {
        return Preference::get('dashboard.widgets') ?? config('statamic.cp.widgets') ?? [];
    }

    private function normalizedConfigs($widgets)
    {
        return collect($widgets)->map(function ($config) {
            return is_string($config) ? ['type' => $config] : $config;
        });
    }

    private function preProcessedConfigs($configs)
    {
        $availableWidgets = collect(app('statamic.widgets')->all());

        return $this->normalizedConfigs($configs)->map(function ($config) use ($availableWidgets) {
            $handle = $config['type'];
            $widgetClass = $availableWidgets->get($handle);

            if (! $widgetClass) {
                return $config;
            }

            $widget = app($widgetClass);
            $widget->setConfig($config);
            $fields = $widget->blueprint()->fields()->addValues($config)->preProcess();

            return array_merge(['type' => $handle], $fields->values()->all());
        })->values()->all();
    }

    /**
     * Get displayable widgets.
     *
     * @param  Loader  $loader
     * @return \Illuminate\Support\Collection
     */
    private function buildDisplayableWidgets($loader, $widgets)
    {
        return $this->normalizedConfigs($widgets)
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
