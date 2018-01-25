<?php

namespace Statamic\Http\Controllers\CP;

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
        return view('statamic::dashboard', [
            'widgets' => $this->getWidgets($loader)
        ]);
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

        // Ditch any empty widgets
        })->reject(function ($widget) {
            return empty($widget['html']);
        });
    }
}
