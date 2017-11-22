<?php

namespace Statamic\Http\Controllers;

use Statamic\API\User;
use Statamic\API\Config;
use Statamic\Extend\Management\WidgetLoader;

/**
 * Controller for the CP home/dashboard
 */
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
        $widgets = $this->getWidgets($loader);

        if ($widgets->isEmpty() && !User::getCurrent()->can('settings:cp')) {
            return redirect()->route('pages');
        }

        $data = [
            'title' => translate('cp.dashboard'),
            'sidebar' => false,
            'widgets' => $widgets
        ];

        return view('dashboard', $data);
    }

    private function getWidgets($loader)
    {
        return collect(Config::get('cp.widgets', []))->map(function ($config) use ($loader) {
            $widget = $loader->load(array_get($config, 'type'), $config);

            return [
                'widget' => $widget,
                'width' => $widget->get('width', 'half'),
                'html' => (string) $widget->html()
            ];
        })->filter(function ($item) {
            if (! $permissions = $item['widget']->get('permissions')) {
                return true;
            }

            $user = User::getCurrent();

            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    return true;
                }
            }

            return false;
        });
    }
}
