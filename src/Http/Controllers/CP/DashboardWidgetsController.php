<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Widgets\Widget;

class DashboardWidgetsController extends CpController
{
    public function meta()
    {
        $this->authorize('access cp');

        return $this->availableWidgets()->map(function ($class, $handle) {
            /** @var Widget $widget */
            $widget = app($class);
            $widget->setConfig(['type' => $handle]);

            $blueprint = $widget->blueprint();
            $fields = $blueprint->fields()->addValues([])->preProcess();

            return [
                'handle' => $handle,
                'title' => $class::title(),
                'icon' => $class::icon(),
                'blueprint' => $blueprint->toPublishArray(),
                'meta' => $fields->meta(),
                'defaults' => $fields->values()->all(),
            ];
        })->values()->all();
    }

    public function update(Request $request)
    {
        $this->authorize('access cp');

        $widgets = collect($request->input('widgets', []))
            ->map(fn ($config) => is_string($config) ? ['type' => $config] : (array) $config)
            ->filter(fn ($config) => isset($config['type']) && $this->availableWidgets()->has($config['type']))
            ->map(function ($config) {
                $handle = $config['type'];
                $widgetClass = $this->availableWidgets()->get($handle);
                $widget = app($widgetClass);
                $widget->setConfig(['type' => $handle]);
                $processedValues = $widget->blueprint()->fields()->addValues($config)->process()->values()->all();

                return array_merge($config, $processedValues);
            })
            ->values()
            ->all();

        User::current()->setPreference('dashboard.widgets', $widgets)->save();

        return response()->noContent();
    }

    public function destroy()
    {
        $this->authorize('access cp');

        User::current()->removePreference('dashboard.widgets')->save();

        return response()->noContent();
    }

    private function availableWidgets()
    {
        return collect(app('statamic.widgets')->all());
    }
}
